<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by', 'category_id', 'title', 'slug', 'description',
        'thumbnail', 'type', 'status', 'location', 'latitude', 'longitude',
        'start_date', 'end_date', 'max_participants', 'participants_count',
        'is_free', 'is_featured',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_free' => 'boolean',
        'is_featured' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /** @var list<string> */
    protected $appends = [
        'thumbnail_url',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function category() { return $this->belongsTo(Category::class); }
    public function comments() { return $this->morphMany(Comment::class, 'commentable'); }
    public function reports() { return $this->morphMany(Report::class, 'reportable'); }

    public function participants() {
        return $this->belongsToMany(User::class, 'event_participations')
            ->withPivot('status')->withTimestamps();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'published')->where('start_date', '>', now());
    }

    /**
     * Événements publiés non terminés (pour l’app : inclut le jour J et la fin de série).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeVisibleOnApp($query)
    {
        return $query->where('status', 'published')
            ->where(function ($outer): void {
                $outer->where(function ($q): void {
                    $q->whereNotNull('end_date')
                        ->where('end_date', '>=', now());
                })->orWhere(function ($q): void {
                    $q->whereNull('end_date')
                        ->whereNotNull('start_date')
                        ->where('start_date', '>=', now()->startOfDay());
                });
            });
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $raw = $this->attributes['thumbnail'] ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }
        if (Str::startsWith($raw, ['http://', 'https://', '//'])) {
            return $raw;
        }

        return Storage::disk('public')->url(ltrim((string) $raw, '/'));
    }

    public function getIsFullAttribute(): bool {
        return $this->max_participants && $this->participants_count >= $this->max_participants;
    }
}
