<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\InstructorProfile;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run the Permissions Seeder FIRST
        // This sets up the Roles and your 3 Demo accounts
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Bulk Create 10 Random Students
        User::factory()
            ->count(10)
            ->has(StudentProfile::factory())
            ->create()
            ->each(function ($user) {
                $user->assignRole('Student');
            });

        // 3. Bulk Create 10 Random Instructors
        User::factory()
            ->count(10)
            ->has(InstructorProfile::factory())
            ->create()
            ->each(function ($user) {
                $user->assignRole('Instructor');
            });
            
        // 4. (Optional) Output specific info to console
        $this->command->info('Seeding Complete!');
        $this->command->info('Admin: admin@blrt.com | password');
        $this->command->info('Instructor: instructor@blrt.com | password');
        $this->command->info('Student: student@blrt.com | password');
    }
}