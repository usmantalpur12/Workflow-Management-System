<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Project;

class CheckProjectAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // Super admins have access to all projects
        if ($user->role === 'super-admin') {
            return $next($request);
        }

        // Get the project ID from the route
        $projectId = $request->route('project');
        
        if (!$projectId) {
            return redirect()->back()->with('error', 'Project not found');
        }

        // Get the project
        $project = Project::find($projectId);
        
        if (!$project) {
            return redirect()->back()->with('error', 'Project not found');
        }

        // Check if user has access to this project
        if (!$project->hasAccess($user)) {
            return redirect()->back()->with('error', 'Unauthorized access to this project');
        }

        return $next($request);
    }
}
