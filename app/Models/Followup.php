<?php

namespace App\Models;

use App\Enums\FollowupStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Followup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'chat_id',
        'agent_id',
        'followup_time',
        'status',
        'notes',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status'           => FollowupStatus::class,
            'followup_time'    => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopePending($query)
    {
        return $query->where('status', FollowupStatus::PENDING);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', FollowupStatus::PENDING)
            ->where('followup_time', '<=', now());
    }
}
