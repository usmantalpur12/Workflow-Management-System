<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'title', 'description', 'due_date', 'status', 'priority', 'progress', 'department_id',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Get the departments this task belongs to through its project
    public function departments()
    {
        return $this->belongsTo(Project::class)->departments();
    }

    // Check if the user has access to this task
    public function hasAccess(User $user)
    {
        // Super admins have access to all tasks
        if ($user->role === 'super-admin') {
            return true;
        }

        // HR admins have access if they manage any department this task belongs to
        if ($user->role === 'hr-admin') {
            return $this->project->departments()->where('hr_admin_id', $user->id)->exists();
        }

        // Regular users have access if they are assigned to this task
        return $this->users()->where('id', $user->id)->exists();
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id')
            ->withPivot('assigned_by', 'notes', 'created_at');
    }

    public function clockLogs()
    {
        return $this->hasMany(TaskClockLog::class);
    }

    public function isOverdue()
    {
        return $this->due_date < now() && $this->status !== 'completed';
    }

    public function getProgressColorAttribute()
    {
        if ($this->status === 'completed') {
            return 'success';
        }
        if ($this->isOverdue()) {
            return 'danger';
        }
        return match($this->progress) {
            0 => 'secondary',
            $this->progress < 50 => 'warning',
            $this->progress < 100 => 'info',
            default => 'primary',
        };
    }
}