<?php

namespace Tests\Feature\Library;

use App\Models\Author;
use App\Models\Book;
use App\Models\Copy;
use App\Models\Grade;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use App\Models\Publisher;
use App\Models\Role;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\User;

trait LibraryTestHelper {
    protected function seedDefaultLibrarySettings(): void {
        LibrarySetting::set('loan_period', ['student' => 14, 'staff' => 30]);
        LibrarySetting::set('max_books', ['student' => 3, 'staff' => 5]);
        LibrarySetting::set('fine_rate_per_day', ['student' => 1.00, 'staff' => 2.00]);
        LibrarySetting::set('max_renewals', ['student' => 1, 'staff' => 2]);
        LibrarySetting::set('fine_threshold', ['amount' => 50.00]);
        LibrarySetting::set('lost_book_fine', ['amount' => 100.00]);
        LibrarySetting::set('lost_book_period', ['student' => 60, 'staff' => 60]);
        LibrarySetting::set('reservation_pickup_window', ['days' => 3]);
        LibrarySetting::set('max_reservations_per_borrower', ['student' => 2, 'staff' => 3]);
        LibrarySetting::set('overdue_sms_enabled', false);
    }

    protected function createLibrarianUser(): User {
        $role = Role::firstOrCreate(['name' => 'Librarian']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    protected function createAdminUser(): User {
        $role = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    protected function createStaffBorrower(): User {
        return User::factory()->create();
    }

    protected function createStudentBorrower(): Student {
        // StudentFactory depends on Grade::find(1) and Sponsor::all()->random()
        $this->seedGradeAndSponsorIfNeeded();

        return Student::factory()->create();
    }

    protected function seedGradeAndSponsorIfNeeded(): void {
        // StudentFactory references grades 1,2,3,4,9,10 via Grade::find($id)
        $gradeData = [
            1 => ['name' => 'F1', 'promotion' => 'F2', 'description' => 'Form 1', 'level' => 'Junior'],
            2 => ['name' => 'F2', 'promotion' => 'F3', 'description' => 'Form 2', 'level' => 'Junior'],
            3 => ['name' => 'F3', 'promotion' => 'Alumni', 'description' => 'Form 3', 'level' => 'Junior'],
            4 => ['name' => 'STD 1', 'promotion' => 'STD 2', 'description' => 'Standard 1', 'level' => 'Primary'],
            9 => ['name' => 'F4', 'promotion' => 'F5', 'description' => 'Form 4', 'level' => 'Senior'],
            10 => ['name' => 'F5', 'promotion' => 'Alumni', 'description' => 'Form 5', 'level' => 'Senior'],
        ];

        // Get a valid term_id for the grades
        $termId = \App\Models\Term::first()?->id ?? 1;

        foreach ($gradeData as $id => $data) {
            if (!Grade::find($id)) {
                \Illuminate\Support\Facades\DB::table('grades')->insert([
                    'id' => $id,
                    'sequence' => $id,
                    'name' => $data['name'],
                    'promotion' => $data['promotion'],
                    'description' => $data['description'],
                    'level' => $data['level'],
                    'term_id' => $termId,
                    'active' => 1,
                    'year' => 2023,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Ensure at least one sponsor exists
        if (Sponsor::count() === 0) {
            Sponsor::factory()->create();
        }
    }

    protected function createAvailableCopy(?Book $book = null): Copy {
        return Copy::factory()->create([
            'book_id' => ($book ?? Book::factory()->create())->id,
            'status' => 'available',
        ]);
    }

    protected function createCheckedOutCopy($borrower, ?User $librarian = null): array {
        $librarian = $librarian ?? $this->createLibrarianUser();
        $copy = Copy::factory()->checkedOut()->create();

        $borrowerType = $borrower instanceof Student ? 'student' : 'user';

        $transaction = LibraryTransaction::factory()->create([
            'copy_id' => $copy->id,
            'borrower_type' => $borrowerType,
            'borrower_id' => $borrower->id,
            'checked_out_by' => $librarian->id,
            'status' => 'checked_out',
            'checkout_date' => now(),
            'due_date' => now()->addDays(14),
        ]);

        return [$copy, $transaction];
    }

    protected function createOverdueTransaction($borrower, int $daysOverdue = 5, ?User $librarian = null): array {
        $librarian = $librarian ?? $this->createLibrarianUser();
        $copy = Copy::factory()->checkedOut()->create();

        $borrowerType = $borrower instanceof Student ? 'student' : 'user';

        $transaction = LibraryTransaction::factory()->create([
            'copy_id' => $copy->id,
            'borrower_type' => $borrowerType,
            'borrower_id' => $borrower->id,
            'checked_out_by' => $librarian->id,
            'status' => 'overdue',
            'checkout_date' => now()->subDays($daysOverdue + 14),
            'due_date' => now()->subDays($daysOverdue),
        ]);

        return [$copy, $transaction];
    }

    protected function createFine(LibraryTransaction $transaction, string $amount = '10.00', string $type = 'overdue'): LibraryFine {
        return LibraryFine::factory()->create([
            'library_transaction_id' => $transaction->id,
            'borrower_type' => $transaction->borrower_type,
            'borrower_id' => $transaction->borrower_id,
            'fine_type' => $type,
            'amount' => $amount,
            'daily_rate' => $type === 'overdue' ? '2.00' : null,
        ]);
    }
}
