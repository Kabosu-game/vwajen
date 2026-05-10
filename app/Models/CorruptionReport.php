<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorruptionReport extends Model
{
    protected $fillable = [
        'anonymous_token', 'category', 'title', 'description',
        'documents', 'location', 'period',
        'is_verified', 'status', 'moderator_note', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'documents' => 'array',
        'is_verified' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    // Jamais de relation directe avec l'utilisateur — anonymat total
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
