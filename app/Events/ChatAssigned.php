<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Chat $chat,
        public User $agent,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chat->id),
            new PrivateChannel('agents'),
            new PrivateChannel('agent.' . $this->agent->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'chat_id'    => $this->chat->id,
            'agent_id'   => $this->agent->id,
            'agent_name' => $this->agent->name,
            'status'     => $this->chat->status->value,
        ];
    }
}
