<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InstructorProfile;
class InstructorMetric extends Model
{
    /** @use HasFactory<\Database\Factories\InstructorMetricFactory> */
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'metric_month',
        'total_sessions',
        'completed_sessions',
        'total_hours',
        'avg_rating',
        'students_taught',
        'students_passed',
        'pass_rate',
    ];

    protected $casts = [
        'metric_month' => 'date',
        'total_sessions' => 'integer',
        'completed_sessions' => 'integer',
        'total_hours' => 'decimal:2',
        'avg_rating' => 'decimal:2',
        'students_taught' => 'integer',
        'students_passed' => 'integer',
        'pass_rate' => 'decimal:2',
    ];

    public function instructor()
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_id');
    }
}
