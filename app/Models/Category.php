<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'type', 'is_active', 'order'];

    protected $casts = ['is_active' => 'boolean'];

    public function courses() { return $this->hasMany(Course::class); }
    public function videos() { return $this->hasMany(Video::class); }
    public function events() { return $this->hasMany(Event::class); }
}
