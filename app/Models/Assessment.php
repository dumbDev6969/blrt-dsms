<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentFactory> */
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'instructor_id',
        'booking_session_id',
        'assessment_type',
        'assessment_date',
        'pre_drive_checklist',
        'pre_drive_errors',
        'immediate_fails',
        'immediate_fail_count',
        'driving_skills',
        'driving_skills_rating',
        'number_of_attempts',
        'traffic_rules',
        'traffic_rules_rating',
        'learner_type',
        'recommended_for_additional_driving',
        'instructor_remarks',
        'observations',
        'is_passed',
        'failure_reason',
        'instructor_signature_path',
        'instructor_signature_date',
        'admin_signature_path',
        'admin_noted_date',
        'noted_by',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'pre_drive_checklist' => 'array',
        'immediate_fails' => 'array',
        'driving_skills' => 'array',
        'traffic_rules' => 'array',
        'recommended_for_additional_driving' => 'boolean',
        'is_passed' => 'boolean',
        'instructor_signature_date' => 'date',
        'admin_noted_date' => 'date',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_id');
    }

    public function bookingSession(): BelongsTo
    {
        return $this->belongsTo(BookingSession::class);
    }

    public function notedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'noted_by');
    }
}
