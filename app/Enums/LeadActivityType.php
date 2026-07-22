<?php

namespace App\Enums;

enum LeadActivityType: string
{
    case Call = 'call';
    case Note = 'note';
    case Email = 'email';
    case StatusChange = 'status_change';
    case Reassignment = 'reassignment';

    public function getLabel(): string
    {
        return match ($this) {
            self::Call => 'Call logged',
            self::Note => 'Note',
            self::Email => 'Email',
            self::StatusChange => 'Status changed',
            self::Reassignment => 'Reassigned',
        };
    }
}
