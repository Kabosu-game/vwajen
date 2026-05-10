<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'creator_id', 'title', 'description', 'cover_url', 'images',
        'video_url', 'is_published', 'is_featured', 'supports_count',
        'comments_count', 'category', 'status',
    ];

    protected $casts = [
        'images' => 'array',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function creator() { return $this->belongsTo(User::class, 'creator_id'); }
    public function comments() { return $this->morphMany(Comment::class, 'commentable'); }

    public function supportedBy() {
        return $this->belongsToMany(User::class, 'project_supports')->withTimestamps();
    }

    public function scopePublished($query) { return $query->where('is_published', true); }
}
