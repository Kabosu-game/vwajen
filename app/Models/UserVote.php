<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVote extends Model
{
    protected $fillable = ['user_id', 'vote_id', 'vote_option_id'];

    public function user() { return $this->belongsTo(User::class); }
    public function vote() { return $this->belongsTo(Vote::class); }
    public function option() { return $this->belongsTo(VoteOption::class, 'vote_option_id'); }
}
