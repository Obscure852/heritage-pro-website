<?php

namespace Database\Factories\Library;

use App\Models\Library\LibraryFine;
use App\Models\Library\LibraryTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryFineFactory extends Factory {
    protected $model = LibraryFine::class;

    public function definition(): array {
        return [
            'library_transaction_id' => LibraryTransaction::factory(),
            'borrower_type' => 'user',
            'borrower_id' => 1,
            'fine_type' => 'overdue',
            'amount' => '10.00',
            'amount_paid' => '0.00',
            'amount_waived' => '0.00',
            'daily_rate' => '2.00',
            'fine_date' => now(),
            'status' => 'pending',
        ];
    }

    public function lost(): self {
        return $this->state([
            'fine_type' => 'lost',
            'amount' => '100.00',
            'daily_rate' => null,
        ]);
    }

    public function partial(string $amountPaid = '5.00'): self {
        return $this->state([
            'amount_paid' => $amountPaid,
            'status' => 'partial',
        ]);
    }

    public function paid(): self {
        return $this->state(function (array $attributes) {
            return [
                'amount_paid' => $attributes['amount'],
                'status' => 'paid',
            ];
        });
    }

    public function waived(): self {
        return $this->state(function (array $attributes) {
            return [
                'amount_waived' => $attributes['amount'],
                'status' => 'waived',
            ];
        });
    }
}
