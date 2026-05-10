<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CooperationInterest extends Model
{
    protected $fillable = ['user_id', 'cooperation_project_id', 'message'];

    public function user() { return $this->belongsTo(User::class); }
    public function project() { return $this->belongsTo(CooperationProject::class, 'cooperation_project_id'); }
}
