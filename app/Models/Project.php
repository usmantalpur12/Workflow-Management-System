<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name', 'description', 'start_date', 'end_date', 'status', 'priority', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'project_department')
            ->withPivot('created_by', 'created_at')
            ->withTimestamps();
    }

    // Get the description for this project
    public function description()
    {
        return $this->morphOne(Description::class, 'describable');
    }

    // Check if the user has access to this project
    public function hasAccess(User $user)
    {
        // Super admins have access to all projects
        if ($user->role === 'super-admin') {
            return true;
        }

        // HR admins have access if they manage any department this project belongs to
        if ($user->role === 'hr-admin') {
            return $this->departments()->where('hr_admin_id', $user->id)->exists();
        }

        // Regular users have access if they are assigned to this project
        return $this->users()->where('id', $user->id)->exists();
    }

    /**
     * All users assigned to this project across all departments.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'project_department_user', 'project_id', 'user_id')
            ->using(ProjectDepartmentUser::class)
            ->withPivot('department_id', 'assigned_by', 'created_at', 'updated_at');
    }

    public function departmentUsers()
    {
        return $this->belongsToMany(User::class, 'project_department_user', 'project_id', 'user_id')
            ->using(ProjectDepartmentUser::class)
            ->withPivot('department_id', 'assigned_by', 'created_at', 'updated_at');
    }

    public function usersByDepartment(Department $department)
    {
        return $this->belongsToMany(User::class, 'project_department_user', 'project_id', 'user_id')
            ->wherePivot('department_id', $department->id)
            ->withPivot('department_id', 'assigned_by', 'created_at', 'updated_at');
    }

    // Get the HR admin for this project's department
    public function hrAdmin()
    {
        return $this->belongsTo(User::class, 'hr_admin_id')
            ->where('role', 'hr-admin');
    }

    public function getProgressAttribute()
    {
        if ($this->tasks->isEmpty()) {
            return 0;
        }
        return $this->tasks->avg('progress') ?? 0;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'completed' => 'success',
            'in_progress' => 'primary',
            'planning' => 'info',
            'on_hold' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}