<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingIndicator implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int    $chatId,
        public int    $userId,
        public string $userName,
        public string $senderType, // 'agent' or 'visitor'
        public bool   $isTyping = true,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat-room.' . $this->chatId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'typing.indicator';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_id'     => $this->chatId,
            'user_id'     => $this->userId,
            'user_name'   => $this->userName,
            'sender_type' => $this->senderType,
            'is_typing'   => $this->isTyping,
        ];
    }
}
