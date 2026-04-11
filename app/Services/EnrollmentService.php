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
            //Update the enrollment form status
            $form->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            //Find the best matching instructor
            $instructor = $this->findBestInstructor($form);

            // Load the course for pricing/duration info
            $course = $form->course;

            // Create the Enrollment record
            $enrollment = Enrollment::create([
                'enrollment_form_id' => $form->id,
                'code' => $this->generateEnrollmentCode(),
                'student_id' => $form->student_id,
                'course_id' => $form->course_id,
                'instructor_id' => $instructor?->id,
                'status' => $instructor !== null ? 'active' : 'waiting_list',
                'progress_percent' => 0,
                'start_date' => now()->toDateString(),
                'target_completion_date' => now()->addDays(2)->toDateString(),
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

        $query = InstructorProfile::where('is_active', true)
            ->where('status', 'approved');

        // Get all candidates and filter in PHP (JSON columns)
        $candidates = $query->get();

        //Filter by course type / skill matching
        $candidates = $candidates->filter(function (InstructorProfile $instructor) use ($form) {
            $skills = collect($instructor->skills ?? []);
            $courseType = $form->course->type ?? null;

            if ($courseType === 'theoretical') {
                // Must have theoretical driving skill (tdc key)
                return $skills->contains('tdc');
            }

            if ($courseType === 'practical') {
                // Must have any driving-related practical skill (manual or auto)
                return $skills->contains('manual')
                    || $skills->contains('auto');
            }

            return true;
        });

        //PDC-specific filters
        if ($form->package_type === 'PDC') {
            // Filter by transmission
            if ($form->transmission) {
                $requiredSkill = strtolower($form->transmission); // "Manual" -> "manual", "Auto" -> "auto"
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

        // Map full day names to the abbreviations used in weekly_schedule
        $dayMap = [
            'Monday' => 'mon', 'Tuesday' => 'tue', 'Wednesday' => 'wed',
            'Thursday' => 'thu', 'Friday' => 'fri', 'Saturday' => 'sat', 'Sunday' => 'sun',
        ];

        $schedulePrefs = $form->course_preferences['schedule_pref'] ?? [];
        if (!empty($schedulePrefs)) {
            $candidates = $candidates->filter(function (InstructorProfile $instructor) use ($schedulePrefs, $dayMap) {
                $schedule = $instructor->weekly_schedule ?? [];
                foreach ($schedulePrefs as $preferredDay) {
                    $key = $dayMap[$preferredDay] ?? strtolower(substr($preferredDay, 0, 3));
                    if (isset($schedule[$key]) && !empty($schedule[$key]['active'])) {
                        return true; // At least one preferred day is active
                    }
                }
                return false;
            });
        }

        //Workload balancing: sort by least active enrollments
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
            'Automobile' => ['Automobile'],
            'Motorcycle' => ['Motorcycle'],
            'Tricycle'   => ['Tricycle'],
            default      => [],
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
