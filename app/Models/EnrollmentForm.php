<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\StudentProfile;
use App\Models\Course;
use App\Models\Enrollment;
class EnrollmentForm extends Model
{
    /** @use HasFactory<\Database\Factories\EnrollmentFormFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'control_number',
        'package_type',
        'vehicle_category',
        'transmission',
        'status',
        'personal_info',
        'course_preferences',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'personal_info' => 'array',
        'course_preferences' => 'array',
        'reviewed_at' => 'datetime',
    ];

    // Generate a unique code.
    protected static function generateUniqueCode()
    {
        do {
            $controlNumber = 'BLRT-' . Str::upper(Str::random(8));
        } while (self::where('control_number', $controlNumber)->exists());

        return $controlNumber;
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function enrollment()
    {
        return $this->hasOne(Enrollment::class);
    }
}
