<?php

namespace Tests\Feature\Library;

use App\Models\Copy;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibraryReservation;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use App\Services\Library\CirculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CirculationServiceTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    protected CirculationService $service;
    protected $librarian;

    protected function setUp(): void {
        parent::setUp();
        $this->seedDefaultLibrarySettings();
        $this->service = app(CirculationService::class);
        $this->librarian = $this->createLibrarianUser();
    }

    // ==================== CHECKOUT ====================

    public function test_checkout_creates_transaction(): void {
        $borrower = $this->createStaffBorrower();
        $copy = $this->createAvailableCopy();

        $transaction = $this->service->checkout($copy, 'user', $borrower->id, $this->librarian->id);

        $this->assertDatabaseHas('library_transactions', [
            'id' => $transaction->id,
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'checked_out',
        ]);
    }

    public function test_checkout_sets_due_date_from_settings(): void {
        $borrower = $this->createStaffBorrower();
        $copy = $this->createAvailableCopy();

        // Staff loan period is 30 days
        $transaction = $this->service->checkout($copy, 'user', $borrower->id, $this->librarian->id);

        $expectedDueDate = now()->addDays(30)->toDateString();
        $this->assertEquals($expectedDueDate, $transaction->due_date->toDateString());
    }

    public function test_checkout_updates_copy_status(): void {
        $borrower = $this->createStaffBorrower();
        $copy = $this->createAvailableCopy();

        $this->service->checkout($copy, 'user', $borrower->id, $this->librarian->id);

        $this->assertEquals('checked_out', $copy->fresh()->status);
    }

    public function test_checkout_blocked_by_overdue_books(): void {
        $borrower = $this->createStaffBorrower();
        $this->createOverdueTransaction($borrower);

        $copy = $this->createAvailableCopy();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('overdue');

        $this->service->checkout($copy, 'user', $borrower->id, $this->librarian->id);
    }

    public function test_checkout_blocked_by_fine_threshold(): void {
        $borrower = $this->createStaffBorrower();

        // Create a fine exceeding the threshold (50.00)
        $transaction = LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'returned',
        ]);
        LibraryFine::factory()->create([
            'library_transaction_id' => $transaction->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'amount' => '60.00',
            'amount_paid' => '0.00',
            'amount_waived' => '0.00',
            'status' => 'pending',
        ]);

        $copy = $this->createAvailableCopy();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('fines');

        $this->service->checkout($copy, 'user', $borrower->id, $this->librarian->id);
    }

    public function test_checkout_blocked_by_max_books(): void {
        $borrower = $this->createStaffBorrower();

        // Staff max is 5 books — create 5 active transactions
        for ($i = 0; $i < 5; $i++) {
            $this->createCheckedOutCopy($borrower, $this->librarian);
        }

        $copy = $this->createAvailableCopy();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('limit');

        $this->service->checkout($copy, 'user', $borrower->id, $this->librarian->id);
    }

    public function test_checkout_blocked_copy_not_available(): void {
        $borrower = $this->createStaffBorrower();
        $copy = Copy::factory()->checkedOut()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->checkout($copy, 'user', $borrower->id, $this->librarian->id);
    }

    // ==================== CHECKIN ====================

    public function test_checkin_returns_copy(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createCheckedOutCopy($borrower, $this->librarian);

        $returned = $this->service->checkin($copy, $this->librarian->id);

        $this->assertEquals('returned', $returned->status);
    }

    public function test_checkin_updates_copy_status(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createCheckedOutCopy($borrower, $this->librarian);

        $this->service->checkin($copy, $this->librarian->id);

        $this->assertEquals('available', $copy->fresh()->status);
    }

    public function test_checkin_records_returned_at(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createCheckedOutCopy($borrower, $this->librarian);

        $returned = $this->service->checkin($copy, $this->librarian->id);

        $this->assertNotNull($returned->return_date);
        $this->assertEquals(now()->toDateString(), $returned->return_date->toDateString());
    }

    public function test_checkin_nonexistent_copy_fails(): void {
        $copy = $this->createAvailableCopy();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->checkin($copy, $this->librarian->id);
    }

    // ==================== RENEWAL ====================

    public function test_renew_extends_due_date(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createCheckedOutCopy($borrower, $this->librarian);

        $renewed = $this->service->renew($transaction, $this->librarian->id);

        // Staff loan period is 30 days from today
        $expectedDueDate = now()->addDays(30)->toDateString();
        $this->assertEquals($expectedDueDate, $renewed->due_date->toDateString());
    }

    public function test_renew_increments_count(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createCheckedOutCopy($borrower, $this->librarian);

        $renewed = $this->service->renew($transaction, $this->librarian->id);

        $this->assertEquals(1, $renewed->renewal_count);
    }

    public function test_renew_blocked_at_max(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createCheckedOutCopy($borrower, $this->librarian);

        // Staff max renewals is 2
        $transaction->update(['renewal_count' => 2]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Maximum renewal limit');

        $this->service->renew($transaction, $this->librarian->id);
    }

    public function test_renew_blocked_if_overdue(): void {
        $borrower = $this->createStaffBorrower();

        // Create a separate overdue transaction
        $this->createOverdueTransaction($borrower, 5, $this->librarian);

        // Create a checked-out transaction to renew
        [$copy, $transaction] = $this->createCheckedOutCopy($borrower, $this->librarian);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('overdue');

        $this->service->renew($transaction, $this->librarian->id);
    }

    public function test_renew_excludes_self_from_overdue_check(): void {
        $borrower = $this->createStaffBorrower();

        // Create a transaction that is itself overdue (due_date in past) but status is still checked_out
        $copy = Copy::factory()->checkedOut()->create();
        $transaction = LibraryTransaction::factory()->create([
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'checked_out_by' => $this->librarian->id,
            'status' => 'checked_out',
            'checkout_date' => now()->subDays(20),
            'due_date' => now()->subDays(2),
            'renewal_count' => 0,
        ]);

        // This should succeed because the transaction excludes itself from the overdue check
        $renewed = $this->service->renew($transaction, $this->librarian->id);

        $this->assertEquals(1, $renewed->renewal_count);
    }

    // ==================== BULK OPERATIONS ====================

    public function test_bulk_checkout_all_succeed(): void {
        $borrower = $this->createStaffBorrower();
        $copies = [
            $this->createAvailableCopy(),
            $this->createAvailableCopy(),
        ];

        $results = $this->service->bulkCheckout(
            array_map(fn($c) => $c->id, $copies),
            'user', $borrower->id, $this->librarian->id
        );

        $this->assertCount(2, $results['success']);
        $this->assertCount(0, $results['errors']);
    }

    public function test_bulk_checkout_capacity_check(): void {
        $borrower = $this->createStaffBorrower();

        // Already at 4 of 5 max
        for ($i = 0; $i < 4; $i++) {
            $this->createCheckedOutCopy($borrower, $this->librarian);
        }

        // Try to checkout 2 more (only 1 slot left)
        $copies = [$this->createAvailableCopy(), $this->createAvailableCopy()];

        $results = $this->service->bulkCheckout(
            array_map(fn($c) => $c->id, $copies),
            'user', $borrower->id, $this->librarian->id
        );

        $this->assertCount(0, $results['success']);
        $this->assertCount(2, $results['errors']);
    }

    public function test_bulk_checkout_partial_failure(): void {
        $borrower = $this->createStaffBorrower();
        $availableCopy = $this->createAvailableCopy();
        $checkedOutCopy = Copy::factory()->checkedOut()->create();

        $results = $this->service->bulkCheckout(
            [$availableCopy->id, $checkedOutCopy->id],
            'user', $borrower->id, $this->librarian->id
        );

        $this->assertCount(1, $results['success']);
        $this->assertCount(1, $results['errors']);
    }

    public function test_bulk_checkin_all_succeed(): void {
        $borrower = $this->createStaffBorrower();
        [$copy1, $tx1] = $this->createCheckedOutCopy($borrower, $this->librarian);
        [$copy2, $tx2] = $this->createCheckedOutCopy($borrower, $this->librarian);

        $results = $this->service->bulkCheckin(
            [$copy1->accession_number, $copy2->accession_number],
            $this->librarian->id
        );

        $this->assertCount(2, $results['success']);
        $this->assertCount(0, $results['errors']);
    }

    // ==================== SETTINGS ====================

    public function test_settings_key_maps_user_to_staff(): void {
        $borrower = $this->createStaffBorrower();
        $copy = $this->createAvailableCopy();

        // Staff loan period is 30 days (mapped from 'user' borrower_type to 'staff' settings key)
        $transaction = $this->service->checkout($copy, 'user', $borrower->id, $this->librarian->id);

        $this->assertEquals(now()->addDays(30)->toDateString(), $transaction->due_date->toDateString());
    }

    public function test_checkout_on_hold_copy_without_reservation_blocked(): void {
        $borrower = $this->createStaffBorrower();
        $book = \App\Models\Book::factory()->create();
        $copy = Copy::factory()->onHold()->create(['book_id' => $book->id]);

        // Attempting to checkout an on_hold copy throws because
        // canLibraryCheckout requires status === 'available'
        $this->expectException(\RuntimeException::class);

        $this->service->checkout($copy, 'user', $borrower->id, $this->librarian->id);
    }

    public function test_student_checkout_uses_student_loan_period(): void {
        $student = $this->createStudentBorrower();
        $copy = $this->createAvailableCopy();

        // Student loan period is 14 days
        $transaction = $this->service->checkout($copy, 'student', $student->id, $this->librarian->id);

        $expectedDueDate = now()->addDays(14)->toDateString();
        $this->assertEquals($expectedDueDate, $transaction->due_date->toDateString());
    }
}
