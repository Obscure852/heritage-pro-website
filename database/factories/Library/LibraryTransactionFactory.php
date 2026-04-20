<?php

namespace Database\Factories\Library;

use App\Models\Copy;
use App\Models\Library\LibraryTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryTransactionFactory extends Factory {
    protected $model = LibraryTransaction::class;

    public function definition(): array {
        return [
            'copy_id' => Copy::factory(),
            'borrower_type' => 'user',
            'borrower_id' => User::factory(),
            'checkout_date' => now(),
            'due_date' => now()->addDays(14),
            'return_date' => null,
            'status' => 'checked_out',
            'renewal_count' => 0,
            'checked_out_by' => User::factory(),
        ];
    }

    public function overdue(int $daysOverdue = 5): self {
        return $this->state([
            'status' => 'overdue',
            'checkout_date' => now()->subDays($daysOverdue + 14),
            'due_date' => now()->subDays($daysOverdue),
        ]);
    }

    public function returned(): self {
        return $this->state([
            'status' => 'returned',
            'return_date' => now(),
        ]);
    }

    public function lost(): self {
        return $this->state([
            'status' => 'lost',
            'checkout_date' => now()->subDays(90),
            'due_date' => now()->subDays(60),
        ]);
    }

    public function forStudent(int $studentId): self {
        return $this->state([
            'borrower_type' => 'student',
            'borrower_id' => $studentId,
        ]);
    }

    public function forStaff(int $userId): self {
        return $this->state([
            'borrower_type' => 'user',
            'borrower_id' => $userId,
        ]);
    }
}
