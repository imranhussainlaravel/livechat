<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ChatMessage $message) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.'.$this->message->chat_id),
        ];

        // Also broadcast to the assigned agent's personal channel for global push notifications
        $chat = $this->message->chat;
        if ($chat && $chat->assigned_agent_id) {
            $channels[] = new PrivateChannel('agent.'.$chat->assigned_agent_id);
        }

        // Also broadcast to the global admin channel so admins get all notifications
        $channels[] = new PrivateChannel('admin');

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.new';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $senderType = $this->message->sender_type->value ?? $this->message->sender_type;

        return [
            'id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
            'message' => $this->message->message,
            'sender_type' => $senderType,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $senderType === 'agent' ? $this->message->sender?->name : null,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
