<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'chat_id'        => $this->chat_id,
            'agent_id'       => $this->agent_id,
            'status'         => $this->status,
            'quotation_sent' => $this->quotation_sent,
            'amount'         => $this->amount,
            'notes'          => $this->notes,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
