<?php

namespace App\Enums;

enum TicketStatus: string
{
    case INTERESTED     = 'interested';
    case NOT_INTERESTED = 'not_interested';
}
