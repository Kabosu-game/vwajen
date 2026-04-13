<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'phone',
        'avatar', 'bio', 'location', 'birth_date', 'gender',
        'status', 'is_admin', 'is_verified', 'engagement_level',
        'gjka_member_id', 'gjka_member_since', 'fcm_token',
    ];

    protected $hidden = ['password', 'remember_token', 'fcm_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'gjka_member_since' => 'datetime',
        'birth_date' => 'date',
        'is_admin' => 'boolean',
        'is_verified' => 'boolean',
        'password' => 'hashed',
    ];

    // Relations
    public function videos() { return $this->hasMany(Video::class); }
    public function courses() { return $this->hasMany(Course::class, 'created_by'); }
    public function lives() { return $this->hasMany(Live::class); }
    public function events() { return $this->hasMany(Event::class, 'created_by'); }
    public function civicActions() { return $this->hasMany(CivicAction::class, 'created_by'); }
    public function membership() { return $this->hasOne(Membership::class); }
    public function badges() { return $this->belongsToMany(Badge::class, 'user_badges')->withPivot('awarded_at'); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function reports() { return $this->hasMany(Report::class, 'reporter_id'); }
    public function certifications() { return $this->hasMany(Certification::class); }
    public function engagementPoints() { return $this->hasMany(EngagementPoint::class); }
    public function savedContents() { return $this->hasMany(SavedContent::class); }

    public function followers() {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id');
    }

    public function following() {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id');
    }

    public function enrolledCourses() {
        return $this->belongsToMany(Course::class, 'course_enrollments')
            ->withPivot(['progress_percent', 'completed_at'])->withTimestamps();
    }

    public function likedVideos() {
        return $this->belongsToMany(Video::class, 'video_likes')->withTimestamps();
    }

    public function participatedEvents() {
        return $this->belongsToMany(Event::class, 'event_participations')
            ->withPivot('status')->withTimestamps();
    }

    public function participatedCivicActions() {
        return $this->belongsToMany(CivicAction::class, 'civic_action_participants')
            ->withPivot(['role', 'status'])->withTimestamps();
    }

    // Accesseurs
    public function getAvatarUrlAttribute(): string {
        return $this->avatar ? asset('storage/'.$this->avatar) : asset('images/default-avatar.png');
    }

    public function getTotalPointsAttribute(): int {
        return $this->engagementPoints()->sum('points');
    }

    public function getIsActiveGjkaMemberAttribute(): bool {
        return $this->membership?->status === 'active';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin && $this->status === 'active';
    }
}
