<?php

namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a birth date between 16 and 40 years ago
        $birthDate = $this->faker->dateTimeBetween('-40 years', '-16 years');
        
        // Calculate age to determine if minor
        $age = $birthDate->diff(now())->y;
        $isMinor = $age < 18;

        return [
            'user_id' => \App\Models\User::factory(),
            
            // Personal Details
            'birth_date' => $birthDate,
            'contact_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'nationality' => $this->faker->randomElement(['filipino', 'foreigner']),
            'is_minor' => $isMinor,
            'occupation' => $isMinor ? 'Student' : $this->faker->jobTitle(),
            
            // Enums
            'educational_attainment' => $this->faker->randomElement(['elementary', 'high_school', 'college', 'post_graduate']),
            'civil_status' => $this->faker->randomElement(['single', 'married', 'widowed', 'separated']),
            'sex' => $this->faker->randomElement(['male', 'female']),
            
            // Licensing IDs (Nullable)
            'ltms_client_id' => $this->faker->numerify('##############'), // 15 digits
            'student_permit_or_license_no' => $this->faker->bothify('?##-##-######'),
            'is_active' => $this->faker->boolean(90), // 90% active

            // JSON Meta Details (Optional/Nullable data)
            'meta_details' => [
                'guardian_name' => $isMinor ? $this->faker->name() : null,
                'guardian_contact' => $isMinor ? $this->faker->phoneNumber() : null,
                'blood_type' => $this->faker->randomElement(['A+', 'O+', 'B+', 'AB+']),
            ],
        ];
    }
}
