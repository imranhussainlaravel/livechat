<?php

namespace Src\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Src\Core\StateMachine\Exceptions\InvalidTransitionException;

class Conversation extends Model
{
    protected $fillable = [
        'session_id',
        'queue',
        'state',
        'assigned_agent_id',
        'sla_state',
    ];

    public function visitorSession()
    {
        return $this->belongsTo(VisitorSession::class, 'session_id', 'session_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function slaLogs()
    {
        return $this->hasMany(SlaLog::class);
    }
}
