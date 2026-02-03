<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Define Permissions Grouped by Domain
        $permissions = [
            // User Management
            'user.view', 'user.create', 'user.update', 'user.delete',
            
            // Student Profiles
            'student.view_any', 'student.view_own', 'student.update_own',
            
            // Instructor Management
            'instructor.view_any', 'instructor.view_own', 'instructor.manage_performance',

            // Assets (Vehicles)
            'vehicle.view', 'vehicle.create', 'vehicle.update', 'vehicle.maintenance',

            // Academic (Courses)
            'course.view', 'course.create', 'course.update', 'course.publish',

            // Enrollment & Forms
            'enrollment.submit',     // For students submitting forms
            'enrollment.review',     // For admins reviewing/rejecting
            'enrollment.view_own',   // For students
            'enrollment.view_any',   // For admins
            'enrollment.view_assigned', // For instructors

            // Operations (Booking/Sessions)
            'booking.create',
            'booking.view_own',
            'booking.view_any',
            'booking.cancel',
            'booking.complete',      // Mark session as done

            // Assessments & Grading
            'assessment.create',     // Instructor grading
            'assessment.view_own',   // Student viewing grade
            'assessment.finalize',   // Admin noting/approving

            // Documents & Compliance
            'document.upload',
            'document.verify',       // LTO Clinic/Admin verification
            'document.view_own',

            // Analytics
            'report.view_financial',
            'report.view_performance',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 3. Create Roles and Assign Permissions

        // --- ROLE: STUDENT ---
        $studentRole = Role::firstOrCreate(['name' => 'Student']);
        $studentRole->givePermissionTo([
            'student.view_own',
            'student.update_own',
            'course.view',
            'enrollment.submit',
            'enrollment.view_own',
            'booking.view_own',
            'assessment.view_own',
            'document.upload',
            'document.view_own',
        ]);

        // --- ROLE: INSTRUCTOR ---
        $instructorRole = Role::firstOrCreate(['name' => 'Instructor']);
        $instructorRole->givePermissionTo([
            'instructor.view_own',
            'student.view_any',         // View student list
            'enrollment.view_assigned', // Only their students
            'vehicle.view',             // Check vehicle availability
            'booking.view_any',         // See schedule
            'booking.complete',         // Mark sessions done
            'assessment.create',        // Grade students
            'document.view_own',
        ]);

        // --- ROLE: ADMIN ---
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        // Admins get everything except specific student "own" restrictions if implied logic differs
        // Ideally, simply give all permissions or use a Super Admin gate.
        // Here we assign specific administrative rights:
        $adminRole->givePermissionTo([
            'user.view', 'user.create', 'user.update', 'user.delete',
            'student.view_any',
            'instructor.view_any', 'instructor.manage_performance',
            'vehicle.view', 'vehicle.create', 'vehicle.update', 'vehicle.maintenance',
            'course.view', 'course.create', 'course.update', 'course.publish',
            'enrollment.review', 'enrollment.view_any',
            'booking.create', 'booking.view_any', 'booking.cancel',
            'assessment.finalize',
            'document.verify',
            'report.view_financial', 'report.view_performance',
        ]);

        $this->createDemoUsers();
    }

    private function createDemoUsers(): void
    {
        // Admin
        $admin = \App\Models\User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@blrt.com',
            'password' => 'password', 
        ]);
        $admin->assignRole('Admin');

        // Instructor
        $instructor = \App\Models\User::factory()->create([
            'name' => 'John Instructor',
            'email' => 'instructor@blrt.com',
            'password' => 'password',
        ]);
        $instructor->assignRole('Instructor');

        // Student
        $student = \App\Models\User::factory()->create([
            'name' => 'Jane Student',
            'email' => 'student@blrt.com',
            'password' => 'password',
        ]);
        $student->assignRole('Student');
    }
}