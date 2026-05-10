<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by', 'title', 'description', 'gallery', 'thumbnail',
        'status', 'is_published', 'is_anonymous',
        'start_date', 'end_date', 'total_votes_count',
    ];

    protected $casts = [
        'gallery' => 'array',
        'is_published' => 'boolean',
        'is_anonymous' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function options() { return $this->hasMany(VoteOption::class)->orderBy('order'); }
    public function userVotes() { return $this->hasMany(UserVote::class); }

    public function scopePublished($query) { return $query->where('is_published', true); }
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            });
    }
}
