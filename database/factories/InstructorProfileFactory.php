<?php

namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstructorProfile>
 */
class InstructorProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            
            // Generate a fake license number format (e.g., D01-23-456789)
            'license_number' => strtoupper($this->faker->bothify('?##-##-######')),
            
            'license_expiry' => $this->faker->dateTimeBetween('+1 year', '+5 years'),
            
            // JSON: Return an array (Laravel casts will handle conversion to JSON)
            'skills' => $this->faker->randomElements(
                ['Manual Transmission', 'Automatic Transmission', 'Defensive Driving', 'Theoretical Driving', 'Maintenance'], 
                rand(1, 3)
            ),
            
            // JSON: Vehicle Types
            'vehicle_types' => $this->faker->randomElements(
                ['Sedan', 'SUV', 'Hatchback', 'Motorcycle', 'Light Truck'], 
                rand(1, 2)
            ),
            
            // JSON: A sample weekly schedule structure
            'weekly_schedule' => [
                'Monday'    => ['08:00-12:00', '13:00-17:00'],
                'Wednesday' => ['08:00-12:00'],
                'Friday'    => ['08:00-12:00', '13:00-16:00'],
            ],
            
            'is_active' => true,
        ];
    }
}
