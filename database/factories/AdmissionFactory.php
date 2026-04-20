<?php

namespace Database\Factories;

use App\Models\Admission;  // Replace with your actual model
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Term;

class AdmissionFactory extends Factory{
    protected $model = Admission::class;

    public function definition(){
        return [
            'sponsor_id' => Sponsor::all()->random()->id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'middle_name' => $this->faker->optional()->lastName,
            'gender' => $this->faker->randomElement(['M', 'F']),
            'date_of_birth' => $this->faker->date($format = 'Y-m-d', $max = '2018-07-14'),
            'nationality' => $this->faker->country,
            'id_number' => $this->faker->unique()->randomNumber(8, true),
            'grade_applying_for' => $this->faker->randomElement(['STD 1', 'STD 2', 'STD 3']),
            'academic_year_applying_for' => $this->faker->year($max = 'now'),
            'application_date' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
            'status' => $this->faker->randomElement(['Pending','Waiting','Accepted','To be interviewed']),
            'term_id' => 2,
            'year' => 2023,
        ];
    }
}
