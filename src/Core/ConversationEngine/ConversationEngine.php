<?php

namespace Src\Core\ConversationEngine;

use Src\Core\ConversationEngine\Contracts\ConversationEngineInterface;
use Src\Core\StateMachine\StateMachine;
use Src\Database\Models\Conversation;
use Src\Database\Models\Visitor;
use Illuminate\Support\Facades\DB;

/**
 * Conversation Engine — owns the lifecycle of every conversation.
 *
 * Responsibilities:
 *  - Create conversations from visitor requests
 *  - Transition states via StateMachine
 *  - Close / reopen conversations
 *
 * All state lives in the database. No in-memory state.
 */
class ConversationEngine implements ConversationEngineInterface
{
    public function __construct(
        private readonly StateMachine $stateMachine,
    ) {}

    /**
     * Start a new conversation for a visitor.
     */
    public function start(int $visitorId, ?int $queueId = null, ?string $subject = null): Conversation
    {
        return DB::transaction(function () use ($visitorId, $queueId, $subject) {
            $conversation = Conversation::create([
                'visitor_id' => $visitorId,
                'queue_id'   => $queueId,
                'subject'    => $subject,
                'state'      => 'new',
                'priority'   => 'medium',
                'sla_status' => 'healthy',
            ]);

            return $conversation;
        });
    }

    /**
     * Transition a conversation to a new state.
     */
    public function transition(int $conversationId, string $toState): Conversation
    {
        return DB::transaction(function () use ($conversationId, $toState) {
            $conversation = Conversation::lockForUpdate()->findOrFail($conversationId);
            $this->stateMachine->transition($conversation, $toState);
            return $conversation->fresh();
        });
    }

    /**
     * Close a conversation.
     */
    public function close(int $conversationId): Conversation
    {
        return DB::transaction(function () use ($conversationId) {
            $conversation = Conversation::lockForUpdate()->findOrFail($conversationId);
            $this->stateMachine->transition($conversation, 'closed');
            $conversation->update(['resolved_at' => now()]);
            return $conversation->fresh();
        });
    }
}
