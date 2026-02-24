<?php

namespace Src\Core\StateMachine;

use Src\Database\Models\Conversation;
use InvalidArgumentException;

/**
 * State Machine — enforces valid conversation state transitions.
 *
 * States: new → pending → active → waiting → escalated → closed
 *
 * No state in memory. Reads current state from DB, validates
 * the transition, persists the new state.
 */
class StateMachine
{
    /**
     * Allowed state transitions.
     */
    private const TRANSITIONS = [
        'new'       => ['pending', 'active', 'closed'],
        'pending'   => ['active', 'escalated', 'closed'],
        'active'    => ['waiting', 'escalated', 'closed'],
        'waiting'   => ['active', 'escalated', 'closed'],
        'escalated' => ['active', 'closed'],
        'closed'    => [], // terminal state
    ];

    /**
     * Transition a conversation to a new state.
     *
     * @throws InvalidArgumentException if transition is not allowed
     */
    public function transition(Conversation $conversation, string $toState): void
    {
        $currentState = $conversation->state;

        if (! $this->canTransition($currentState, $toState)) {
            throw new InvalidArgumentException(
                "Invalid state transition: [{$currentState}] → [{$toState}]"
            );
        }

        $conversation->update(['state' => $toState]);
    }

    /**
     * Check if a transition is allowed.
     */
    public function canTransition(string $fromState, string $toState): bool
    {
        return in_array($toState, self::TRANSITIONS[$fromState] ?? [], true);
    }

    /**
     * Get all possible next states from a given state.
     */
    public function allowedTransitions(string $fromState): array
    {
        return self::TRANSITIONS[$fromState] ?? [];
    }
}
