<?php

namespace Src\Core\MessageTimeline\Contracts;

use Src\Database\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface MessageTimelineInterface
{
    public function append(array $data): Message;
    public function timeline(int $conversationId): Collection;
    public function recent(int $conversationId, int $limit = 50): Collection;
}
