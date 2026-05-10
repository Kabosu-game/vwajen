<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'text', 'type', 'images', 'video_url',
        'is_published', 'likes_count', 'comments_count', 'shares_count',
    ];

    protected $casts = [
        'images' => 'array',
        'is_published' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function comments() { return $this->morphMany(Comment::class, 'commentable'); }
    public function reports() { return $this->morphMany(Report::class, 'reportable'); }

    public function likedBy() {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    public function scopePublished($query) { return $query->where('is_published', true); }
}
