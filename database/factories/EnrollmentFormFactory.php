<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnrollmentForm>
 */
class EnrollmentFormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => \App\Models\StudentProfile::factory(),
            'course_id' => \App\Models\Course::factory(),
            'control_number' => 'BLRT-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8)),
            'package_type' => 'TDC',
            'status' => 'draft',
            'personal_info' => [
                'emergency_contact' => [
                    'name' => $this->faker->name(),
                    'number' => $this->faker->phoneNumber(),
                ],
            ],
            'course_preferences' => [
                'schedule_pref' => ['Monday', 'Wednesday', 'Friday'],
                'instructor_pref' => null,
            ],
        ];
    }
}
