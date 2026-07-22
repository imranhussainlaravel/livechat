<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use LogsActivity;

    protected $fillable = ['deal_id', 'status', 'deadline', 'special_instructions', 'delivered_at'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'deadline' => 'date',
            'delivered_at' => 'datetime',
        ];
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function dispatch(): HasOne
    {
        return $this->hasOne(Dispatch::class);
    }

    public function activityLogLabel(): string
    {
        return "Order #{$this->id} ({$this->deal?->lead?->contact?->company?->name})";
    }
}
