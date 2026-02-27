<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentChatLoad extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_assigned_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
