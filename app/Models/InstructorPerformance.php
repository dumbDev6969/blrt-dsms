<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorPerformance extends Model
{
    /** @use HasFactory<\Database\Factories\InstructorPerformanceFactory> */
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'student_id',
        'enrollment_id',
        'booking_session_id',
        'rating',
        'performance_criteria',
        'feedback_comment',
        'areas_of_strength',
        'areas_for_improvement',
        'evaluation_date',
    ];

    protected $casts = [
        'performance_criteria' => 'array',
        'rating' => 'integer',
        'evaluation_date' => 'date',
    ];

    public function instructorProfile()
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_id');
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function bookingSession()
    {
        return $this->belongsTo(BookingSession::class, 'booking_session_id');
    }
}
