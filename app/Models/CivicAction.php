<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CivicAction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by', 'title', 'slug', 'description', 'thumbnail',
        'type', 'status', 'location', 'latitude', 'longitude',
        'action_date', 'participants_needed', 'participants_count', 'is_featured',
    ];

    protected $casts = [
        'action_date' => 'datetime',
        'is_featured' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function reports() { return $this->morphMany(Report::class, 'reportable'); }

    public function participants() {
        return $this->belongsToMany(User::class, 'civic_action_participants')
            ->withPivot(['role', 'status'])->withTimestamps();
    }

    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopePlanned($query) { return $query->where('status', 'planned'); }
}
