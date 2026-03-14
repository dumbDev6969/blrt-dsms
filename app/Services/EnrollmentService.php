<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentForm;
use App\Models\InstructorProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnrollmentService
{
    /**
     * Approve an enrollment form and create the corresponding Enrollment record.
     *
     * @param  EnrollmentForm  $form
     * @return array{enrollment: Enrollment, instructor_assigned: bool}
     *
     * @throws \Exception
     */
    public function approve(EnrollmentForm $form): array
    {
        if ($form->status === 'approved') {
            throw new \Exception('This enrollment form has already been approved.');
        }

        return DB::transaction(function () use ($form) {
            // 1. Update the enrollment form status
            $form->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // 2. Find the best matching instructor
            $instructor = $this->findBestInstructor($form);

            // 3. Load the course for pricing/duration info
            $course = $form->course;

            // 4. Create the Enrollment record
            $enrollment = Enrollment::create([
                'enrollment_form_id' => $form->id,
                'code' => $this->generateEnrollmentCode(),
                'student_id' => $form->student_id,
                'course_id' => $form->course_id,
                'instructor_id' => $instructor?->id,
                'status' => 'active',
                'progress_percent' => 0,
                'start_date' => now()->toDateString(),
                'total_amount' => $course->price ?? 0,
                'amount_paid' => 0,
                'balance' => $course->price ?? 0,
                'tdc_hours_required' => $course->type === 'theoretical' ? $course->duration_hours : 0,
                'pdc_hours_required' => $course->type === 'practical' ? $course->duration_hours : 0,
                'tdc_status' => $course->type === 'theoretical' ? 'not_started' : 'not_started',
                'pdc_status' => $course->type === 'practical' ? 'not_started' : 'not_started',
            ]);

            return [
                'enrollment' => $enrollment,
                'instructor_assigned' => $instructor !== null,
            ];
        });
    }

    
    public function findBestInstructor(EnrollmentForm $form): ?InstructorProfile
    {
        $form->loadMissing('course');

        // Start: only active, verified instructors
        $query = InstructorProfile::where('is_active', true)
            ->where('status', 'verified');

        // Get all candidates and filter in PHP (JSON columns)
        $candidates = $query->get();

        // --- Filter by course type / skill matching ---
        $candidates = $candidates->filter(function (InstructorProfile $instructor) use ($form) {
            $skills = collect($instructor->skills ?? []);
            $courseType = $form->course->type ?? null;

            if ($courseType === 'theoretical') {
                // Must have theoretical driving skill
                return $skills->contains('Theoretical Driving');
            }

            if ($courseType === 'practical') {
                // Must have any driving-related practical skill
                return $skills->contains('Manual Transmission')
                    || $skills->contains('Automatic Transmission')
                    || $skills->contains('Defensive Driving');
            }

            return true;
        });

        // --- PDC-specific filters ---
        if ($form->package_type === 'PDC') {
            // Filter by transmission
            if ($form->transmission) {
                $requiredSkill = $form->transmission . ' Transmission'; // e.g. "Manual Transmission"
                $candidates = $candidates->filter(function (InstructorProfile $instructor) use ($requiredSkill) {
                    return collect($instructor->skills ?? [])->contains($requiredSkill);
                });
            }

            // Filter by vehicle category
            if ($form->vehicle_category) {
                $matchingTypes = $this->mapVehicleCategoryToTypes($form->vehicle_category);
                $candidates = $candidates->filter(function (InstructorProfile $instructor) use ($matchingTypes) {
                    $instructorTypes = collect($instructor->vehicle_types ?? []);
                    return $instructorTypes->intersect($matchingTypes)->isNotEmpty();
                });
            }
        }

        // --- Schedule availability ---
        $schedulePrefs = $form->course_preferences['schedule_pref'] ?? [];
        if (!empty($schedulePrefs)) {
            $candidates = $candidates->filter(function (InstructorProfile $instructor) use ($schedulePrefs) {
                $schedule = $instructor->weekly_schedule ?? [];
                foreach ($schedulePrefs as $preferredDay) {
                    if (isset($schedule[$preferredDay]) && !empty($schedule[$preferredDay])) {
                        return true; // At least one preferred day is available
                    }
                }
                return false;
            });
        }

        // --- Workload balancing: sort by least active enrollments ---
        $candidates = $candidates->sortBy(function (InstructorProfile $instructor) {
            return $instructor->enrollments()
                ->whereIn('status', ['pending', 'active'])
                ->count();
        });

        return $candidates->first();
    }

    /**
     * Map the enrollment form's vehicle_category to instructor vehicle_types.
     *
     * @param  string  $category
     * @return array
     */
    protected function mapVehicleCategoryToTypes(string $category): array
    {
        return match ($category) {
            '4-Wheel' => ['Sedan', 'SUV', 'Hatchback', 'Light Truck'],
            'Motorcycle' => ['Motorcycle'],
            'Tricycle' => ['Motorcycle'], // Closest available mapping
            default => [],
        };
    }

    /**
     * Generate a unique enrollment code.
     *
     * @return string
     */
    protected function generateEnrollmentCode(): string
    {
        do {
            $code = 'ENR-' . Str::upper(Str::random(8));
        } while (Enrollment::where('code', $code)->exists());

        return $code;
    }
}
