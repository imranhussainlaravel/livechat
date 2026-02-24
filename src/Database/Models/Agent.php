<?php

namespace Src\Database\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'max_concurrency',
        'current_load',
        'last_activity_at',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password'         => 'hashed',
            'max_concurrency'  => 'integer',
            'current_load'     => 'integer',
            'last_activity_at' => 'datetime',
        ];
    }

    // ── Relationships ──

    public function queues()
    {
        return $this->belongsToMany(Queue::class, 'agent_queue');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'current_agent_id');
    }
}
