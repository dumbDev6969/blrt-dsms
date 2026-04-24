<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LtoClinic extends Model
{
    /** @use HasFactory<\Database\Factories\LtoClinicFactory> */
    use HasFactory;

    protected $fillable = [
        'clinic_name',
        'accreditation_number',
        'address',
        'contact_number',
        'is_active',
        'accreditation_expiry',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'accreditation_expiry' => 'date',
    ];
}
