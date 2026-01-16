<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Check if user is admin
        if (Auth::user()->role !== 'super-admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $users = User::with('department')->get();
        $departments = Department::all();
        return view('users.index', compact('users', 'departments'));
    }

    public function create()
    {
        // Check if user is admin
        if (Auth::user()->role !== 'super-admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $departments = Department::all();
        return view('users.create', compact('departments'));
    }

    public function store(Request $request)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'super-admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:employee,hr-admin,department-admin,teacher,student',
            'department_id' => 'nullable|exists:departments,id',
            'ip_address' => 'nullable|ip',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'department_id' => $request->department_id,
            'ip_address' => $request->ip_address,
        ]);

        // If user is HR admin, assign them to the department
        if ($request->role === 'hr-admin' && $request->department_id) {
            Department::find($request->department_id)->update(['hr_admin_id' => $user->id]);
        }

        return redirect()->back()->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'super-admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'super-admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:employee,hr-admin,department-admin,teacher,student',
            'department_id' => 'nullable|exists:departments,id',
            'ip_address' => 'nullable|ip',
        ]);

        // Update user details
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department_id' => $request->department_id,
            'ip_address' => $request->ip_address,
        ]);

        // Sync role using Laravel's role management
        $user->syncRoles([$request->role]);

        // If user is HR admin and department changed
        if ($request->role === 'hr-admin' && $request->department_id) {
            // Remove from previous department if any
            if ($user->hrDepartment()) {
                $user->hrDepartment()->update(['hr_admin_id' => null]);
            }
            // Assign to new department
            Department::find($request->department_id)->update(['hr_admin_id' => $user->id]);
        }

        return redirect()->back()->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'super-admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Don't allow admin to delete themselves
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account!');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully!');
    }
}