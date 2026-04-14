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
        // Auto-assignment disabled. Agents manually pick from the queue.
    }

    /**
     * Process the FIFO queue.
     */
    private function processQueue(): void
    {
        // Auto-assignment disabled.
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
                'status' => ChatStatus::ASSIGNED->value,
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
        event(new ChatAssigned($chat, $agent));
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

        // As a chat leaves, an agent opens up. (Auto-assign removed)
    }
}
