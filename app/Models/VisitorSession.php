<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    protected $fillable = [
        'session_id',
        'visitor_name',
        'visitor_email',
        'metadata',
        'last_activity_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_activity_at' => 'datetime',
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'session_id', 'session_id');
    }
}