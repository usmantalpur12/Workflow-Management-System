<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // Get all users
        $users = User::all();

        // Create attendance records for today
        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => Carbon::today()->toDateString(),
                'clock_in' => '09:00:00',
                'clock_out' => '17:00:00',
                'status' => 'present',
                'notes' => null,
            ]);
        }
    }
}
