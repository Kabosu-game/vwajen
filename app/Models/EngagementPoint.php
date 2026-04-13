<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EngagementPoint extends Model
{
    protected $fillable = ['user_id', 'points', 'action', 'pointable_type', 'pointable_id', 'description'];

    public function user() { return $this->belongsTo(User::class); }
    public function pointable() { return $this->morphTo(); }
}
