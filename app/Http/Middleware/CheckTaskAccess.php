<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Task;

class CheckTaskAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        // Super admins have access to all tasks
        if ($user->role === 'super-admin') {
            return $next($request);
        }

        // Get the task ID from the route
        $taskId = $request->route('task');
        
        if (!$taskId) {
            return redirect()->back()->with('error', 'Task not found');
        }

        // Get the task
        $task = Task::find($taskId);
        
        if (!$task) {
            return redirect()->back()->with('error', 'Task not found');
        }

        // Check if user has access to this task
        if (!$task->hasAccess($user)) {
            return redirect()->back()->with('error', 'Unauthorized access to this task');
        }

        return $next($request);
    }
}
