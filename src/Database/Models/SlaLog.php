<?php

namespace Src\Database\Models;

use Illuminate\Database\Eloquent\Model;

class SlaLog extends Model
{
    protected $fillable = ['conversation_id', 'event', 'details'];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
