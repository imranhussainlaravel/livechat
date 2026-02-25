<?php

namespace App\Jobs;

use App\Enums\ChatStatus;
use App\Events\ChatAssigned;
use App\Models\Chat;
use App\Services\ActivityService;
use App\Services\AgentLoadService;
use App\Services\ChatQueueService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProcessChatQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Max attempts before failing the job. */
    public int $tries = 3;

    /** Timeout in seconds. */
    public int $timeout = 30;

    public function __construct() {}

    /**
     * Process the chat waiting queue.
     *
     * 1. Purge timed-out chats
     * 2. Loop through queue in FIFO order
     * 3. For each chat, find an agent with capacity
     * 4. Assign using atomic Redis lock (concurrency-safe)
     * 5. Stop when no more agents available or queue is empty
     */
    public function handle(
        ChatQueueService $queue,
        AgentLoadService $load,
        ActivityService  $activity,
    ): void {
        // ── Step 1: Purge timed-out chats ───────────────────────────
        $timedOut = $queue->purgeTimedOut();

        foreach ($timedOut as $chatId) {
            $chat = Chat::find($chatId);
            if ($chat && $chat->status === ChatStatus::PENDING) {
                $chat->update(['status' => ChatStatus::CLOSED->value, 'ended_at' => now()]);
                $activity->log(null, 'chat.queue_timeout', 'Chat', $chatId);
            }
        }

        // ── Step 2: Process queue in FIFO order ─────────────────────
        $maxIterations = $queue->length();
        $processed     = 0;

        while ($processed < $maxIterations) {
            $chatId = $queue->peek();

            if ($chatId === null) {
                break; // Queue is empty
            }

            // Validate the chat still needs assignment
            $chat = Chat::find($chatId);

            if (! $chat || $chat->status !== ChatStatus::PENDING) {
                // Stale entry — remove and continue
                $queue->dequeue();
                $processed++;
                continue;
            }

            // Find agent with most capacity
            $agents = $load->getAgentsWithCapacity();

            if ($agents->isEmpty()) {
                break; // No more agents available — stop processing
            }

            // ── Step 3: Concurrency-safe assignment with atomic lock ─
            $assigned = false;

            foreach ($agents as $agent) {
                $lockKey = "assign_lock:agent:{$agent->id}";

                // Atomic lock — prevents two jobs from assigning to the same agent simultaneously
                $lock = Cache::lock($lockKey, 5);

                if ($lock->get()) {
                    try {
                        // Double-check capacity inside lock
                        if (! $load->hasCapacity($agent->id)) {
                            continue;
                        }

                        DB::transaction(function () use ($chat, $agent, $load, $activity) {
                            $chat->update([
                                'assigned_agent_id' => $agent->id,
                                'status'            => ChatStatus::OPEN->value,
                            ]);

                            // Increment agent load in Redis
                            $load->increment($agent->id);

                            $activity->log($agent->id, 'chat.auto_assigned', 'Chat', $chat->id, [
                                'agent_name' => $agent->name,
                                'source'     => 'queue_processor',
                            ]);
                        });

                        // Fire event outside transaction
                        event(new ChatAssigned($chat->fresh(['visitor', 'agent']), $chat->agent));

                        $assigned = true;
                    } finally {
                        $lock->release();
                    }

                    break; // Move to next chat in queue
                }
            }

            if ($assigned) {
                $queue->dequeue();
            } else {
                break; // All agents locked or full — stop
            }

            $processed++;
        }
    }
}
