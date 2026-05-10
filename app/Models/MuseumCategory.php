<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MuseumCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(static function (MuseumCategory $c): void {
            if (($c->slug === null || $c->slug === '') && $c->name !== '') {
                $base = Str::slug($c->name);
                $slug = $base;
                $n = 0;
                while (static::withoutGlobalScopes()->where('slug', $slug)->exists()) {
                    $slug = $base.'-'.(++$n);
                }
                $c->slug = $slug;
            }
        });
    }

    public function museumEntries(): HasMany
    {
        return $this->hasMany(MuseumEntry::class, 'museum_category_id');
    }

    public function publishedEntries(): HasMany
    {
        return $this->museumEntries()
            ->where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderBy('name');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
