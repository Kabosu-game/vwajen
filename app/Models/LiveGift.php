<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveGift extends Model
{
    protected $fillable = ['live_id', 'sender_id', 'gift_type', 'value'];

    public function live(): BelongsTo
    {
        return $this->belongsTo(Live::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
