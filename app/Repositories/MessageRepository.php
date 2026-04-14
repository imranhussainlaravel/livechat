<?php

namespace App\Repositories;

use App\Models\ChatMessage;
use App\Repositories\Contracts\MessageRepositoryInterface;

class MessageRepository implements MessageRepositoryInterface
{
    public function getByChatId(int $chatId, int $perPage = 50)
    {
        return ChatMessage::with('sender')->where('chat_id', $chatId)
            ->orderBy('created_at')
            ->paginate($perPage);
    }

    public function create(array $data)
    {
        return ChatMessage::create($data);
    }
}
