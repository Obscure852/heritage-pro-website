<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommentBankFactory extends Factory{

    public function definition(){
        return [
            'type' => $this->faker->boolean,
            'body' => $this->faker->sentence(10),
            'year' => date('Y'),
        ];
    }
}
