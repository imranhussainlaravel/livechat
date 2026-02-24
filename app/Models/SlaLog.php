<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaLog extends Model
{
    protected $fillable = [
        'conversation_id',
        'breach_type',
        'triggered_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'triggered_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}