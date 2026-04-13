<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveViewer extends Model
{
    protected $fillable = ['user_id', 'live_id', 'joined_at', 'left_at'];
    protected $casts = ['joined_at' => 'datetime', 'left_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function live() { return $this->belongsTo(Live::class); }
}
