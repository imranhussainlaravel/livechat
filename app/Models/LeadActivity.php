<?php

namespace App\Models;

use App\Enums\LeadActivityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['lead_id', 'user_id', 'type', 'note'];

    protected function casts(): array
    {
        return [
            'type' => LeadActivityType::class,
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
