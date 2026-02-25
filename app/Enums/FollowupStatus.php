<?php

namespace App\Enums;

enum FollowupStatus: string
{
    case PENDING   = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
