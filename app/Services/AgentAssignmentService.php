<?php

namespace App\Services;

use App\Enums\ChatStatus;
use App\Events\ChatAssigned;
use App\Jobs\ProcessChatQueue;
use App\Models\Chat;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AgentAssignmentService
{
    public function __construct(
        private AgentLoadService        $load,
        private ChatQueueService        $queue,
        private ChatRepositoryInterface $chats,
        private ActivityService         $activity,
    ) {}

    /**
     * Attempt to assign a chat to the best available agent.
     *
     * Strategy:
     *   1. Find agents with capacity (sorted by most available slots)
     *   2. Use atomic lock for concurrency-safe assignment
     *   3. If no agents available, push to Redis FIFO queue
     *
     * Returns true if assigned, false if queued.
     */
    public function tryAssign(Chat $chat): bool
    {
        $agents = $this->load->getAgentsWithCapacity();

        if ($agents->isEmpty()) {
            // ── No agents → push to queue ───────────────────────────
            $this->queue->enqueue($chat->id);

            $this->activity->log(null, 'chat.queued', 'Chat', $chat->id, [
                'queue_position' => $this->queue->length(),
                'reason'         => 'No available agents',
            ]);

            return false;
        }

        // ── Try assignment with atomic locking ──────────────────────
        foreach ($agents as $agent) {
            $lockKey = "assign_lock:agent:{$agent->id}";
            $lock    = Cache::lock($lockKey, 5);

            if ($lock->get()) {
                try {
                    // Double-check capacity under lock
                    if (! $this->load->hasCapacity($agent->id)) {
                        continue;
                    }

                    DB::transaction(function () use ($chat, $agent) {
                        $this->chats->update($chat->id, [
                            'assigned_agent_id' => $agent->id,
                            'status'            => ChatStatus::ASSIGNED->value,
                        ]);

                        $this->load->increment($agent->id);
                    });

                    $this->activity->log($agent->id, 'chat.auto_assigned', 'Chat', $chat->id, [
                        'agent_name'  => $agent->name,
                        'active_load' => $agent->active + 1,
                        'max_chats'   => $agent->max,
                    ]);

                    event(new ChatAssigned($chat->fresh(['visitor', 'agent']), $chat->fresh()->agent));

                    return true;
                } finally {
                    $lock->release();
                }
            }
        }

        // ── All agents locked — queue for later ─────────────────────
        $this->queue->enqueue($chat->id);

        $this->activity->log(null, 'chat.queued', 'Chat', $chat->id, [
            'queue_position' => $this->queue->length(),
            'reason'         => 'All agents at capacity or locked',
        ]);

        return false;
    }

    /**
     * Called when an agent becomes free (chat closed, transferred away).
     * Dispatches the queue processor to fill the open slot.
     */
    public function onAgentFreed(int $agentId): void
    {
        $this->load->decrement($agentId);

        // Process waiting queue if there are chats waiting
        if ($this->queue->length() > 0) {
            ProcessChatQueue::dispatch()->onQueue('chat_assignment');
        }
    }

    /**
     * Force re-sync all agent loads from database.
     */
    public function syncLoads(): void
    {
        $this->load->syncAll();
    }
}
