<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Conversation;
use App\Models\Assignment;
use App\Services\TransitionConversationState;
use App\Exceptions\AssignmentException;

class AcceptConversation
{
    protected TransitionConversationState $stateTransition;

    public function __construct(TransitionConversationState $stateTransition)
    {
        $this->stateTransition = $stateTransition;
    }

    /**
     * Agent accepts a pending conversation.
     * Uses DB transactions and pessimistic locking to prevent race conditions.
     *
     * @param int $conversationId
     * @param int $agentId
     * @return Conversation
     * @throws AssignmentException
     */
    public function execute(int $conversationId, int $agentId): Conversation
    {
        return DB::transaction(function () use ($conversationId, $agentId) {
            // 1. Lock the row for update to prevent concurrent assignments
            $conversation = Conversation::lockForUpdate()->find($conversationId);

            if (!$conversation) {
                throw new AssignmentException("Conversation not found.");
            }

            // 2. Validate state == PENDING
            if ($conversation->state !== 'PENDING') {
                throw new AssignmentException("Conversation is not in PENDING state. Current state: {$conversation->state}");
            }

            // 3. Prevent double ownership
            if ($conversation->assigned_agent_id !== null) {
                throw new AssignmentException("Conversation is already assigned to an agent.");
            }

            // 4. Assign agent
            $conversation->assigned_agent_id = $agentId;

            // 5. Create Assignment audit record
            Assignment::create([
                'conversation_id' => $conversation->id,
                'agent_id' => $agentId,
                'assigned_at' => now(),
            ]);

            // 6. Transition state to ACTIVE (This will also save the conversation)
            $this->stateTransition->execute($conversation, 'ACTIVE');

            return $conversation;
        });
    }
}
