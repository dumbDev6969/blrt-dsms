<?php

namespace App\Services;

use App\Models\InstructorMetric;
use Carbon\Carbon;

class InstructorMetricService
{
    /**
     * Get or create a metric record for the instructor for the current month.
     */
    private function getMetric(int $instructorId): InstructorMetric
    {
        return InstructorMetric::firstOrCreate(
            [
                'instructor_id' => $instructorId,
                'metric_month' => now()->startOfMonth()->format('Y-m-d')
            ],
            [
                'total_sessions' => 0,
                'completed_sessions' => 0,
                'total_hours' => 0,
                'avg_rating' => 0,
                'students_taught' => 0,
                'students_passed' => 0,
                'pass_rate' => 0
            ]
        );
    }

    /**
     * Record the completion of a single session.
     */
    public function recordSessionCompletion(int $instructorId, float $durationHours): void
    {
        $metric = $this->getMetric($instructorId);

        $metric->increment('total_sessions');
        $metric->increment('completed_sessions');
        $metric->increment('total_hours', $durationHours);
    }

    /**
     * Record the final course completion for a student.
     */
    public function recordCourseCompletion(int $instructorId, bool $isPassed): void
    {
        $metric = $this->getMetric($instructorId);

        $metric->increment('students_taught');
        
        if ($isPassed) {
            $metric->increment('students_passed');
        }

        // Recalculate pass rate
        $metric->update([
            'pass_rate' => $metric->students_taught > 0 
                ? ($metric->students_passed / $metric->students_taught) * 100 
                : 0
        ]);
    }
}
