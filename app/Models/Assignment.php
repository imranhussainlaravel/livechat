<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'conversation_id',
        'agent_id',
        'assigned_at',
    ];

    public $timestamps = false; // assigned_at managed manually

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}