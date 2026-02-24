<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\NotificationLog;
use App\Jobs\SendWhatsappAlertJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TriggerWhatsappAlert
{
    /**
     * Dispatch a WhatsApp alert for an escalated or away conversation.
     * Enforces a cooldown to prevent agent spam.
     *
     * @param Conversation $conversation
     * @param string $message
     * @param string $level (e.g. 'ESCALATION' or 'AWAY_REPLY')
     * @return void
     */
    public function execute(Conversation $conversation, string $message, string $level = 'ESCALATION'): void
    {
        $agentId = $conversation->assigned_agent_id;

        // Respect cooldown per agent (e.g., 5 minutes for non-escalations)
        if ($agentId && $level !== 'ESCALATION') {
            $lastAlert = NotificationLog::where('agent_id', $agentId)
                ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                ->first();

            if ($lastAlert) {
                Log::channel('single')->info("WHATSAPP ALERT SKIPPED: Agent {$agentId} is in cooldown.");
                return;
            }
        }

        // Dispatch background job (exponential backoff handled by Job class)
        SendWhatsappAlertJob::dispatch($conversation, $message, $agentId, $level);
    }
}
