<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Conversation;
use App\Models\NotificationLog;

class SendWhatsappAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $conversation;
    public $message;
    public $agentId;
    public $level;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(Conversation $conversation, string $message, ?int $agentId, string $level)
    {
        $this->conversation = $conversation;
        $this->message = $message;
        $this->agentId = $agentId;
        $this->level = $level;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     * Exponential backoff: 10s, 30s, 90s, 270s...
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 90, 270];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Must reload the conversation to get the freshest state
        $this->conversation->refresh();

        // Stop alerts once conversation becomes ACTIVE
        if ($this->conversation->state === 'ACTIVE' && $this->level !== 'ESCALATION') {
            Log::channel('single')->info("WHATSAPP ALERT ABORTED: Conversation {$this->conversation->id} is now ACTIVE.");
            return;
        }

        try {
            // Integration logic here (HTTP request to WhatsApp Provider API)
            // WE ONLY SEND ALERTS - NO CONTENT SYNC
            Log::channel('single')->info("WHATSAPP ALERT DISPATCHED [Conv: {$this->conversation->id} | Agent: {$this->agentId}]: {$this->message}");

            NotificationLog::create([
                'agent_id' => $this->agentId,
                'conversation_id' => $this->conversation->id,
                'level' => $this->level,
                'status' => 'SENT',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("WhatsApp alert failed: " . $e->getMessage());

            // Re-throw to trigger exponential backoff retry
            throw $e;
        }
    }
}
