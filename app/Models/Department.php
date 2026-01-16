<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Department extends Model
{
    protected $fillable = [
        'name', 'hr_admin_id',
    ];

    protected $casts = [
        'hr_admin_id' => 'integer',
    ];

    // Get the description for this department
    public function description()
    {
        return $this->morphOne(Description::class, 'describable');
    }

    // Get the HR admin for this department
    public function hrAdmin()
    {
        return $this->belongsTo(User::class, 'hr_admin_id')
            ->where('role', 'hr-admin');
    }

    // Get all users in this department
    public function users()
    {
        return $this->hasMany(User::class, 'department_id')
            ->where('role', '!=', 'hr-admin'); // Exclude HR admin from regular users list
    }

    // Get all users in this department including HR admin
    public function allUsers()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    // Get all users assigned to projects in this department
    public function projectUsers()
    {
        return $this->belongsToMany(User::class, 'project_department_user', 'department_id', 'user_id')
            ->withPivot('project_id', 'assigned_by', 'created_at')
            ->withTimestamps();
    }

    // Get all projects assigned to this department
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_department')
            ->withPivot('created_by', 'created_at')
            ->withTimestamps();
    }

    // Get all projects assigned to this department (with HR admin check)
    public function projectsWithHrAdmin()
    {
        return $this->projects()
            ->where('hr_admin_id', $this->hr_admin_id);
    }

    // Check if the given user is the HR admin for this department
    public function isHrAdmin(User $user)
    {
        return $this->hr_admin_id === $user->id;
    }

    // Check if a user has access to this department
    public function hasAccess(User $user)
    {
        // Super admins have access to all departments
        if ($user->role === 'super-admin') {
            return true;
        }

        // HR admins have access to their own department
        if ($user->role === 'hr-admin') {
            return $this->isHrAdmin($user);
        }

        // Regular users have access to their own department
        return $user->department_id === $this->id;
    }
}
