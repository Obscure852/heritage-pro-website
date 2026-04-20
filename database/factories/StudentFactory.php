<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Student;  // Replace with your actual model
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sponsor;

class StudentFactory extends Factory{
    protected $model = Student::class;

    public function definition(){
        return [
            'connect_id' => Sponsor::all()->random()->id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'gender' => $this->faker->randomElement(['M', 'F']),
            'date_of_birth' => $this->faker->date($format = 'Y-m-d', $max = '2017-12-31'),
            'nationality' => $this->faker->country,
            'id_number' => $this->faker->unique()->randomNumber(8, true),
            'status' => 'Current',
            'year' => 2023,
            'sponsor_id' => Sponsor::all()->random()->id,
            'password' => bcrypt('password'),
        ];
    }
}
