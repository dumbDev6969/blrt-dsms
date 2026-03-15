<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudentProfile;
use App\Models\BookingSession;
use App\Models\EnrollmentForm;
use App\Models\InstructorProfile;
use App\Models\Course;

class Enrollment extends Model
{
    /** @use HasFactory<\Database\Factories\EnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'enrollment_form_id',
        'code',
        'student_id',
        'course_id',
        'instructor_id',
        'status',
        'progress_percent',
        'final_grade',
        'start_date',
        'target_completion_date',
        'total_amount',
        'amount_paid',
        'balance',
        'tdc_hours_completed',
        'tdc_hours_required',
        'pdc_hours_completed',
        'pdc_hours_required',
        'pdc_kms_driven',
        'tdc_status',
        'pdc_status',
    ];

    protected $casts = [
        'status' => 'string',
        'progress_percent' => 'decimal:2',
        'final_grade' => 'decimal:2',
        'start_date' => 'date',
        'target_completion_date' => 'date',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'pdc_hours_completed' => 'decimal:2',
        'pdc_hours_required' => 'decimal:2',
        'pdc_kms_driven' => 'decimal:2',
        'tdc_status' => 'string',
        'pdc_status' => 'string',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function bookingSessions()
    {
        return $this->hasMany(BookingSession::class);
    }

    public function enrollmentForm()
    {
        return $this->belongsTo(EnrollmentForm::class);
    }

    public function instructorProfile()
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
