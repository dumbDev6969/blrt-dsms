<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EnrollmentForm;
use App\Models\BookingSession;
use App\Models\Enrollment;
class StudentProfile extends Model
{
    /** @use HasFactory<\Database\Factories\StudentProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'birth_date',
        'contact_number',
        'address',
        'nationality',
        'is_minor',
        'occupation',
        'educational_attainment',
        'civil_status',
        'sex',
        'ltms_client_id',
        'student_permit_or_license_no',
        'meta_details',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'birth_date' => 'date',           // Automatically converts to Carbon instance
        'is_minor' => 'boolean',          // Converts 0/1 to true/false
        'meta_details' => 'array',        // KEY: Automatically decodes JSON to PHP array
        'is_active' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollmentForms()
    {
        return $this->hasMany(EnrollmentForm::class, 'student_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'student_id');
    }
}
