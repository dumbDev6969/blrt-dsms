<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\BookingSession;
use App\Models\InstructorMetric;

class InstructorProfile extends Model
{
    /** @use HasFactory<\Database\Factories\InstructorProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'license_expiry',
        'skills',
        'vehicle_types',
        'weekly_schedule',
        'is_active',
        'status',
    ];

    protected $casts = [
        'license_expiry' => 'date',   // Casts to Carbon instance
        'skills' => 'array',          // Auto-converts JSON to PHP Array
        'vehicle_types' => 'array',   // Auto-converts JSON to PHP Array
        'weekly_schedule' => 'array', // Auto-converts JSON to PHP Array
        'is_active' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'instructor_id');
    }

    public function bookingSessions()
    {
        return $this->hasMany(BookingSession::class, 'instructor_id');
    }

    public function metrics()
    {
        return $this->hasMany(InstructorMetric::class, 'instructor_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'instructor_id');
    }

    public function instructorPerformances()
    {
        return $this->hasMany(InstructorPerformance::class, 'instructor_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Whether the instructor can perform actions (view students, manage schedule, etc.).
     * True for any admin-approved instructor, even if they've paused session acceptance.
     */
    public function canPerformActions(): bool
    {
        return in_array($this->status, ['verified', 'not_accepting', 'on_leave']) && $this->is_active;
    }

    /**
     * Whether the instructor is actively accepting new sessions/enrollments.
     * Used by the automated matching system and the waiting list.
     */
    public function isAccepting(): bool
    {
        return $this->status === 'verified' && $this->is_active;
    }
}
