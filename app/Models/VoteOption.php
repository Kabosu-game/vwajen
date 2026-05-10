<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoteOption extends Model
{
    protected $fillable = ['vote_id', 'label', 'description', 'votes_count', 'percentage', 'order'];

    protected $casts = ['percentage' => 'decimal:2'];

    public function vote() { return $this->belongsTo(Vote::class); }
    public function userVotes() { return $this->hasMany(UserVote::class); }
}
