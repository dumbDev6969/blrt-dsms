<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Assessment;
use App\Models\BookingSession;
use App\Services\InstructorMetricService;
use Illuminate\Support\Facades\DB;

class InstructorGradingService
{
    /**
     * Submit an official grade for a student enrollment.
     *
     * @param int $enrollmentId
     * @param array $data ['grade' => float, 'result' => string, 'remarks' => string]
     * @return void
     */
    public function submitGrade(int $enrollmentId, array $data)
    {
        $now = now();
        $enrollment = Enrollment::with('course')->findOrFail($enrollmentId);

        DB::transaction(function () use ($enrollment, $enrollmentId, $data, $now) {
            $oldResult = $enrollment->final_result;

            // 1. Update Enrollment with final grade results
            $enrollment->update([
                'final_grade'  => $data['grade'] ?? null,
                'final_result' => $data['result'],
                'remarks'      => $data['remarks'] ?? null,
                'status'       => 'completed',
            ]);

            // 2. Ensure Assessment record exists for TDC (Theoretical)
            if ($enrollment->course->type === 'theoretical') {
                Assessment::updateOrCreate(
                    ['enrollment_id' => $enrollmentId, 'assessment_type' => 'theoretical'],
                    [
                        'student_id'        => $enrollment->student_id,
                        'instructor_id'     => $enrollment->instructor_id,
                        'assessment_date'   => $now->format('Y-m-d'),
                        'is_passed'         => $data['result'] === 'pass',
                        'failure_reason'    => $data['result'] === 'fail' ? ($data['remarks'] ?? null) : null,
                        'instructor_remarks' => $data['remarks'] ?? null,
                    ]
                );
            }

            // 3. Mark any active TDC sessions as completed
            BookingSession::where('enrollment_id', $enrollmentId)
                ->where('status', 'scheduled')
                ->whereHas('enrollment.course', function($q) { 
                    $q->where('type', 'theoretical'); 
                })
                ->update(['status' => 'completed', 'end_time' => $now]);

            // 4. Update Instructor Metrics if results changed or first record
            if ($oldResult !== $data['result']) {
                app(InstructorMetricService::class)->recordCourseCompletion(
                    $enrollment->instructor_id,
                    $data['result'] === 'pass'
                );
            }
        });
    }
}
