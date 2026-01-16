<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Run the department seeder
        $this->call(DepartmentSeeder::class);
        
        // Run the attendance seeder
        $this->call(AttendanceSeeder::class);

        // Run the chat seeder
        $this->call(ChatSeeder::class);

        // Create roles if they don't exist
        $roles = ['super-admin', 'hr-admin', 'department-admin', 'teacher', 'student', 'employee'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create a department if it doesn't exist
        $department = Department::firstOrCreate([
            'name' => 'General'
        ], [
            'description' => 'General Department',
        ]);

        // Create users if they don't exist
        $users = [
            [
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
                'password' => 'password',
            'role' => 'super-admin',
            'ip_address' => '127.0.0.1',
            'department' => 'General',
            ],
            [
                'name' => 'HR Admin',
                'email' => 'hradmin@example.com',
                'password' => 'password',
                'role' => 'hr-admin',
                'department' => 'General',
            ],
            [
                'name' => 'Department Admin',
                'email' => 'deptadmin@example.com',
                'password' => 'password',
                'role' => 'department-admin',
                'department' => 'Teacher Dept',
            ],
            [
                'name' => 'Teacher One',
                'email' => 'teacher@example.com',
                'password' => 'password',
                'role' => 'teacher',
                'department' => 'Teacher Dept',
            ],
            [
                'name' => 'Student One',
                'email' => 'student@example.com',
                'password' => 'password',
                'role' => 'student',
                'department' => 'Teacher Dept',
            ],
            [
                'name' => 'Employee One',
                'email' => 'employee@example.com',
                'password' => 'password',
                'role' => 'employee',
                'department' => 'General',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate([
                'email' => $userData['email']
            ], [
                'name' => $userData['name'],
                'password' => bcrypt($userData['password']),
                'role' => $userData['role'],
                'ip_address' => $userData['ip_address'] ?? null,
            ]);

            // Assign department
            $department = Department::where('name', $userData['department'])->first();
            if ($department) {
                $user->department_id = $department->id;
                $user->save();
            }

            // Assign role if not already assigned
            if (!$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }
        }

        // Create Teacher Department if it doesn't exist
        $teacherDept = Department::firstOrCreate([
            'name' => 'Teacher Dept'
        ], [
            'description' => 'Teacher Department',
        ]);

        // Create sample projects
        $project1 = \App\Models\Project::create([
            'name' => 'Project One',
            'description' => 'First sample project',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'status' => 'planning',
            'priority' => 'medium',
            'created_by' => User::where('email', 'superadmin@example.com')->first()->id
        ]);

        $project2 = \App\Models\Project::create([
            'name' => 'Project Two',
            'description' => 'Second sample project',
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'status' => 'planning',
            'priority' => 'high',
            'created_by' => User::where('email', 'superadmin@example.com')->first()->id
        ]);

        // Assign departments to projects
        \App\Models\ProjectDepartment::create([
            'project_id' => $project1->id,
            'department_id' => $teacherDept->id,
            'created_by' => User::where('email', 'superadmin@example.com')->first()->id
        ]);

        \App\Models\ProjectDepartment::create([
            'project_id' => $project2->id,
            'department_id' => $teacherDept->id,
            'created_by' => User::where('email', 'superadmin@example.com')->first()->id
        ]);

        // Create sample tasks for projects
        $task1 = \App\Models\Task::create([
            'project_id' => $project1->id,
            'department_id' => $teacherDept->id,
            'title' => 'Task One',
            'description' => 'First task of Project One',
            'due_date' => now()->addDays(7),
            'status' => 'pending',
            'priority' => 'medium'
        ]);

        $task2 = \App\Models\Task::create([
            'project_id' => $project1->id,
            'department_id' => $teacherDept->id,
            'title' => 'Task Two',
            'description' => 'Second task of Project One',
            'due_date' => now()->addDays(14),
            'status' => 'pending',
            'priority' => 'high'
        ]);

        // Create more sample tasks for Project Two
        $task3 = \App\Models\Task::create([
            'project_id' => $project2->id,
            'department_id' => $teacherDept->id,
            'title' => 'Task Three',
            'description' => 'First task of Project Two',
            'due_date' => now()->addDays(10),
            'status' => 'pending',
            'priority' => 'medium'
        ]);

        $task4 = \App\Models\Task::create([
            'project_id' => $project2->id,
            'department_id' => $teacherDept->id,
            'title' => 'Task Four',
            'description' => 'Second task of Project Two',
            'due_date' => now()->addDays(15),
            'status' => 'pending',
            'priority' => 'high'
        ]);

        // Assign tasks to users
        \App\Models\TaskAssignment::create([
            'task_id' => $task1->id,
            'user_id' => User::where('email', 'teacher@example.com')->first()->id,
            'assigned_by' => User::where('email', 'superadmin@example.com')->first()->id,
            'notes' => 'Assigned to Teacher One'
        ]);

        \App\Models\TaskAssignment::create([
            'task_id' => $task2->id,
            'user_id' => User::where('email', 'teacher@example.com')->first()->id,
            'assigned_by' => User::where('email', 'superadmin@example.com')->first()->id,
            'notes' => 'Assigned to Teacher One'
        ]);

        \App\Models\TaskAssignment::create([
            'task_id' => $task3->id,
            'user_id' => User::where('email', 'teacher@example.com')->first()->id,
            'assigned_by' => User::where('email', 'superadmin@example.com')->first()->id,
            'notes' => 'Assigned to Teacher One'
        ]);

        \App\Models\TaskAssignment::create([
            'task_id' => $task4->id,
            'user_id' => User::where('email', 'teacher@example.com')->first()->id,
            'assigned_by' => User::where('email', 'superadmin@example.com')->first()->id,
            'notes' => 'Assigned to Teacher One'
        ]);

        // Create notifications for task assignments
        \App\Models\Notification::create([
            'user_id' => User::where('email', 'teacher@example.com')->first()->id,
            'type' => 'task_assigned',
            'message' => 'You have been assigned Task One',
            'data' => json_encode(['task_id' => $task1->id, 'task_title' => $task1->title]),
            'read' => false
        ]);

        \App\Models\Notification::create([
            'user_id' => User::where('email', 'teacher@example.com')->first()->id,
            'type' => 'task_assigned',
            'message' => 'You have been assigned Task Two',
            'data' => json_encode(['task_id' => $task2->id, 'task_title' => $task2->title]),
            'read' => false
        ]);


    }
}
