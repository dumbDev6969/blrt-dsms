<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
}
