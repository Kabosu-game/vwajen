<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $fillable = [
        'user_id', 'type', 'status', 'department', 'commune', 'section',
        'motivation', 'referral_code', 'referred_by', 'approved_at',
        'approved_by', 'expires_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function referrer() { return $this->belongsTo(User::class, 'referred_by'); }

    public function getIsActiveAttribute(): bool {
        return $this->status === 'active' && (!$this->expires_at || $this->expires_at->isFuture());
    }
}
