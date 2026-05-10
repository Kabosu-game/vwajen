<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MuseumEntry extends Model
{
    protected $fillable = [
        'museum_category_id',
        'name',
        'slug',
        'description',
        'portrait_url',
        'gallery',
        'is_featured',
        'is_published',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(static function (MuseumEntry $e): void {
            if (($e->slug === null || $e->slug === '') && $e->name !== '') {
                $base = Str::slug($e->name);
                $slug = $base;
                $n = 0;
                while (static::withoutGlobalScopes()->where('slug', $slug)->exists()) {
                    $slug = $base.'-'.(++$n);
                }
                $e->slug = $slug;
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MuseumCategory::class, 'museum_category_id');
    }

    /** @param  \Illuminate\Database\Eloquent\Builder  $query */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /** @param  \Illuminate\Database\Eloquent\Builder  $query */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
