<?php

namespace App\Models;

use App\Enums\ChatStatus;
use App\Enums\PriorityLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'visitor_id',
        'assigned_agent_id',
        'status',
        'priority',
        'subject',
        'metadata',
        'started_at',
        'ended_at',
        'followup_at',
    ];

    protected function casts(): array
    {
        return [
            'status'      => ChatStatus::class,
            'priority'    => PriorityLevel::class,
            'metadata'    => 'array',
            'started_at'  => 'datetime',
            'ended_at'    => 'datetime',
            'followup_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(ChatTransfer::class);
    }

    public function followups(): HasMany
    {
        return $this->hasMany(Followup::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->whereIn('status', array_map(fn($s) => $s->value, ChatStatus::activeStates()));
    }

    public function scopePending($query)
    {
        return $query->where('status', ChatStatus::PENDING);
    }

    public function scopeByAgent($query, int $agentId)
    {
        return $query->where('assigned_agent_id', $agentId);
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    public function isActive(): bool
    {
        return in_array($this->status, ChatStatus::activeStates());
    }
}
