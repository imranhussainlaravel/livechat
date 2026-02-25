<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'chat_id'     => $this->chat_id,
            'sender_type' => $this->sender_type,
            'sender_id'   => $this->sender_id,
            'message'     => $this->message,
            'metadata'    => $this->metadata,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
