<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        // Clear existing departments
        DB::table('departments')->delete();

        // Create departments
        $departments = [
            'General',
            'IT',
            'HR',
            'Finance',
            'Operations',
            'Marketing',
            'Research & Development',
        ];

        foreach ($departments as $departmentName) {
            Department::create([
                'name' => $departmentName,
            ]);
        }
    }
}
