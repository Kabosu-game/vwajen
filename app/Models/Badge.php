<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'type', 'points_required', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function users() {
        return $this->belongsToMany(User::class, 'user_badges')->withPivot('awarded_at')->withTimestamps();
    }
}
