<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\InstructorProfile;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create 10 Users who have a Student Profile
        User::factory()
            ->count(10)
            ->has(StudentProfile::factory()) 
            ->create();

        // 2. Create 10 Users who have an Instructor Profile
        User::factory()
            ->count(10)
            ->has(InstructorProfile::factory())
            ->create();
    }
}
