<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'ENR-' . \Illuminate\Support\Arr::first(explode('-', (string) \Illuminate\Support\Str::uuid())),
            'student_id' => \App\Models\StudentProfile::factory(),
            'course_id' => \App\Models\Course::factory(),
            'status' => 'pending',
            'progress_percent' => 0,
            'total_amount' => 1000,
            'amount_paid' => 0,
            'balance' => 1000,
            'tdc_status' => 'not_started',
            'pdc_status' => 'not_started',
        ];
    }
}
