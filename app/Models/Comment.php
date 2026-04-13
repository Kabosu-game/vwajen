<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'commentable_type', 'commentable_id', 'parent_id', 'content', 'status', 'likes_count'];

    public function user() { return $this->belongsTo(User::class); }
    public function commentable() { return $this->morphTo(); }
    public function replies() { return $this->hasMany(Comment::class, 'parent_id'); }
    public function parent() { return $this->belongsTo(Comment::class, 'parent_id'); }
    public function reports() { return $this->morphMany(Report::class, 'reportable'); }
}
