<?php

namespace App\Enums;

use InvalidArgumentException;

enum ChatStatus: string
{
    case PENDING     = 'pending';
    case OPEN        = 'open';
    case IN_PROGRESS = 'in_progress';
    case SOLVED      = 'solved';
    case CLOSED      = 'closed';
    case FOLLOWUP    = 'followup';

    public function label(): string
    {
        return match ($this) {
            self::PENDING     => 'Pending',
            self::OPEN        => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::SOLVED      => 'Solved',
            self::CLOSED      => 'Closed',
            self::FOLLOWUP    => 'Follow-up',
        };
    }

    /* ------------------------------------------------------------------ */
    /*  Status Flow: pending → open → in_progress → solved → closed       */
    /*  Additional: open/in_progress → followup → in_progress             */
    /* ------------------------------------------------------------------ */

    /**
     * Allowed transitions from each status.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING     => [self::OPEN, self::CLOSED],
            self::OPEN        => [self::IN_PROGRESS, self::FOLLOWUP, self::CLOSED],
            self::IN_PROGRESS => [self::SOLVED, self::FOLLOWUP, self::CLOSED],
            self::FOLLOWUP    => [self::IN_PROGRESS, self::SOLVED, self::CLOSED],
            self::SOLVED      => [self::CLOSED, self::IN_PROGRESS], // reopen
            self::CLOSED      => [],                                 // terminal
        };
    }

    /**
     * Check if transitioning to the target status is allowed.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }

    /**
     * Validate and return the target status, or throw.
     *
     * @throws InvalidArgumentException
     */
    public function transitionTo(self $target): self
    {
        if (! $this->canTransitionTo($target)) {
            throw new InvalidArgumentException(
                "Invalid status transition: {$this->value} → {$target->value}"
            );
        }

        return $target;
    }

    /** States that count as "active" for an agent's load. */
    public static function activeStates(): array
    {
        return [self::OPEN, self::IN_PROGRESS, self::FOLLOWUP];
    }

    /** Is this a terminal (finished) state? */
    public function isTerminal(): bool
    {
        return $this === self::CLOSED;
    }

    /** Is this an active chat state? */
    public function isActive(): bool
    {
        return in_array($this, self::activeStates());
    }
}
