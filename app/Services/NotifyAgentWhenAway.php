<?php

namespace App\Services;

use App\Models\Conversation;

class NotifyAgentWhenAway
{
    protected TriggerWhatsappAlert $whatsappAlert;

    public function __construct(TriggerWhatsappAlert $whatsappAlert)
    {
        $this->whatsappAlert = $whatsappAlert;
    }

    /**
     * Trigger an alert when a user sends a message while the assigned agent is away.
     * This integrates the business rule without syncing actual chat messages.
     *
     * @param Conversation $conversation
     * @return void
     */
    public function execute(Conversation $conversation): void
    {
        // Must be an active assigned conversation
        if ($conversation->state !== 'ACTIVE' || !$conversation->agent) {
            return;
        }

        // Must be AWAY status
        if ($conversation->agent->status === 'away') {
            $message = "Alert: You have received a new reply in conversation #{$conversation->id} while away.";

            // Note: Does NOT sync content. Only triggers structural alert.
            $this->whatsappAlert->execute($conversation, $message, 'AWAY_REPLY');
        }
    }
}
