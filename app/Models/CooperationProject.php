<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CooperationProject extends Model
{
    protected $fillable = [
        'user_id',
        'title', 'description', 'cover_url', 'countries', 'sector', 'listing_type',
        'contact_email', 'organization', 'is_published', 'interests_count',
    ];

    protected $casts = ['is_published' => 'boolean'];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function interests()
    {
        return $this->hasMany(CooperationInterest::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
