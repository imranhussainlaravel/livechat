<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'chat_id',
        'agent_id',
        'status',
        'quotation_sent',
        'amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status'         => TicketStatus::class,
            'quotation_sent' => 'boolean',
            'amount'         => 'decimal:2',
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

    public function scopeInterested($query)
    {
        return $query->where('status', TicketStatus::INTERESTED);
    }

    public function scopeWithQuotation($query)
    {
        return $query->where('quotation_sent', true);
    }
}
