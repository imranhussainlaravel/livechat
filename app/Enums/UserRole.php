<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AGENT = 'agent';
    case PRODUCTION = 'production';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::AGENT => 'Agent',
            self::PRODUCTION => 'Production',
        };
    }
}
