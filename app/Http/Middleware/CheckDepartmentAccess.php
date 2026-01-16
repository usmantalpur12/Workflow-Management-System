<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Department;

class CheckDepartmentAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // Super admins have access to all departments
        if ($user->role === 'super-admin') {
            return $next($request);
        }

        // For HR dashboard access
        if ($request->is('hr/*') && $user->role === 'hr-admin') {
            $department = $user->hrDepartment()->first();
            if (!$department) {
                return redirect()->route('dashboard')->with('error', 'No department assigned to HR admin');
            }
            $request->session()->put('current_department', $department);
            return $next($request);
        }

        // Get the department ID from the route
        $departmentId = $request->route('department');
        
        if (!$departmentId) {
            return redirect()->route('dashboard')->with('error', 'Department not found');
        }

        // Get the department
        $department = Department::find($departmentId);
        
        if (!$department) {
            return redirect()->route('dashboard')->with('error', 'Department not found');
        }

        // Check if user has access to this department
        if (!$department->hasAccess($user)) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to this department');
        }

        // Set the current department in the session for use in views
        $request->session()->put('current_department', $department);

        return $next($request);
    }
}
