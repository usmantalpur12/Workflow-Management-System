<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'ip_address', 'department_id', 'profile_picture', 'role',
    ];

    // Get the description for this user
    public function description()
    {
        return $this->morphOne(Description::class, 'describable');
    }

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function sentChats()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function receivedChats()
    {
        return $this->hasMany(Chat::class, 'recipient_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function hrDepartment()
    {
        return $this->hasOne(Department::class, 'hr_admin_id');
    }

    public function tasks()
    {
        return $this->hasManyThrough(Task::class, TaskAssignment::class, 'user_id', 'id');
    }

    // Get profile picture URL
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        // Return a default avatar using initials
        $initials = strtoupper(substr($this->name, 0, 2));
        $color = $this->getAvatarColor();
        return "data:image/svg+xml;base64," . base64_encode("
            <svg width='150' height='150' xmlns='http://www.w3.org/2000/svg'>
                <rect width='150' height='150' fill='{$color}'/>
                <text x='75' y='75' font-family='Arial, sans-serif' font-size='48' fill='white' text-anchor='middle' dy='.3em'>{$initials}</text>
            </svg>
        ");
    }

    // Get avatar color based on user ID
    private function getAvatarColor()
    {
        $colors = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
            '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
        ];
        return $colors[$this->id % count($colors)];
    }
}
