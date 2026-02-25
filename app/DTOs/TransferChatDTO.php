<?php

namespace App\DTOs;

class TransferChatDTO
{
    public function __construct(
        public readonly int     $chatId,
        public readonly int     $fromAgentId,
        public readonly int     $toAgentId,
        public readonly ?string $reason = null,
    ) {}

    public static function fromRequest(array $data, int $chatId, int $fromAgentId): self
    {
        return new self(
            chatId: $chatId,
            fromAgentId: $fromAgentId,
            toAgentId: $data['to_agent_id'],
            reason: $data['reason'] ?? null,
        );
    }
}
