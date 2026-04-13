<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'course_id', 'title', 'description', 'content',
        'video_url', 'duration_minutes', 'order', 'is_published',
    ];

    protected $casts = ['is_published' => 'boolean'];

    public function course() { return $this->belongsTo(Course::class); }
    public function progress() { return $this->hasMany(LessonProgress::class); }
}
