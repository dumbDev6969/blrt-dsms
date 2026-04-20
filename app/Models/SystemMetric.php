<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemMetric extends Model
{
    /** @use HasFactory<\Database\Factories\SystemMetricFactory> */
    use HasFactory;
    
    protected $fillable = [
        'metric_date',
        'new_students',
        'active_enrollments',
        'completed_courses',
        'total_bookings',
        'revenue',
        'additional_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'additional_data' => 'array',
            'revenue' => 'decimal:2',
        ];
    }

    /**
     * Synchronize today's real-time metrics into the system_metrics table.
     */
    public static function syncToday()
    {
        $today = \Carbon\Carbon::today();

        $metric = self::firstOrNew(['metric_date' => $today]);

        // Revenue: Sum of all Enrollments made today
        $metric->revenue = \App\Models\Enrollment::whereDate('created_at', $today)->sum('amount_paid');

        // New Students: Count of users created today with role 'Student'
        $metric->new_students = \App\Models\User::role('Student')->whereDate('created_at', $today)->count();

        // Active Enrollments: Total count of active enrollments
        $metric->active_enrollments = \App\Models\Enrollment::where('status', 'active')->count();

        // Completed Courses: Total count of completed enrollments
        $metric->completed_courses = \App\Models\Enrollment::where('status', 'completed')->count();

        // Total Bookings: Total count of booking sessions today
        if (class_exists(\App\Models\BookingSession::class)) {
            $metric->total_bookings = \App\Models\BookingSession::whereDate('created_at', $today)->count();
        }

        $metric->save();

        return $metric;
    }
}
