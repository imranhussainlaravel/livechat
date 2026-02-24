<?php

namespace App\Services;

use App\Models\Conversation;
use App\Exceptions\InvalidTransitionException;

class TransitionConversationState
{
    /**
     * @var array<string, array<string>> Valid state transitions.
     */
    protected array $validTransitions = [
        'NEW' => ['PENDING'],
        'PENDING' => ['ACTIVE'],
        'ACTIVE' => ['WAITING', 'TRANSFERRED', 'CLOSED'],
        'WAITING' => ['ACTIVE'],
        'TRANSFERRED' => ['ACTIVE'],
        // ANY -> ESCALATED is handled in the logic
    ];

    /**
     * Execute the state transition for a given conversation.
     *
     * @param Conversation $conversation The conversation to transition state for
     * @param string $newState The new state
     * @return Conversation The updated conversation
     * @throws InvalidTransitionException If the transition is invalid
     */
    public function execute(Conversation $conversation, string $newState): Conversation
    {
        $currentState = $conversation->state;

        // "ANY -> ESCALATED" transition allowed
        if ($newState === 'ESCALATED') {
            $this->applyTransition($conversation, $newState);
            return $conversation;
        }

        // Prevent transitioning to the same state as self
        if ($currentState === $newState) {
            return $conversation;
        }

        $allowedNextStates = $this->validTransitions[$currentState] ?? [];

        if (!in_array($newState, $allowedNextStates, true)) {
            throw new InvalidTransitionException("Cannot transition from {$currentState} to {$newState}");
        }

        $this->applyTransition($conversation, $newState);
        return $conversation;
    }

    /**
     * Applies the state change and persists it.
     *
     * @param Conversation $conversation
     * @param string $newState
     * @return void
     */
    protected function applyTransition(Conversation $conversation, string $newState): void
    {
        // Business logic or event dispatching can be added here
        $conversation->state = $newState;
        $conversation->save();
    }
}
