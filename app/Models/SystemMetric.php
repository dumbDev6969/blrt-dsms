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
}
