<?php

namespace App\Enums;

use InvalidArgumentException;

enum ChatStatus: string
{
    case PENDING     = 'pending';       // waiting for agent assignment
    case ASSIGNED    = 'assigned';      // assigned to an agent
    case ACTIVE      = 'active';        // agent and user actively chatting
    case CLOSED      = 'closed';        // chat completed
    case TRANSFERRED = 'transferred';   // moved to another agent

    public function label(): string
    {
        return match ($this) {
            self::PENDING     => 'Pending',
            self::ASSIGNED    => 'Assigned',
            self::ACTIVE      => 'Active',
            self::CLOSED      => 'Closed',
            self::TRANSFERRED => 'Transferred',
        };
    }

    /* ------------------------------------------------------------------ */
    /*  Status Flow: pending → assigned → active → closed                 */
    /*  Additional: assigned/active → transferred → assigned              */
    /* ------------------------------------------------------------------ */

    /**
     * Allowed transitions from each status.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING     => [self::ASSIGNED, self::CLOSED],
            self::ASSIGNED    => [self::ACTIVE, self::TRANSFERRED, self::CLOSED],
            self::ACTIVE      => [self::TRANSFERRED, self::CLOSED],
            self::TRANSFERRED => [self::ASSIGNED, self::CLOSED],
            self::CLOSED      => [], // terminal
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
        return [self::ASSIGNED, self::ACTIVE, self::TRANSFERRED];
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
