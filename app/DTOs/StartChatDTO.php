<?php

namespace App\DTOs;

class StartChatDTO
{
    public function __construct(
        public readonly string  $sessionToken,
        public readonly ?string $visitorName = null,
        public readonly ?string $visitorEmail = null,
        public readonly ?string $subject = null,
        public readonly ?array  $metadata = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            sessionToken: $data['session_token'],
            visitorName: $data['visitor_name'] ?? null,
            visitorEmail: $data['visitor_email'] ?? null,
            subject: $data['subject'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
