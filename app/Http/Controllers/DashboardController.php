<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Redirect HR admins to their specific dashboard
        if ($user->role === 'hr-admin') {
            return redirect()->route('hr.dashboard');
        }

        // Get all required statistics
        $data = [
            'totalProjects' => Project::count(),
            'totalUsers' => User::count(),
            'completedProjects' => Project::where('status', 'completed')->count(),
            'overdueProjects' => Project::where('status', '!=', 'completed')
                ->where('end_date', '<', now())
                ->count(),
            'recentActivities' => Project::with(['departments'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($project) {
                    $department = $project->departments->first(); // Get first department
                    return (object)[
                        'project' => $project,
                        'department' => $department ?? (object)['name' => 'No Department'],
                        'status' => $project->status,
                        'status_color' => match($project->status) {
                            'completed' => 'success',
                            'active' => 'primary',
                            'on_hold' => 'warning',
                            'planning' => 'info',
                            default => 'secondary'
                        },
                        'created_at' => $project->created_at
                    ];
                })
        ];

        // Add role-specific data
        if ($user->role === 'super-admin') {
            $data['total_attendances'] = Attendance::count();
            $data['today_attendances'] = Attendance::whereDate('date', now())->count();
            $data['active_projects'] = Project::where('status', 'active')->count();
        } else if ($user->role === 'hr-admin') {
            $data['total_attendances'] = Attendance::count();
            $data['today_attendances'] = Attendance::whereDate('date', now())->count();
            $data['pending_attendances'] = Attendance::whereNull('clock_out')->count();
        } else if ($user->role === 'department-admin') {
            $data['department_users'] = User::where('department_id', $user->department_id)->count();
            $data['department_tasks'] = Task::where('department_id', $user->department_id)->count();
            $data['department_attendances'] = Attendance::whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })->count();
        } else {
            $data['my_attendances'] = Attendance::where('user_id', $user->id)->count();
            $data['my_today_attendance'] = Attendance::where('user_id', $user->id)
                ->whereDate('date', now())
                ->count();
            $data['my_tasks'] = Task::where('user_id', $user->id)->count();
            $data['my_active_tasks'] = Task::where('user_id', $user->id)
                ->where('status', 'active')
                ->count();
            $data['my_completed_tasks'] = Task::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
        }

        return view('dashboard', $data);
    }

    public function getStats()
    {
        $user = Auth::user();
        
        $stats = [
            'online_users' => User::where('last_activity_at', '>', now()->subMinutes(5))->count(),
            'active_tasks' => Task::where('status', 'active')->count(),
            'pending_approvals' => Task::where('status', 'pending_approval')->count(),
            'productivity_score' => $this->calculateProductivityScore($user),
        ];

        return response()->json($stats);
    }

    public function getActivities()
    {
        $activities = Project::with(['departments'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($project) {
                $department = $project->departments->first();
                return [
                    'id' => $project->id,
                    'type' => 'project',
                    'title' => $project->name,
                    'description' => 'Project status updated',
                    'department' => $department ? $department->name : 'No Department',
                    'status' => $project->status,
                    'status_color' => match($project->status) {
                        'completed' => 'success',
                        'active' => 'primary',
                        'on_hold' => 'warning',
                        'planning' => 'info',
                        default => 'secondary'
                    },
                    'created_at' => $project->created_at->format('M d, Y H:i'),
                    'time_ago' => $project->created_at->diffForHumans(),
                ];
            });

        return response()->json($activities);
    }

    private function calculateProductivityScore($user)
    {
        // Simple productivity calculation based on completed tasks
        $totalTasks = Task::where('user_id', $user->id)->count();
        $completedTasks = Task::where('user_id', $user->id)->where('status', 'completed')->count();
        
        if ($totalTasks == 0) return 0;
        
        return round(($completedTasks / $totalTasks) * 100);
    }
}
