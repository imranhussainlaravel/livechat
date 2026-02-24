<?php

namespace Src\Database\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'agent_id',
        'conversation_id',
        'level',
        'status',
        'sent_at',
    ];

    public $timestamps = false;
    
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}