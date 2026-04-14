<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'COURSE-' . \Illuminate\Support\Arr::first(explode('-', (string) \Illuminate\Support\Str::uuid())),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 1000, 5000),
            'duration_hours' => $this->faker->numberBetween(8, 40),
            'type' => $this->faker->randomElement(['theoretical', 'practical']),
            'prerequisites' => [],
            'is_active' => true,
        ];
    }
}
