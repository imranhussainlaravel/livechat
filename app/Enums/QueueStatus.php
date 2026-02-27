<?php

namespace App\Enums;

enum QueueStatus: string
{
    case QUEUED = 'queued';
    case PICKED = 'picked';
    case NONE   = 'none';

    public function label(): string
    {
        return match ($this) {
            self::QUEUED => 'Queued',
            self::PICKED => 'Picked',
            self::NONE   => 'None',
        };
    }
}
