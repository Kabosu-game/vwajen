<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by', 'title', 'description', 'image_url', 'video_url',
        'link_url', 'type', 'placement', 'status', 'start_date', 'end_date',
        'impressions_count', 'clicks_count', 'budget', 'rejection_reason', 'approved_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function scopeActive($query) { return $query->where('status', 'active'); }
}
