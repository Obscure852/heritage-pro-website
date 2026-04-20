<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Copy;
use Illuminate\Database\Eloquent\Factories\Factory;

class CopyFactory extends Factory {
    protected $model = Copy::class;

    public function definition(): array {
        return [
            'book_id' => Book::factory(),
            'accession_number' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'status' => 'available',
        ];
    }

    public function checkedOut(): self {
        return $this->state(['status' => 'checked_out']);
    }

    public function lost(): self {
        return $this->state(['status' => 'lost']);
    }

    public function onHold(): self {
        return $this->state(['status' => 'on_hold']);
    }

    public function inRepair(): self {
        return $this->state(['status' => 'in_repair']);
    }

    public function missing(): self {
        return $this->state(['status' => 'missing']);
    }
}
