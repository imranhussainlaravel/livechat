<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatQueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $pendingCount = 0) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.queue'),
            new PrivateChannel('agent.queue'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'queue.updated';
    }

    public function broadcastWith(): array
    {
        return ['pending_count' => $this->pendingCount];
    }
}
