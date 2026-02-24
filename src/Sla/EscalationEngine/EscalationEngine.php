<?php

namespace Src\Sla\EscalationEngine;

use Src\Database\Models\Conversation;
use Src\Database\Models\SlaLog;
use Illuminate\Support\Facades\DB;

/**
 * Escalation Engine — monitors SLA compliance and escalates breaches.
 *
 * Runs periodically via a scheduled job. Checks conversations
 * against SLA thresholds and transitions breaching conversations
 * to the 'escalated' state.
 */
class EscalationEngine
{
    /**
     * Default SLA thresholds (in seconds).
     */
    private const DEFAULTS = [
        'first_response'   => 120,  // 2 minutes
        'resolution'       => 3600, // 1 hour
        'queue_wait'       => 300,  // 5 minutes
    ];

    /**
     * Check all active conversations for SLA breaches.
     *
     * @return int Number of escalated conversations
     */
    public function sweep(): int
    {
        $count = 0;
        $thresholds = $this->getThresholds();

        // Check first-response SLA
        $noResponse = Conversation::whereIn('state', ['pending', 'active'])
            ->whereNull('first_response_at')
            ->where('created_at', '<', now()->subSeconds($thresholds['first_response']))
            ->get();

        foreach ($noResponse as $conversation) {
            $this->escalate($conversation, 'first_response_breach');
            $count++;
        }

        // Check resolution SLA
        $unresolved = Conversation::whereIn('state', ['active', 'waiting', 'escalated'])
            ->whereNull('resolved_at')
            ->where('created_at', '<', now()->subSeconds($thresholds['resolution']))
            ->where('sla_status', '!=', 'breached')
            ->get();

        foreach ($unresolved as $conversation) {
            $this->markBreached($conversation, 'resolution_breach');
            $count++;
        }

        return $count;
    }

    /**
     * Escalate a single conversation.
     */
    private function escalate(Conversation $conversation, string $reason): void
    {
        DB::transaction(function () use ($conversation, $reason) {
            $conversation->update([
                'state'      => 'escalated',
                'sla_status' => 'breached',
                'priority'   => 'high',
            ]);

            SlaLog::create([
                'conversation_id' => $conversation->id,
                'event'           => $reason,
                'details'         => json_encode(['escalated_at' => now()->toIso8601String()]),
            ]);
        });
    }

    /**
     * Mark a conversation as SLA-breached without state change.
     */
    private function markBreached(Conversation $conversation, string $reason): void
    {
        $conversation->update(['sla_status' => 'breached']);

        SlaLog::create([
            'conversation_id' => $conversation->id,
            'event'           => $reason,
            'details'         => json_encode(['breached_at' => now()->toIso8601String()]),
        ]);
    }

    /**
     * Get SLA thresholds from config or defaults.
     */
    private function getThresholds(): array
    {
        return config('livechat.sla', self::DEFAULTS);
    }
}
