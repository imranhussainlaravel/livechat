<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'agent_queue');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
