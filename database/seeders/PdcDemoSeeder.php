<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\InstructorProfile;
use App\Models\Document;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PdcDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a Verified Instructor
        $instructorUser = User::firstOrCreate(
            ['email' => 'pdc_instructor@demo.com'],
            [
                'name' => 'Demo Instructor',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        
        if (!InstructorProfile::where('user_id', $instructorUser->id)->exists()) {
            InstructorProfile::factory()->create([
                'user_id' => $instructorUser->id,
            ]);
        }
        
        $instructorUser->assignRole('Instructor');

        // 2. Create a Verified Student
        $studentUser = User::firstOrCreate(
            ['email' => 'pdc_ready@demo.com'],
            [
                'name' => 'Demo Student PDC Ready',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $studentProfile = StudentProfile::where('user_id', $studentUser->id)->first();
        if (!$studentProfile) {
            $studentProfile = StudentProfile::factory()->create([
                'user_id' => $studentUser->id,
                'nationality' => 'filipino',
                'sex' => 'male',
                'civil_status' => 'single',
            ]);
        }
        
        $studentUser->assignRole('Student');

        // 3. Create all required documents for the student (Verified)
        $documentTypes = ['medical', 'adl_form', 'valid_id', 'birth_cert'];
        
        foreach ($documentTypes as $type) {
            Document::firstOrCreate(
                ['user_id' => $studentUser->id, 'type' => $type],
                [
                    'file_path' => 'demo/path/' . $type . '.pdf',
                    'status' => 'verified',
                    'metadata' => [],
                    'verified_by' => 1, // assuming user 1 is admin
                ]
            );
        }

        // 4. Create a TDC Course and complete it for the student
        $tdcCourse = Course::firstOrCreate(
            ['title' => 'Theoretical Driving Course (TDC)'],
            [
                'type' => 'theoretical',
                'duration_hours' => 15,
                'code' => 'TDC-DEMO',
                'price' => 1000,
                'description' => 'TDC Demo Course',
                'prerequisites' => [],
                'is_active' => true,
            ]
        );

        Enrollment::firstOrCreate(
            ['student_id' => $studentProfile->id, 'course_id' => $tdcCourse->id],
            [
                'instructor_id' => $instructorUser->instructorProfile->id ?? null,
                'code' => 'ENR-TDC-DEMO',
                'status' => 'completed',
                'tdc_status' => 'completed',
                'progress_percent' => 100,
                'tdc_hours_completed' => 15,
                'tdc_hours_required' => 15,
                'total_amount' => 1000,
                'amount_paid' => 1000,
                'balance' => 0,
            ]
        );

        // 5. Create Vehicles for PDC
        $vehicleTypes = ['Motorcycle', 'Tricycle', 'Automobile'];
        foreach ($vehicleTypes as $type) {
            \App\Models\Vehicle::firstOrCreate(
                ['model' => 'Demo ' . $type],
                [
                    'plate_number' => strtoupper(Str::random(3)) . '-' . rand(1000, 9999),
                    'transmission' => 'Manual',
                    'type' => $type,
                    'status' => 'available',
                ]
            );
        }

        $this->command->info('PDC Demo Seeder Completed successfully.');
        $this->command->info('Instructor: pdc_instructor@demo.com / password');
        $this->command->info('Student: pdc_ready@demo.com / password');
    }
}
