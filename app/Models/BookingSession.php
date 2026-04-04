<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enrollment;

class BookingSession extends Model
{
    /** @use HasFactory<\Database\Factories\BookingSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'instructor_id',
        'vehicle_id',
        'start_time',
        'end_time',
        'type',
        'status',
        'score',
        'is_passed',
        'rating',
        'review_comment',
        'skill_progress',
        'change_log',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'score' => 'decimal:2',
        'is_passed' => 'boolean',
        'rating' => 'integer',
        'skill_progress' => 'json',
        'change_log' => 'json',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function instructorProfile()
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function instructorPerformance()
    {
        return $this->hasOne(InstructorPerformance::class, 'booking_session_id');
    }
}
