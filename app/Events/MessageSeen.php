<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when the visitor has read the chat, so the agent's dashboard can
 * show a "Seen" indicator. Intentionally one-directional — there is no
 * equivalent event for when an agent reads a visitor's message.
 */
class MessageSeen implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $chatId, public string $seenAt) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.'.$this->chatId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.seen';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chatId,
            'seen_at' => $this->seenAt,
        ];
    }
}
