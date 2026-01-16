<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'super-admin') {
            $departments = Department::with(['hrAdmin', 'users', 'projects'])
                ->get()
                ->map(function($department) {
                    // If hrAdmin is null, set a default name
                    if (!$department->hrAdmin) {
                        $department->hrAdmin = new \stdClass();
                        $department->hrAdmin->name = 'Not Assigned';
                    }
                    return $department;
                });
        } else {
            $departments = Department::where('id', $user->department_id)
                ->with(['hrAdmin', 'users', 'projects', 'description'])
                ->get()
                ->map(function($department) {
                    if (!$department->hrAdmin) {
                        $department->hrAdmin = new \stdClass();
                        $department->hrAdmin->name = 'Not Assigned';
                    }
                    return $department;
                });
        }
        
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        // Only super-admin can create departments
        if (Auth::user()->role !== 'super-admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $hrAdmins = User::where('role', 'hr-admin')->get();
        return view('departments.create', compact('hrAdmins'));
    }

    public function store(Request $request)
    {
        // Only super-admin can create departments
        if (Auth::user()->role !== 'super-admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hr_admin_id' => 'nullable|exists:users,id',
        ]);

        $department = Department::create([
            'name' => $request->name,
            'hr_admin_id' => $request->hr_admin_id,
        ]);

        // Create description if provided
        if ($request->description) {
            $department->description()->create([
                'content' => $request->description,
            ]);
        }

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully!');
    }

    public function edit(Department $department)
    {
        $user = Auth::user();
        
        if ($user->role !== 'super-admin' && !$department->isHrAdmin($user)) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        $hrAdmins = User::where('role', 'hr-admin')->get();
        return view('departments.edit', compact('department', 'hrAdmins'));
    }

    public function update(Request $request, Department $department)
    {
        $user = Auth::user();
        
        if ($user->role !== 'super-admin' && !$department->isHrAdmin($user)) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hr_admin_id' => 'nullable|exists:users,id',
        ]);

        $department->update($validated);
        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully');
    }

    public function show(Department $department)
    {
        $user = Auth::user();
        
        if ($user->role !== 'super-admin' && !$department->isHrAdmin($user)) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
        $department->load(['hrAdmin', 'users', 'projects']);
        return view('departments.show', compact('department'));
    }

    public function destroy(Department $department)
    {
        $user = Auth::user();
        
        if ($user->role !== 'super-admin' && !$department->isHrAdmin($user)) {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $department->delete();
        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully');
    }
}
