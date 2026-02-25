<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user'           => new UserResource($this->whenLoaded('user')),
            'action'         => $this->action,
            'reference_type' => $this->reference_type,
            'reference_id'   => $this->reference_id,
            'metadata'       => $this->metadata,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
