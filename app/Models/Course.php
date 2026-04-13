<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'created_by', 'title', 'slug', 'description',
        'thumbnail', 'level', 'type', 'duration_minutes', 'is_published',
        'is_featured', 'is_free', 'points_reward', 'enrollments_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_free' => 'boolean',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function lessons() { return $this->hasMany(Lesson::class)->orderBy('order'); }
    public function quizzes() { return $this->hasMany(Quiz::class); }
    public function enrollments() { return $this->hasMany(CourseEnrollment::class); }
    public function certifications() { return $this->hasMany(Certification::class); }

    public function enrolledUsers() {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withPivot(['progress_percent', 'completed_at'])->withTimestamps();
    }

    public function getThumbnailUrlAttribute(): ?string {
        return $this->thumbnail ? asset('storage/'.$this->thumbnail) : null;
    }
}
