<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'description', 'video_url',
        'thumbnail_url', 'duration_seconds', 'status', 'content_type',
        'is_featured', 'views_count', 'likes_count', 'shares_count',
        'comments_count', 'comments_enabled', 'algorithm_score', 'hashtags',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'comments_enabled' => 'boolean',
        'hashtags' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function comments() { return $this->morphMany(Comment::class, 'commentable'); }
    public function reports() { return $this->morphMany(Report::class, 'reportable'); }
    public function savedBy() { return $this->morphMany(SavedContent::class, 'saveable'); }

    public function likedBy() {
        return $this->belongsToMany(User::class, 'video_likes')->withTimestamps();
    }

    public function scopePublished($query) { return $query->where('status', 'published'); }

    public function scopeFeed($query) {
        return $query->published()->orderByDesc('algorithm_score')->orderByDesc('created_at');
    }
}
