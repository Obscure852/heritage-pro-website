<?php

namespace Database\Factories\Library;

use App\Models\Book;
use App\Models\Library\LibraryReservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryReservationFactory extends Factory {
    protected $model = LibraryReservation::class;

    public function definition(): array {
        return [
            'book_id' => Book::factory(),
            'borrower_type' => 'user',
            'borrower_id' => User::factory(),
            'status' => 'pending',
            'queue_position' => 1,
        ];
    }

    public function ready(): self {
        return $this->state([
            'status' => 'ready',
            'notified_at' => now(),
            'expires_at' => now()->addDays(3),
        ]);
    }

    public function cancelled(): self {
        return $this->state([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function fulfilled(): self {
        return $this->state([
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
        ]);
    }

    public function expired(): self {
        return $this->state([
            'status' => 'expired',
            'cancelled_at' => now(),
        ]);
    }
}
