<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $senderType = $this->sender_type instanceof \BackedEnum
            ? $this->sender_type->value
            : (string) $this->sender_type;

        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'sender_type' => $senderType,
            'sender_id' => $this->sender_id,
            'sender_name' => $senderType === 'agent' ? $this->sender?->name : null,
            'message' => $this->message,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
