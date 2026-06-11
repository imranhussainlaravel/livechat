<?php

namespace App\Events;

use App\Enums\ChatStatus;
use App\Enums\QueueStatus;
use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Chat $chat) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.'.$this->chat->id),
            new PrivateChannel('agents'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.started';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chat->id,
            'visitor_name' => $this->chat->visitor->name ?? 'Visitor',
            'pending_count' => Chat::where('queue_status', QueueStatus::QUEUED)
                ->whereNull('assigned_agent_id')
                ->where('status', ChatStatus::PENDING)
                ->count(),
        ];
    }
}
