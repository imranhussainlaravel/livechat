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
    ];

    protected function casts(): array
    {
        return [
            'followup_time' => 'datetime',
            'status'        => FollowupStatus::class,
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
