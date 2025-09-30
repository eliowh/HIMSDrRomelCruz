<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'middle_name' => $this->faker->lastName,
            'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
            'province' => 'Bulacan',
            'city' => 'Malolos City',
            'barangay' => $this->faker->word,
            'nationality' => $this->faker->country,
            // leave patient_no null so model assigns it automatically during creating
            'patient_no' => null,
        ];
    }
}
?>