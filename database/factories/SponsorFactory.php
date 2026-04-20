<?php

namespace Database\Factories;

use App\Models\Sponsor;  // Replace with your actual model
use Illuminate\Database\Eloquent\Factories\Factory;

class SponsorFactory extends Factory{
    protected $model = Sponsor::class;

    public function definition(){
        return [
            'connect_id' => $this->faker->unique()->numberBetween(100,9000),
            'title' => $this->faker->title,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'middle_name' => $this->faker->optional()->lastName,
            'gender' => $this->faker->randomElement(['M', 'F']),
            'date_of_birth' => $this->faker->date($format = 'Y-m-d', $max = '1970-12-31'),
            'nationality' => $this->faker->randomElement(['Motswana','Zimbabwean','South African','Zambian','Namibian','Mosotho']),
            'relation' => $this->faker->randomElement(['Mother','Father','Brother','Sister','Uncle','Grandmother','Grandfather','Relative']),
            'status' => 'Current',
            'id_number' => $this->faker->unique()->randomNumber(8, true),
            'phone' => $this->faker->phoneNumber,
            'profession' => $this->faker->jobTitle,
            'work_place' => $this->faker->company,
            'telephone' => $this->faker->phoneNumber,
            'last_updated_by' => 'Administrator',
            'year' => 2023,
        ];
    }
}
