<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user read marker for a Team Chat group channel. Records the id of the
 * newest message the user has seen in that channel, so unread counts can be
 * computed as "messages newer than this, not sent by me".
 */
class ChannelRead extends Model
{
    protected $fillable = ['user_id', 'channel', 'last_read_message_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
