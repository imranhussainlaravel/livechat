<?php

namespace App\DTOs;

class SendMessageDTO
{
    public function __construct(
        public readonly int    $chatId,
        public readonly string $senderType,
        public readonly ?int   $senderId,
        public readonly string $message,
        public readonly ?array $metadata = null,
    ) {}

    public static function fromRequest(array $data, int $chatId): self
    {
        return new self(
            chatId: $chatId,
            senderType: $data['sender_type'],
            senderId: $data['sender_id'] ?? null,
            message: $data['message'],
            metadata: $data['metadata'] ?? null,
        );
    }
}
