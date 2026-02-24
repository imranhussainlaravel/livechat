<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Conversation;
use App\Models\SlaLog;
use App\Services\TransitionConversationState;
use App\Services\TriggerWhatsappAlert;

class CheckConversationSla
{
    protected TransitionConversationState $stateTransition;
    protected TriggerWhatsappAlert $whatsappAlert;

    public function __construct(
        TransitionConversationState $stateTransition,
        TriggerWhatsappAlert $whatsappAlert
    ) {
        $this->stateTransition = $stateTransition;
        $this->whatsappAlert = $whatsappAlert;
    }

    /**
     * Evaluates SLA rules on a conversation and executes escalations.
     *
     * @param Conversation $conversation
     * @return void
     */
    public function execute(Conversation $conversation): void
    {
        DB::transaction(function () use ($conversation) {
            // Lock for processing SLA
            $convo = Conversation::lockForUpdate()->find($conversation->id);
            if (!$convo || $convo->state === 'CLOSED' || $convo->state === 'ESCALATED') {
                return;
            }

            $now = Carbon::now();
            $updatedAt = $convo->updated_at; // last transition state
            $secondsSinceUpdate = $updatedAt ? $now->diffInSeconds($updatedAt) : 0;

            // Scenario 1: Pending rule
            if ($convo->state === 'PENDING') {
                if ($secondsSinceUpdate > 60) {
                    $this->escalate($convo, 'PENDING_TIME_EXCEEDED');
                    return;
                }

                if ($secondsSinceUpdate > 30 && $convo->sla_state !== 'WARNING') {
                    $convo->sla_state = 'WARNING';
                    $convo->save();
                    return;
                }
            }

            // Scenario 2: Active with unanswered user messages
            if ($convo->state === 'ACTIVE') {
                $unansweredCount = $convo->messages()
                    ->where('sender_type', 'USER')
                    ->where('created_at', '>', function ($query) use ($convo) {
                        $query->select('created_at')
                            ->from('messages')
                            ->where('conversation_id', $convo->id)
                            ->where('sender_type', 'AGENT')
                            ->orderByDesc('created_at')
                            ->limit(1);
                    })
                    ->count();

                // If agent has never replied, count all user messages since assigned
                if ($unansweredCount === 0) {
                    $agentReplyExists = $convo->messages()->where('sender_type', 'AGENT')->exists();
                    if (!$agentReplyExists) {
                        $unansweredCount = $convo->messages()->where('sender_type', 'USER')->count();
                    }
                }

                if ($unansweredCount >= 3) {
                    $this->escalate($convo, 'MULTIPLE_UNANSWERED_MESSAGES');
                    return;
                }
            }
        });
    }

    /**
     * Process escalation routine.
     *
     * @param Conversation $conversation
     * @param string $breachType
     * @return void
     */
    protected function escalate(Conversation $conversation, string $breachType): void
    {
        $this->stateTransition->execute($conversation, 'ESCALATED');
        $conversation->sla_state = 'BREACHED';
        $conversation->save();

        SlaLog::create([
            'conversation_id' => $conversation->id,
            'breach_type' => $breachType,
            'triggered_at' => now(),
        ]);

        $this->whatsappAlert->execute(
            $conversation,
            "SLA Breach: Conversation #{$conversation->id} escalated. Reason: {$breachType}"
        );
    }
}
