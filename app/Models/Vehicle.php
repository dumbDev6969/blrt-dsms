<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory;

    protected $fillable = [
        'model',
        'plate_number',
        'transmission',
        'type',
        'status',
        'maintenance_history',
        'next_maintenance_date',
    ];

    protected $casts = [
        'maintenance_history' => 'array',
        'next_maintenance_date' => 'date',
    ];
}
