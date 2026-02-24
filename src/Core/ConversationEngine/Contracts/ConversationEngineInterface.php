<?php

namespace Src\Core\ConversationEngine\Contracts;

use Src\Database\Models\Conversation;

interface ConversationEngineInterface
{
    public function start(int $visitorId, ?int $queueId = null, ?string $subject = null): Conversation;
    public function transition(int $conversationId, string $toState): Conversation;
    public function close(int $conversationId): Conversation;
}
