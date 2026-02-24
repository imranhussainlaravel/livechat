<?php

namespace Src\Database\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['channel', 'recipient', 'message', 'status', 'metadata'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
