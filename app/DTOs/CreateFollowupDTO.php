<?php

namespace App\DTOs;

class CreateFollowupDTO
{
    public function __construct(
        public readonly int    $chatId,
        public readonly int    $agentId,
        public readonly string $followupTime,
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(array $data, int $agentId): self
    {
        return new self(
            chatId: $data['chat_id'],
            agentId: $agentId,
            followupTime: $data['followup_time'],
            notes: $data['notes'] ?? null,
        );
    }
}
