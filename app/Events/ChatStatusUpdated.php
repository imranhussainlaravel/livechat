<?php

namespace App\Events;

use App\Models\Chat;
use App\Enums\ChatStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Chat       $chat,
        public ChatStatus $oldStatus,
        public ChatStatus $newStatus,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.' . $this->chat->id),
            new PrivateChannel('agents'),
        ];

        if ($this->chat->assigned_agent_id) {
            $channels[] = new PrivateChannel('agent.' . $this->chat->assigned_agent_id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'chat_id'    => $this->chat->id,
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->newStatus->value,
        ];
    }
}
