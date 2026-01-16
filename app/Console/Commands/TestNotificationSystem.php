<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Project;
use App\Models\Notification;
use App\Notifications\ProjectAssignedNotification;
use Illuminate\Support\Facades\Mail;

class TestNotificationSystem extends Command
{
    protected $signature = 'test:notifications';
    protected $description = 'Test the notification and email system';

    public function handle()
    {
        $this->info('Testing Notification and Email System...');
        
        // Test 1: Check if users exist
        $users = User::all();
        $this->info("Found {$users->count()} users in the system");
        
        if ($users->count() == 0) {
            $this->error('No users found. Please create users first.');
            return;
        }
        
        // Test 2: Check if projects exist
        $projects = Project::all();
        $this->info("Found {$projects->count()} projects in the system");
        
        if ($projects->count() == 0) {
            $this->error('No projects found. Please create projects first.');
            return;
        }
        
        // Test 3: Test database notifications
        $this->info('Testing database notifications...');
        $user = $users->first();
        $project = $projects->first();
        
        try {
            // Create a test notification using Laravel's notification system
            $user->notify(new \App\Notifications\TestNotification());
            
            $this->info("✅ Database notification created successfully");
            
            // Test 4: Test email notification
            $this->info('Testing email notifications...');
            
            try {
                $user->notify(new ProjectAssignedNotification($project));
                $this->info('✅ Email notification sent successfully');
            } catch (\Exception $e) {
                $this->error("❌ Email notification failed: " . $e->getMessage());
                $this->info('Note: Email might be configured to log instead of send');
            }
            
            // Test 5: Check notification count
            $notificationCount = Notification::where('user_id', $user->id)->count();
            $this->info("Total notifications for user {$user->name}: {$notificationCount}");
            
            // Test 6: Test attendance notifications endpoint
            $this->info('Testing attendance notifications endpoint...');
            $attendanceNotifications = \App\Models\Attendance::with('user')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->orWhere('updated_at', '>=', now()->subMinutes(5))
                ->get()
                ->map(function ($attendance) {
                    $action = $attendance->clock_out ? 'clocked out' : 'clocked in';
                    return [
                        'user' => $attendance->user->name,
                        'action' => $action,
                        'time' => $attendance->updated_at->format('H:i:s'),
                        'date' => $attendance->updated_at->format('Y-m-d')
                    ];
                });
            
            $this->info("Found {$attendanceNotifications->count()} recent attendance activities");
            
            $this->info('✅ Notification system test completed successfully!');
            
        } catch (\Exception $e) {
            $this->error("❌ Notification system test failed: " . $e->getMessage());
        }
    }
}