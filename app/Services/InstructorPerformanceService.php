<?php

namespace App\Services;

use App\Models\InstructorPerformance;
use Illuminate\Support\Collection;

class InstructorPerformanceService
{
    /**
     * Get aggregate summary statistics for an instructor (optionally for a specific course).
     *
     * @param int $instructorId
     * @param int|null $courseId
     * @return object|null
     */
    public function getSummary(int $instructorId, ?int $courseId = null): ?object
    {
        $query = InstructorPerformance::query()
            ->where('instructor_id', $instructorId)
            ->when($courseId, function ($q) use ($courseId) {
                return $q->whereHas('enrollment', function ($sq) use ($courseId) {
                    $sq->where('course_id', $courseId);
                });
            });

        // Fetch only necessary columns for stats calculation
        $data = $query->get(['rating', 'performance_criteria', 'areas_of_strength', 'areas_for_improvement', 'evaluation_date']);

        if ($data->isEmpty()) {
            return null;
        }

        return $this->formatPerformanceData($data);
    }

    /**
     * Get paginated reviews with full relations for an instructor (optionally for a specific course).
     *
     * @param int $instructorId
     * @param int|null $courseId
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getReviews(int $instructorId, ?int $courseId = null, int $perPage = 5)
    {
        return InstructorPerformance::query()
            ->where('instructor_id', $instructorId)
            ->when($courseId, function ($q) use ($courseId) {
                return $q->whereHas('enrollment', function ($sq) use ($courseId) {
                    $sq->where('course_id', $courseId);
                });
            })
            ->with(['studentProfile.user', 'enrollment.course', 'bookingSession'])
            ->latest('evaluation_date')
            ->paginate($perPage);
    }

    /**
     * Format a collection of performances into a standardized analytical object.
     *
     * @param Collection $group
     * @return object
     */
    private function formatPerformanceData(Collection $group): object
    {
        // Calculate average criteria
        $criteriaKeys = ['teaching_quality', 'communication', 'punctuality', 'professionalism'];
        $avgCriteria = [];
        foreach ($criteriaKeys as $key) {
            $avgCriteria[$key] = round($group->avg(fn($p) => is_array($p->performance_criteria) ? ($p->performance_criteria[$key] ?? 0) : (json_decode($p->performance_criteria, true)[$key] ?? 0)), 1);
        }

        $totalReviews = $group->count();
        $avgRating = round($group->avg('rating'), 1);

        // Trend (Compare latest 5 reviews to the rest, if enough data exists)
        $trend = 0;
        if ($totalReviews > 5) {
            $recentAvg = $group->take(5)->avg('rating');
            $historicalAvg = $group->skip(5)->avg('rating');
            $trend = round($recentAvg - $historicalAvg, 1);
        }

        // Rating Distribution (1-5 stars)
        $distribution = [
            5 => $group->where('rating', 5)->count(),
            4 => $group->whereBetween('rating', [4, 4.9])->count(),
            3 => $group->whereBetween('rating', [3, 3.9])->count(),
            2 => $group->whereBetween('rating', [2, 2.9])->count(),
            1 => $group->whereBetween('rating', [1, 1.9])->count(),
        ];

        // Feedback Themes (Extract distinct non-empty phrases)
        $extractThemes = function ($field) use ($group) {
            return $group->pluck($field)
                ->filter()
                ->map(fn($t) => explode(',', $t))
                ->flatten()
                ->map(fn($t) => trim($t))
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take(3)
                ->keys()
                ->toArray();
        };

        $topStrengths = $extractThemes('areas_of_strength');
        $topImprovements = $extractThemes('areas_for_improvement');

        return (object) [
            'avgRating' => $avgRating,
            'totalReviews' => $totalReviews,
            'avgCriteria' => $avgCriteria,
            'lastEvaluationDate' => $group->max('evaluation_date'),
            'trend' => $trend,
            'ratingDistribution' => $distribution,
            'topStrengths' => $topStrengths,
            'topImprovements' => $topImprovements,
        ];
    }
}
