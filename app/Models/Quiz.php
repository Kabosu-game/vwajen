<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['course_id', 'title', 'description', 'pass_score', 'time_limit_minutes', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function course() { return $this->belongsTo(Course::class); }
    public function questions() { return $this->hasMany(Question::class)->orderBy('order'); }
    public function attempts() { return $this->hasMany(QuizAttempt::class); }
}
