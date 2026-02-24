<?php

namespace Src\Database\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'visitor_id',
        'current_agent_id',
        'queue_id',
        'state',
        'priority',
        'sla_status',
        'subject',
        'metadata',
        'first_response_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'          => 'array',
            'first_response_at' => 'datetime',
            'resolved_at'       => 'datetime',
        ];
    }

    // ── Relationships ──

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'current_agent_id');
    }

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function slaLogs()
    {
        return $this->hasMany(SlaLog::class);
    }
}
