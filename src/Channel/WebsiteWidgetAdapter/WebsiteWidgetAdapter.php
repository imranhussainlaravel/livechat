<?php

namespace Src\Channel\WebsiteWidgetAdapter;

use Src\Core\ConversationEngine\ConversationEngine;
use Src\Core\MessageTimeline\MessageTimeline;
use Src\Visitor\SessionEngine\SessionEngine;

/**
 * Website Widget Adapter — bridges the website chat widget to the core engine.
 *
 * This adapter translates HTTP requests from the embeddable widget
 * into business operations. It is the sole entry point for visitor
 * interactions from the website channel.
 */
class WebsiteWidgetAdapter
{
    public function __construct(
        private readonly SessionEngine $sessionEngine,
        private readonly ConversationEngine $conversationEngine,
        private readonly MessageTimeline $messageTimeline,
    ) {}

    /**
     * Initialize a widget session — resolves visitor and optionally starts a conversation.
     */
    public function initSession(string $sessionToken): array
    {
        $visitor = $this->sessionEngine->resolve($sessionToken);

        return [
            'visitor_id'    => $visitor->id,
            'session_token' => $visitor->session_token,
        ];
    }

    /**
     * Start a conversation from the widget.
     */
    public function startConversation(int $visitorId, ?string $subject = null): array
    {
        $conversation = $this->conversationEngine->start($visitorId, null, $subject);

        return [
            'conversation_id' => $conversation->id,
            'state'           => $conversation->state,
        ];
    }

    /**
     * Send a message from the widget.
     */
    public function sendMessage(int $conversationId, int $visitorId, string $body): array
    {
        $message = $this->messageTimeline->append([
            'conversation_id' => $conversationId,
            'sender_type'     => 'visitor',
            'sender_id'       => $visitorId,
            'body'            => $body,
        ]);

        return [
            'message_id' => $message->id,
            'created_at' => $message->created_at->toIso8601String(),
        ];
    }
}
