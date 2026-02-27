<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\User;
use App\Models\AgentChatLoad;
use App\Enums\ChatStatus;
use App\Events\ChatAssigned;
use App\Events\ChatQueueUpdated;
use App\Events\AgentLoadUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class QueueService
{
    /**
     * Assign pending chats to available agents using FIFO.
     */
    public function assignPendingChats(): void
    {
        // Use a Redis lock to prevent race conditions in high concurrency
        $lock = Redis::lock('chat_queue_assignment', 10);

        if ($lock->get()) {
            try {
                $this->processQueue();
            } finally {
                $lock->release();
            }
        }
    }

    /**
     * Process the FIFO queue.
     */
    private function processQueue(): void
    {
        // 1. Get pending chats ordered strictly by queued_at (FIFO).
        $pendingChats = Chat::where('status', ChatStatus::PENDING->value)
            ->orderBy('queued_at', 'asc')
            ->get();

        if ($pendingChats->isEmpty()) {
            return;
        }

        foreach ($pendingChats as $chat) {
            // 2. Find the best available agent
            $agent = $this->getAvailableAgent();

            if (! $agent) {
                // Return immediately if no agents available - no point checking next chat.
                break;
            }

            // 3. Assign chat & increment load
            $this->assignChatToAgent($chat, $agent);
        }

        // Trigger an update for admin queues.
        event(new ChatQueueUpdated());
    }

    /**
     * Get the best available agent (Online and under max capacity).
     * Tiebreaker: Returns the agent with the lowest active chat count, 
     * then the one who was assigned a chat the longest ago.
     */
    public function getAvailableAgent(): ?User
    {
        return DB::transaction(function () {
            // Requires a row lock on agent_chat_loads to accurately read/write capacity
            return User::where('users.status', 'online')
                ->where('users.role', 'agent')
                ->join('agent_chat_loads', 'users.id', '=', 'agent_chat_loads.agent_id')
                ->whereColumn('agent_chat_loads.active_chats', '<', 'agent_chat_loads.max_chats')
                ->orderBy('agent_chat_loads.active_chats', 'asc') // Least loaded first
                ->orderBy('agent_chat_loads.last_assigned_at', 'asc') // Round robin for ties
                ->select('users.*') // Ensure we only hydrate the User model
                ->lockForUpdate() // Lock to prevent other threads from reading stale active_chats
                ->first();
        });
    }

    /**
     * Assign a chat to an agent and update load.
     */
    public function assignChatToAgent(Chat $chat, User $agent): void
    {
        DB::transaction(function () use ($chat, $agent) {
            // Assign to chat
            $chat->update([
                'assigned_agent_id' => $agent->id,
                'status' => ChatStatus::OPEN->value,
                'started_at' => now(),
            ]);

            // Increment Load securely
            $load = AgentChatLoad::where('agent_id', $agent->id)->lockForUpdate()->first();
            if ($load) {
                $load->update([
                    'active_chats' => DB::raw('active_chats + 1'),
                    'last_assigned_at' => now(),
                ]);
            }
        });

        // Fire Broadcast Events
        event(new ChatAssigned($chat->id, $agent->id));
        event(new AgentLoadUpdated($agent->id));
    }

    /**
     * Release a chat from an agent, decrementing load. 
     * Should be called when a chat hits "solved" or "closed".
     */
    public function releaseChatFromAgent(Chat $chat): void
    {
        if (! $chat->assigned_agent_id) return;

        DB::transaction(function () use ($chat) {
            $load = AgentChatLoad::where('agent_id', $chat->assigned_agent_id)->lockForUpdate()->first();
            if ($load && $load->active_chats > 0) {
                $load->update([
                    'active_chats' => DB::raw('active_chats - 1'),
                ]);
            }
        });

        event(new AgentLoadUpdated($chat->assigned_agent_id));

        // As a chat leaves, an agent opens up. Run the queue checker!
        $this->assignPendingChats();
    }
}
