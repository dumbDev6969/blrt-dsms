<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\EnrollmentForm;
use App\Models\Enrollment;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'price',
        'duration_hours',
        'type',
        'prerequisites',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'decimal:2' ensures price is returned as a string with 2 decimal places, 
        // preventing floating-point errors common with money.
        'price' => 'decimal:2',
        // 'integer' ensures strict type safety for the duration.
        'duration_hours' => 'integer',
        'prerequisites' => 'array',
        'is_active' => 'boolean',

    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted()
    {
        static::creating(function ($course) {
            // Check if code is empty to allow manual overrides
            if (empty($course->code)) {
                $course->code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Generate a unique code.
     */
    protected static function generateUniqueCode()
    {
        do {
            // Generate a random string (e.g., "CRS-X7Z9")
            $code = 'BLRT-' . Str::upper(Str::random(6));
        } while (self::where('code', $code)->exists()); // Ensure it doesn't exist

        return $code;
    }

    public function enrollmentForms()
    {
        return $this->hasMany(EnrollmentForm::class, 'course_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
