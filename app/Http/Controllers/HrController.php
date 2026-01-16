<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use App\Models\Notification;
use App\Notifications\ProjectAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Get the department for this HR admin (using department_id from user, not hr_admin_id)
        $department = Department::where('id', $user->department_id)->first();

        if (!$department) {
            return redirect()->route('dashboard')->with('error', 'No department assigned. Please contact administrator.');
        }

        return view('dashboard.hr', compact('department', 'user'));
    }

    public function showAssignEmployees()
    {
        $user = Auth::user();

        // Get the department for this HR admin (using department_id from user, not hr_admin_id)
        $department = Department::where('id', $user->department_id)->first();

        if (!$department) {
            return redirect()->route('dashboard')->with('error', 'No department assigned. Please contact administrator.');
        }

        // Get all projects assigned to this department via project_department pivot
        $projects = Project::whereHas('departments', function($query) use ($department) {
            $query->where('departments.id', $department->id);
        })->with(['departments', 'departmentUsers'])->get();
        
        // Get all users in this department (excluding HR admin themselves)
        $employees = $department->users;

        return view('hr.assign-employees', compact('department', 'projects', 'employees'));
    }

    public function assignEmployeesToProject(Request $request)
    {
        $user = Auth::user();

        // Validate and assign employees
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Get the project
        $project = Project::findOrFail($validated['project_id']);
        
        // Get the department for this HR admin (using department_id from user)
        $department = Department::where('id', $user->department_id)->first();

        if (!$department) {
            return response()->json(['error' => 'No department assigned'], 403);
        }

        // Verify HR admin's department is assigned to this project
        $projectDepartment = $project->departments()->where('departments.id', $department->id)->first();
        
        if (!$projectDepartment) {
            return response()->json(['error' => 'Unauthorized access. This project is not assigned to your department.'], 403);
        }

        // Filter users to ensure they belong to the department
        $validUsers = User::whereIn('id', $validated['user_ids'])
            ->where('department_id', $department->id)
            ->get();

        // Assign users to the project via project_department_user pivot
        foreach ($validUsers as $employee) {
            // Check if already assigned
            $exists = $project->departmentUsers()
                ->wherePivot('department_id', $department->id)
                ->wherePivot('user_id', $employee->id)
                ->exists();
            
            if (!$exists) {
                $project->departmentUsers()->attach($employee->id, [
                    'department_id' => $department->id,
                    'assigned_by' => Auth::id(),
                ]);
            }

            // Send email notification to the employee
            $employee->notify(new ProjectAssignedNotification($project));

            // Create database notification
            Notification::create([
                'user_id' => $employee->id,
                'type' => 'project_assigned',
                'message' => "You have been assigned to project '{$project->name}'",
                'data' => json_encode([
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'assigned_by' => Auth::user()->name,
                ]),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Employees assigned successfully with notifications sent']);
    }

    public function getAssignedEmployees($projectId)
    {
        $user = Auth::user();

        // Get the department for this HR admin
        $department = Department::where('id', $user->department_id)->first();

        if (!$department) {
            return response()->json(['error' => 'No department assigned'], 403);
        }

        // Get the project
        $project = Project::findOrFail($projectId);
        
        // Verify HR admin's department is assigned to this project
        $projectDepartment = $project->departments()->where('departments.id', $department->id)->first();
        
        if (!$projectDepartment) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Get all users assigned to this project from the department via project_department_user
        $employees = $project->departmentUsers()
            ->wherePivot('department_id', $department->id)
            ->with('roles')
            ->get()
            ->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'role' => $employee->roles->first()->name ?? ($employee->role ?? 'N/A')
                ];
            });

        return response()->json(['employees' => $employees]);
    }

    public function removeEmployeeFromProject($projectId, $userId)
    {
        $user = Auth::user();

        // Get the department for this HR admin
        $department = Department::where('id', $user->department_id)->first();

        if (!$department) {
            return response()->json(['error' => 'No department assigned'], 403);
        }

        // Get the project
        $project = Project::findOrFail($projectId);
        
        // Verify HR admin's department is assigned to this project
        $projectDepartment = $project->departments()->where('departments.id', $department->id)->first();
        
        if (!$projectDepartment) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Remove the user from the project via project_department_user pivot
        $project->departmentUsers()
            ->wherePivot('department_id', $department->id)
            ->wherePivot('user_id', $userId)
            ->detach();

        return response()->json(['success' => true, 'message' => 'Employee removed successfully']);
    }
}
