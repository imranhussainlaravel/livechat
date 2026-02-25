<?php

namespace App\DTOs;

class CreateTicketDTO
{
    public function __construct(
        public readonly int      $chatId,
        public readonly int      $agentId,
        public readonly string   $status = 'interested',
        public readonly bool     $quotationSent = false,
        public readonly ?float   $amount = null,
        public readonly ?string  $notes = null,
    ) {}

    public static function fromRequest(array $data, int $agentId): self
    {
        return new self(
            chatId: $data['chat_id'],
            agentId: $agentId,
            status: $data['status'] ?? 'interested',
            quotationSent: $data['quotation_sent'] ?? false,
            amount: $data['amount'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
