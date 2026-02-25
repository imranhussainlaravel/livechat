<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'visitor'     => new VisitorResource($this->whenLoaded('visitor')),
            'agent'       => new UserResource($this->whenLoaded('agent')),
            'status'      => $this->status,
            'priority'    => $this->priority,
            'subject'     => $this->subject,
            'started_at'  => $this->started_at?->toIso8601String(),
            'ended_at'    => $this->ended_at?->toIso8601String(),
            'followup_at' => $this->followup_at?->toIso8601String(),
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
