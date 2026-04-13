<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Live extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'description', 'thumbnail', 'stream_key',
        'stream_url', 'playback_url', 'type', 'status', 'scheduled_at',
        'started_at', 'ended_at', 'viewers_count', 'peak_viewers',
        'likes_count', 'is_recorded', 'recording_url',
    ];

    protected $casts = [
        'is_recorded' => 'boolean',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function viewers() { return $this->hasMany(LiveViewer::class); }
    public function comments() { return $this->morphMany(Comment::class, 'commentable'); }
    public function reports() { return $this->morphMany(Report::class, 'reportable'); }
    public function savedBy() { return $this->morphMany(SavedContent::class, 'saveable'); }

    public function scopeLive($query) { return $query->where('status', 'live'); }
    public function scopeScheduled($query) { return $query->where('status', 'scheduled'); }
}
