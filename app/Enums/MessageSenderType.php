<?php

namespace App\Enums;

enum MessageSenderType: string
{
    case BOT     = 'bot';
    case VISITOR = 'visitor';
    case AGENT   = 'agent';
    case SYSTEM  = 'system';
}
