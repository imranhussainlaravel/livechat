<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'chat_id'       => $this->chat_id,
            'agent_id'      => $this->agent_id,
            'followup_time' => $this->followup_time?->toIso8601String(),
            'status'        => $this->status,
            'notes'         => $this->notes,
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
