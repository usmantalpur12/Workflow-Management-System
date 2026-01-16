<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatSeeder extends Seeder
{
    public function run()
    {
        // Get all users
        $users = User::all();

        // Create sample chats between users
        // Group users by department
        $usersByDepartment = $users->groupBy('department_id');

        // Create messages between users in the same department
        foreach ($usersByDepartment as $departmentUsers) {
            foreach ($departmentUsers as $sender) {
                foreach ($departmentUsers as $recipient) {
                    // Skip if sender and recipient are the same
                    if ($sender->id === $recipient->id) {
                        continue;
                    }

                    // Create a chat message
                    Chat::create([
                        'sender_id' => $sender->id,
                        'recipient_id' => $recipient->id,
                        'message' => "Hi {$recipient->name}, this is a sample message from {$sender->name}",
                        'read' => false,
                    ]);
                }
            }
        }


    }
}
