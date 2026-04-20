<?php

namespace Database\Factories\Library;

use App\Models\Library\LibrarySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibrarySettingFactory extends Factory {
    protected $model = LibrarySetting::class;

    public function definition(): array {
        return [
            'key' => $this->faker->unique()->word,
            'value' => [],
        ];
    }
}
