<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use App\Models\Notification;
use App\Notifications\ProjectAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'super-admin') {
            // Super admin can see all projects
            $projects = Project::with(['tasks', 'departments', 'departmentUsers'])->get();
        } elseif ($user->role === 'hr-admin') {
            // HR admin can see projects from departments they manage
            $projects = Project::whereHas('departments', function($query) use ($user) {
                $query->where('hr_admin_id', $user->id);
            })->with(['tasks', 'departments', 'departmentUsers'])->get();
        } elseif ($user->role === 'department-admin') {
            // Department admin can see projects from their department
            $projects = Project::whereHas('departments', function($query) use ($user) {
                $query->where('departments.id', $user->department_id);
            })->with(['tasks', 'departments', 'departmentUsers'])->get();
        } else {
            // Regular users (e.g. teacher, student, employee) can see:
            // - projects they are explicitly assigned to via project_department_user
            // - projects that contain tasks assigned to them
            $projects = Project::where(function ($query) use ($user) {
                $query->whereHas('departmentUsers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orWhereHas('tasks.assignedUsers', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })->with(['tasks', 'departments', 'departmentUsers'])->get();
        }

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        // Only super-admin or HR admin can create projects
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Super admin: can see all departments
        // HR admin: can only create projects for their own department
        if (Auth::user()->role === 'super-admin') {
            $departments = Department::all();
        } else {
            $departments = Department::where('id', Auth::user()->department_id)->get();
        }

        return view('projects.create', compact('departments'));
    }

    public function store(Request $request)
    {
        // Only super-admin or HR admin can create projects
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Base validation
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:planning,in_progress,completed,on_hold',
            'priority' => 'required|string|in:low,medium,high',
        ];

        // Super admin must explicitly select departments
        if (Auth::user()->role === 'super-admin') {
            $rules['department_ids'] = 'required|array';
            $rules['department_ids.*'] = 'exists:departments,id';
        }

        $request->validate($rules);

        // Determine departments to attach
        if (Auth::user()->role === 'super-admin') {
            $departmentIds = $request->department_ids;
        } else {
            // HR admin: automatically use their own department
            if (!Auth::user()->department_id) {
                return redirect()->back()->withInput()->with('error', 'Your user is not linked to any department.');
            }
            $departmentIds = [Auth::user()->department_id];
        }

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'priority' => $request->priority,
            'created_by' => Auth::id(),
        ]);

        // Assign departments
        $project->departments()->attach($departmentIds, [
            'created_by' => Auth::id(),
        ]);

        // Notify department heads
        foreach ($departmentIds as $departmentId) {
            $department = Department::findOrFail($departmentId);
            $departmentHead = $department->users()->whereHas('roles', function($query) {
                $query->where('name', 'department-admin');
            })->first();

            if ($departmentHead) {
                $departmentHead->notify(new ProjectAssignedNotification($project));
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Project created successfully!',
                'project' => $project
            ]);
        }
        
        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }

    public function edit(Project $project)
    {
        // Check if user is admin or super-admin
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin', 'department-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        // Check if user is admin or super-admin
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin', 'department-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:planning,in_progress,completed,on_hold,cancelled',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'priority' => $request->priority ?? $project->priority,
        ]);

        // Update department assignments if provided
        if ($request->has('department_ids')) {
            $project->departments()->sync($request->department_ids);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully!',
                'project' => $project
            ]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully!');
    }

    public function showAssignDepartmentUsers(Project $project, Department $department)
    {
        // Only HR-Admin of this department can manage its users for the project
        if (Auth::user()->role !== 'hr-admin' || Auth::user()->department_id !== $department->id) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        return view('projects.assign-department-users', compact('project', 'department'));
    }

    public function assignDepartmentUsers(Request $request, Project $project, Department $department)
    {
        // Only HR-Admin of this department can assign users
        if (Auth::user()->role !== 'hr-admin' || Auth::user()->department_id !== $department->id) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Get current department users
        $currentUsers = $project->usersByDepartment($department)->pluck('user_id')->toArray();

        // Get new users to add
        $newUsers = array_diff($request->user_ids, $currentUsers);

        // Get users to remove
        $usersToRemove = array_diff($currentUsers, $request->user_ids);

        // Add new users
        foreach ($newUsers as $userId) {
            $project->departmentUsers()->attach([
                'department_id' => $department->id,
                'user_id' => $userId,
                'assigned_by' => Auth::id(),
            ]);
        }

        // Remove users
        foreach ($usersToRemove as $userId) {
            $project->departmentUsers()
                ->wherePivot('department_id', $department->id)
                ->wherePivot('user_id', $userId)
                ->detach();
        }

        return redirect()
            ->route('projects.assign-department-users', [$project, $department])
            ->with('success', 'Department users updated successfully!');
    }

    public function show(Project $project)
    {
        // Check if user is admin or assigned to project
        $user = Auth::user();

        if (!in_array($user->role, ['super-admin', 'hr-admin', 'department-admin']) &&
            !$project->departmentUsers()->where('user_id', $user->id)->exists()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Load relationships to avoid N+1 queries and ensure proper data access
        $project->load([
            'departments',
            'departmentUsers',
            'creator'
        ]);

        return view('projects.show', compact('project'));
    }

    public function assignUser(Request $request, Project $project)
    {
        // Check if user is admin or super-admin
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin', 'department-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $project->users()->sync($request->user_ids);
        return redirect()->back()->with('success', 'Users assigned to project successfully!');
    }

    public function updateProgress(Request $request, Task $task)
    {
        // Check if user is assigned to the project or is admin
        $user = Auth::user();
        $project = $task->project;

        if (!in_array($user->role, ['super-admin', 'hr-admin', 'department-admin']) &&
            !$project->departmentUsers()->where('user_id', $user->id)->exists()) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $task->update(['progress' => $request->progress]);
        return redirect()->back()->with('success', 'Task progress updated successfully!');
    }



    public function destroy(Project $project)
    {
        // Check if user is admin or super-admin
        if (!in_array(Auth::user()->role, ['super-admin', 'hr-admin', 'department-admin'])) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }
}
