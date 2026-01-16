<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\Chat;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AnalyzeProject extends Command
{
    protected $signature = 'analyze:project';
    protected $description = 'Analyze project and fill with comprehensive data';

    public function handle()
    {
        $this->info('🔍 Analyzing Workflow Management System...');
        $this->line('');

        // 1. Analyze Current Data
        $this->analyzeCurrentData();

        // 2. Fill Missing Data
        $this->fillMissingData();

        // 3. Create Sample Data
        $this->createSampleData();

        // 4. Final Analysis
        $this->finalAnalysis();

        $this->line('');
        $this->info('✅ Project analysis and data filling completed!');
    }

    private function analyzeCurrentData()
    {
        $this->info('📊 Current Data Analysis:');
        $this->line('');

        $users = User::count();
        $departments = Department::count();
        $projects = Project::count();
        $tasks = Task::count();
        $chats = Chat::count();
        $attendances = Attendance::count();

        $this->line("👥 Users: {$users}");
        $this->line("🏢 Departments: {$departments}");
        $this->line("📋 Projects: {$projects}");
        $this->line("✅ Tasks: {$tasks}");
        $this->line("💬 Chats: {$chats}");
        $this->line("⏰ Attendances: {$attendances}");

        // Role distribution
        $this->line('');
        $this->info('👤 Role Distribution:');
        $roles = User::select('role', DB::raw('count(*) as count'))->groupBy('role')->get();
        foreach ($roles as $role) {
            $this->line("- {$role->role}: {$role->count}");
        }

        // Department analysis
        $this->line('');
        $this->info('🏢 Department Analysis:');
        $deptStats = Department::withCount(['users', 'projects'])->get();
        foreach ($deptStats as $dept) {
            $this->line("- {$dept->name}: {$dept->users_count} users, {$dept->projects_count} projects");
        }

        $this->line('');
    }

    private function fillMissingData()
    {
        $this->info('🔧 Filling Missing Data...');
        $this->line('');

        // Create missing departments
        $departments = [
            ['name' => 'Human Resources', 'hr_admin_id' => null],
            ['name' => 'Information Technology', 'hr_admin_id' => null],
            ['name' => 'Finance & Accounting', 'hr_admin_id' => null],
            ['name' => 'Marketing & Sales', 'hr_admin_id' => null],
            ['name' => 'Operations', 'hr_admin_id' => null],
            ['name' => 'Research & Development', 'hr_admin_id' => null],
            ['name' => 'Customer Support', 'hr_admin_id' => null],
            ['name' => 'Quality Assurance', 'hr_admin_id' => null],
        ];

        foreach ($departments as $deptData) {
            $existing = Department::where('name', $deptData['name'])->first();
            if (!$existing) {
                Department::create($deptData);
                $this->line("✅ Created department: {$deptData['name']}");
            }
        }

        // Create HR admins for each department
        $departments = Department::whereNull('hr_admin_id')->get();
        foreach ($departments as $department) {
            $hrAdmin = User::where('role', 'hr-admin')
                ->whereNull('department_id')
                ->first();

            if ($hrAdmin) {
                $hrAdmin->department_id = $department->id;
                $hrAdmin->save();
                $department->hr_admin_id = $hrAdmin->id;
                $department->save();
                $this->line("✅ Assigned HR admin to {$department->name}");
            }
        }

        $this->line('');
    }

    private function createSampleData()
    {
        $this->info('📝 Creating Sample Data...');
        $this->line('');

        // Create sample users for each department
        $departments = Department::all();
        $roles = ['employee', 'team-lead', 'manager'];

        foreach ($departments as $department) {
            // Create 3-5 employees per department
            $employeeCount = rand(3, 5);
            for ($i = 1; $i <= $employeeCount; $i++) {
                $existingUser = User::where('email', "{$department->name}_employee_{$i}@company.com")->first();
                if (!$existingUser) {
                    $user = User::create([
                        'name' => "{$department->name} Employee {$i}",
                        'email' => "{$department->name}_employee_{$i}@company.com",
                        'password' => Hash::make('password'),
                        'role' => $roles[array_rand($roles)],
                        'department_id' => $department->id,
                        'ip_address' => '127.0.0.1',
                    ]);
                    $this->line("✅ Created user: {$user->name}");
                }
            }
        }

        // Create sample projects
        $projectTemplates = [
            ['name' => 'Website Redesign', 'description' => 'Complete redesign of company website with modern UI/UX', 'status' => 'in_progress'],
            ['name' => 'Mobile App Development', 'description' => 'Development of mobile application for customer engagement', 'status' => 'planning'],
            ['name' => 'Database Migration', 'description' => 'Migration from legacy database to modern cloud solution', 'status' => 'completed'],
            ['name' => 'Security Audit', 'description' => 'Comprehensive security audit and vulnerability assessment', 'status' => 'in_progress'],
            ['name' => 'Employee Training Program', 'description' => 'New employee onboarding and training program', 'status' => 'planning'],
            ['name' => 'Marketing Campaign', 'description' => 'Q4 marketing campaign for product launch', 'status' => 'in_progress'],
            ['name' => 'Process Automation', 'description' => 'Automation of manual business processes', 'status' => 'completed'],
            ['name' => 'Customer Feedback System', 'description' => 'Implementation of customer feedback collection system', 'status' => 'planning'],
        ];

        foreach ($projectTemplates as $template) {
            $existingProject = Project::where('name', $template['name'])->first();
            if (!$existingProject) {
                $department = Department::inRandomOrder()->first();
                $project = Project::create([
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'status' => $template['status'],
                    'start_date' => now()->subDays(rand(1, 30)),
                    'end_date' => now()->addDays(rand(30, 90)),
                    'created_by' => $department->hr_admin_id ?? User::where('role', 'super-admin')->first()->id,
                ]);

                // Assign project to department
                $project->departments()->attach($department->id, [
                    'created_by' => $project->created_by,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->line("✅ Created project: {$project->name}");
            }
        }

        // Create sample tasks
        $projects = Project::all();
        foreach ($projects as $project) {
            $taskCount = rand(3, 8);
            $projectDepartment = $project->departments->first();
            if ($projectDepartment) {
                for ($i = 1; $i <= $taskCount; $i++) {
                    $existingTask = Task::where('title', "{$project->name} - Task {$i}")->first();
                    if (!$existingTask) {
                        $assignedUser = User::where('department_id', $projectDepartment->id)->inRandomOrder()->first();
                        $task = Task::create([
                            'title' => "{$project->name} - Task {$i}",
                            'description' => "Task {$i} for {$project->name} project",
                            'status' => ['pending', 'in_progress', 'completed'][array_rand(['pending', 'in_progress', 'completed'])],
                            'priority' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                            'project_id' => $project->id,
                            'department_id' => $projectDepartment->id,
                            'due_date' => now()->addDays(rand(1, 30)),
                        ]);
                        
                        // Assign task to user if available
                        if ($assignedUser) {
                            $task->assignedUsers()->attach($assignedUser->id, [
                                'assigned_by' => $project->created_by,
                                'notes' => 'Auto-assigned during data generation',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        
                        $this->line("✅ Created task: {$task->title}");
                    }
                }
            }
        }

        // Create sample chats
        $users = User::all();
        $chatCount = 0;
        foreach ($users as $sender) {
            $recipients = $users->where('id', '!=', $sender->id)->random(rand(2, 4));
            foreach ($recipients as $recipient) {
                $messageCount = rand(5, 15);
                for ($i = 0; $i < $messageCount; $i++) {
                    $existingChat = Chat::where('sender_id', $sender->id)
                        ->where('recipient_id', $recipient->id)
                        ->where('message', "Sample message {$i} from {$sender->name}")
                        ->first();
                    
                    if (!$existingChat) {
                        Chat::create([
                            'sender_id' => $sender->id,
                            'recipient_id' => $recipient->id,
                            'message' => "Sample message {$i} from {$sender->name}",
                            'message_type' => 'text',
                            'read' => rand(0, 1),
                            'created_at' => now()->subDays(rand(1, 30)),
                        ]);
                        $chatCount++;
                    }
                }
            }
        }
        $this->line("✅ Created {$chatCount} sample chat messages");

        // Create sample attendances
        $users = User::where('role', '!=', 'super-admin')->get();
        $attendanceCount = 0;
        foreach ($users as $user) {
            $days = rand(15, 25); // Last 15-25 days
            for ($i = 0; $i < $days; $i++) {
                $date = now()->subDays($i);
                $existingAttendance = Attendance::where('user_id', $user->id)
                    ->whereDate('created_at', $date->toDateString())
                    ->first();
                
                if (!$existingAttendance) {
                    $clockIn = $date->copy()->setHour(rand(8, 10))->setMinute(rand(0, 59));
                    $clockOut = $clockIn->copy()->addHours(rand(7, 9))->addMinutes(rand(0, 59));
                    
                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => 'present',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    $attendanceCount++;
                }
            }
        }
        $this->line("✅ Created {$attendanceCount} sample attendance records");

        $this->line('');
    }

    private function finalAnalysis()
    {
        $this->info('📈 Final Analysis:');
        $this->line('');

        $users = User::count();
        $departments = Department::count();
        $projects = Project::count();
        $tasks = Task::count();
        $chats = Chat::count();
        $attendances = Attendance::count();

        $this->line("👥 Total Users: {$users}");
        $this->line("🏢 Total Departments: {$departments}");
        $this->line("📋 Total Projects: {$projects}");
        $this->line("✅ Total Tasks: {$tasks}");
        $this->line("💬 Total Chats: {$chats}");
        $this->line("⏰ Total Attendances: {$attendances}");

        // System health check
        $this->line('');
        $this->info('🔍 System Health Check:');
        
        // Check for HR admins in each department
        $deptWithoutHr = Department::whereNull('hr_admin_id')->count();
        if ($deptWithoutHr > 0) {
            $this->warn("⚠️  {$deptWithoutHr} departments without HR admins");
        } else {
            $this->line("✅ All departments have HR admins");
        }

        // Check for projects without departments
        $projectsWithoutDept = Project::whereDoesntHave('departments')->count();
        if ($projectsWithoutDept > 0) {
            $this->warn("⚠️  {$projectsWithoutDept} projects without departments");
        } else {
            $this->line("✅ All projects are assigned to departments");
        }

        // Check for users without departments
        $usersWithoutDept = User::whereNull('department_id')->where('role', '!=', 'super-admin')->count();
        if ($usersWithoutDept > 0) {
            $this->warn("⚠️  {$usersWithoutDept} users without departments");
        } else {
            $this->line("✅ All users are assigned to departments");
        }

        // Check for tasks without assignees
        $tasksWithoutAssignee = Task::whereNull('assigned_to')->count();
        if ($tasksWithoutAssignee > 0) {
            $this->warn("⚠️  {$tasksWithoutAssignee} tasks without assignees");
        } else {
            $this->line("✅ All tasks have assignees");
        }

        $this->line('');
        $this->info('🎯 System is ready for comprehensive testing!');
    }
}