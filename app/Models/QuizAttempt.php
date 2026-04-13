<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = ['user_id', 'quiz_id', 'score', 'max_score', 'is_passed', 'started_at', 'completed_at'];
    protected $casts = [
        'is_passed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function quiz() { return $this->belongsTo(Quiz::class); }

    public function getScorePercentAttribute(): int {
        return $this->max_score > 0 ? (int) round(($this->score / $this->max_score) * 100) : 0;
    }
}
