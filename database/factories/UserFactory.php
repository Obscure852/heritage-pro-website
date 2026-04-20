<?php

namespace Database\Factories;

use App\Models\User;  // Replace with your actual model
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Term;

class UserFactory extends Factory{
    protected $model = User::class;

    public function definition(){
        return [
            'firstname' => $this->faker->firstName,
            'middlename' => $this->faker->optional()->lastName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'avatar' => $this->faker->imageUrl(),
            'gender' => $this->faker->randomElement(['M', 'F']),
            'date_of_birth' => $this->faker->date($format = 'd-m-Y', $max = '14-07-1987'),
            'position' => $this->faker->jobTitle,
            'area_of_work' => $this->faker->randomElement(['Teaching','Administration','Teaching Aids','Non Teaching']),
            'phone' => $this->faker->phoneNumber,
            'id_number' => $this->faker->unique()->randomNumber(8, true),
            'city' => $this->faker->city,
            'address' => $this->faker->address,
            'active' => $this->faker->boolean,
            'status' => 'Current',
            'username' => $this->faker->unique()->userName,
            'year' => 2023,
            'last_updated_by' => 'Administrator',
            'password' => bcrypt('password'), 
        ];
    }
}
