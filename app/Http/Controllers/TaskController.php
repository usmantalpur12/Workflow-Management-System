<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskClockLog;
use App\Models\Notification;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        // Only HR admins and super-admins can create tasks
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:low,medium,high',
            'department_id' => 'required|exists:departments,id',
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'due_date' => $validated['due_date'],
            'priority' => $validated['priority'],
            'department_id' => $validated['department_id'],
            'status' => 'pending',
            'progress' => 0,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Task created successfully');
    }

    public function assign(Request $request, Task $task)
    {
        // Only department heads or HR admins can assign tasks
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin', 'department-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'notes' => 'nullable|string',
        ]);

        foreach ($validated['user_ids'] as $userId) {
            // Check if user is in the same department
            $user = User::findOrFail($userId);
            if ($user->department !== $task->department) {
                return redirect()->back()->with('error', 'Cannot assign task to user from different department');
            }

            TaskAssignment::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'assigned_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Notify assigned user
            Notification::create([
                'user_id' => $userId,
                'type' => 'task_assigned',
                'message' => "You have been assigned to task '{$task->title}' in project '{$task->project->name}'",
                'data' => [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id
                ],
            ]);
        }

        return redirect()->route('projects.show', $task->project)
            ->with('success', 'Task assigned successfully');
    }

    public function update(Request $request, Task $task)
    {
        // Only HR admins, super-admins, and assigned users can update tasks
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin']) &&
            !$task->assignedUsers()->where('user_id', Auth::id())->exists()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $task->update($validated);

        // If task is completed, notify relevant parties
        if ($validated['status'] === 'completed') {
            $project = $task->project;
            $hrAdmins = $project->hrAdmins;
            $superAdmins = User::whereHas('roles', function ($query) {
                $query->where('name', 'super-admin');
            })->get();

            // Notify HR admins and super admins
            foreach (array_merge($hrAdmins->toArray(), $superAdmins->toArray()) as $user) {
                Notification::create([
                    'user_id' => $user['id'],
                    'type' => 'task_completed',
                    'message' => "Task '{$task->title}' has been completed",
                    'data' => ['task_id' => $task->id],
                ]);
            }

            // Notify senior officers if they exist
            $seniorOfficers = $task->assignedUsers()->whereHas('roles', function ($query) {
                $query->where('name', 'senior-officer');
            })->get();

            foreach ($seniorOfficers as $senior) {
                Notification::create([
                    'user_id' => $senior->id,
                    'type' => 'task_completed',
                    'message' => "Task '{$task->title}' has been completed",
                    'data' => ['task_id' => $task->id],
                ]);
            }
        }

        return redirect()->route('projects.show', $task->project)
            ->with('success', 'Task updated successfully');
    }

    public function clockIn(Task $task)
    {
        // Only assigned users can clock in
        if (!$task->assignedUsers()->where('user_id', Auth::id())->exists()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Check if user already has an active clock log
        $activeLog = TaskClockLog::where('task_id', $task->id)
            ->where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->first();

        if ($activeLog) {
            return redirect()->back()->with('error', 'You already have an active clock log');
        }

        TaskClockLog::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'clock_in' => now(),
        ]);

        return redirect()->route('projects.show', $task->project)
            ->with('success', 'Clock-in successful');
    }

    public function clockOut(Task $task)
    {
        // Only assigned users can clock out
        if (!$task->assignedUsers()->where('user_id', Auth::id())->exists()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $activeLog = TaskClockLog::where('task_id', $task->id)
            ->where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->first();

        if (!$activeLog) {
            return redirect()->back()->with('error', 'No active clock log found');
        }

        $activeLog->update(['clock_out' => now()]);

        return redirect()->route('projects.show', $task->project)
            ->with('success', 'Clock-out successful');
    }

    public function destroy(Task $task)
    {
        // Only HR admins and super-admins can delete tasks
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $project = $task->project;
        $task->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Task deleted successfully');
    }
}
