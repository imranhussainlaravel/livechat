<?php

namespace Src\Core\MessageTimeline;

use Src\Core\MessageTimeline\Contracts\MessageTimelineInterface;
use Src\Database\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Message Timeline — append-only message log per conversation.
 *
 * Messages are immutable once created. The timeline is the
 * authoritative record of all communication within a conversation.
 */
class MessageTimeline implements MessageTimelineInterface
{
    /**
     * Append a message to a conversation's timeline.
     */
    public function append(array $data): Message
    {
        return DB::transaction(function () use ($data) {
            return Message::create([
                'conversation_id' => $data['conversation_id'],
                'sender_type'     => $data['sender_type'], // 'agent', 'visitor', 'system'
                'sender_id'       => $data['sender_id'] ?? null,
                'type'            => $data['type'] ?? 'text',
                'body'            => $data['body'],
                'metadata'        => $data['metadata'] ?? null,
            ]);
        });
    }

    /**
     * Get the full timeline for a conversation.
     */
    public function timeline(int $conversationId): Collection
    {
        return Message::where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get the latest N messages for a conversation.
     */
    public function recent(int $conversationId, int $limit = 50): Collection
    {
        return Message::where('conversation_id', $conversationId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }
}
