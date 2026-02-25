<?php

namespace App\Enums;

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

    /** States that count as "active" for an agent's load. */
    public static function activeStates(): array
    {
        return [self::OPEN, self::IN_PROGRESS, self::FOLLOWUP];
    }
}
