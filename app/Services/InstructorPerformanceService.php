<?php

namespace App\Services;

use App\Models\InstructorPerformance;
use Illuminate\Support\Collection;

class InstructorPerformanceService
{
    /**
     * Get analytical performance data grouped by course for a specific instructor.
     *
     * @param int $instructorId
     * @param int|null $limit
     * @return Collection
     */
    public function getPerformancesByCourse(int $instructorId, ?int $limit = null): Collection
    {
        $performances = InstructorPerformance::query()
            ->where('instructor_id', $instructorId)
            ->with(['studentProfile.user', 'enrollment.course', 'bookingSession'])
            ->latest('evaluation_date')
            ->get();

        $grouped = $performances->groupBy(fn($p) => $p->enrollment->course_id)
            ->map(function ($group) {
                $course = $group->first()->enrollment->course;
                
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
                    'course' => $course,
                    'avgRating' => $avgRating,
                    'totalReviews' => $totalReviews,
                    'avgCriteria' => $avgCriteria,
                    'performances' => $group,
                    'lastEvaluationDate' => $group->max('evaluation_date'),
                    'trend' => $trend,
                    'ratingDistribution' => $distribution,
                    'topStrengths' => $topStrengths,
                    'topImprovements' => $topImprovements,
                ];
            })->values();

        if ($limit) {
            return $grouped->take($limit);
        }

        return $grouped;
    }
}
