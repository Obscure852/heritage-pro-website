<?php

namespace Tests\Feature\Library;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use App\Services\Library\FineService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FineServiceTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    protected FineService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->seedDefaultLibrarySettings();
        $this->service = app(FineService::class);
    }

    // ==================== ASSESS ====================

    public function test_assess_creates_fine_for_overdue(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 5);

        $result = $this->service->assessOverdueFines();

        $this->assertEquals(1, $result['assessed']);
        $this->assertDatabaseHas('library_fines', [
            'library_transaction_id' => $transaction->id,
            'fine_type' => 'overdue',
        ]);
    }

    public function test_assess_uses_rate_snapshot(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 5);

        $this->service->assessOverdueFines();

        $fine = LibraryFine::where('library_transaction_id', $transaction->id)->first();
        // Staff rate is 2.00 per day
        $this->assertEquals('2.00', $fine->daily_rate);
    }

    public function test_assess_idempotent(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 5);

        $this->service->assessOverdueFines();
        $result = $this->service->assessOverdueFines();

        // Second call should update (or no-op), not create a new fine
        $this->assertEquals(0, $result['assessed']);
        $fineCount = LibraryFine::where('library_transaction_id', $transaction->id)
            ->where('fine_type', 'overdue')
            ->count();
        $this->assertEquals(1, $fineCount);
    }

    public function test_assess_skips_returned_transactions(): void {
        $borrower = $this->createStaffBorrower();

        LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'returned',
            'due_date' => now()->subDays(5),
            'return_date' => now(),
        ]);

        $result = $this->service->assessOverdueFines();

        $this->assertEquals(0, $result['assessed']);
    }

    public function test_assess_skips_lost_with_existing_fine(): void {
        $borrower = $this->createStaffBorrower();
        $copy = Copy::factory()->lost()->create();

        $transaction = LibraryTransaction::factory()->create([
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'lost',
            'due_date' => now()->subDays(60),
        ]);

        // Create existing lost fine
        LibraryFine::factory()->create([
            'library_transaction_id' => $transaction->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'fine_type' => 'lost',
            'amount' => '100.00',
        ]);

        $result = $this->service->assessOverdueFines();

        $this->assertEquals(1, $result['skipped']);
    }

    public function test_calculate_amount_uses_bcmath(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 7);

        $this->service->assessOverdueFines();

        $fine = LibraryFine::where('library_transaction_id', $transaction->id)->first();
        // Staff rate 2.00 * 7 days = 14.00 (using bcmath)
        $expected = bcmul('2.00', '7', 2);
        $this->assertEquals($expected, $fine->amount);
    }

    public function test_calculate_amount_for_multiple_days(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 10);

        $this->service->assessOverdueFines();

        $fine = LibraryFine::where('library_transaction_id', $transaction->id)->first();
        // 2.00 * 10 = 20.00
        $this->assertEquals('20.00', $fine->amount);
    }

    // ==================== PAYMENT ====================

    public function test_record_payment_full(): void {
        $borrower = $this->createStaffBorrower();
        $transaction = LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
        ]);
        $fine = $this->createFine($transaction, '10.00');
        $librarian = $this->createLibrarianUser();

        $result = $this->service->recordPayment($fine, 10.00, $librarian->id);

        $this->assertEquals('paid', $result->status);
        $this->assertEquals('10.00', $result->amount_paid);
    }

    public function test_record_payment_partial(): void {
        $borrower = $this->createStaffBorrower();
        $transaction = LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
        ]);
        $fine = $this->createFine($transaction, '10.00');
        $librarian = $this->createLibrarianUser();

        $result = $this->service->recordPayment($fine, 3.00, $librarian->id);

        $this->assertEquals('partial', $result->status);
        $this->assertEquals('3.00', $result->amount_paid);
    }

    public function test_record_payment_exceeds_balance(): void {
        $borrower = $this->createStaffBorrower();
        $transaction = LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
        ]);
        $fine = $this->createFine($transaction, '10.00');
        $librarian = $this->createLibrarianUser();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('exceeds');

        $this->service->recordPayment($fine, 15.00, $librarian->id);
    }

    // ==================== WAIVER ====================

    public function test_waive_fine_full(): void {
        $borrower = $this->createStaffBorrower();
        $transaction = LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
        ]);
        $fine = $this->createFine($transaction, '10.00');
        $admin = $this->createAdminUser();

        $result = $this->service->waiveFine($fine, 10.00, $admin->id, 'Compassionate grounds');

        $this->assertEquals('waived', $result->status);
        $this->assertEquals('10.00', $result->amount_waived);
    }

    public function test_waive_fine_partial(): void {
        $borrower = $this->createStaffBorrower();
        $transaction = LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
        ]);
        $fine = $this->createFine($transaction, '10.00');
        $admin = $this->createAdminUser();

        $result = $this->service->waiveFine($fine, 5.00, $admin->id, 'Partial waiver');

        $this->assertEquals('partial', $result->status);
        $this->assertEquals('5.00', $result->amount_waived);
    }

    public function test_waive_fine_records_authorized_by(): void {
        $borrower = $this->createStaffBorrower();
        $transaction = LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
        ]);
        $fine = $this->createFine($transaction, '10.00');
        $admin = $this->createAdminUser();

        $result = $this->service->waiveFine($fine, 10.00, $admin->id, 'Approved by admin');

        $this->assertEquals($admin->id, $result->waived_by);
        $this->assertEquals('Approved by admin', $result->waiver_reason);
    }

    // ==================== LOST BOOK FINE ====================

    public function test_assess_lost_book_fine_uses_book_price(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create(['price' => 75.50]);
        $copy = Copy::factory()->lost()->create(['book_id' => $book->id]);

        $transaction = LibraryTransaction::factory()->create([
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'lost',
        ]);

        $fine = $this->service->assessLostBookFine($transaction);

        $this->assertEquals('75.50', $fine->amount);
        $this->assertEquals('lost', $fine->fine_type);
    }

    public function test_assess_lost_book_fine_uses_setting_fallback(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create(['price' => 0]);
        $copy = Copy::factory()->lost()->create(['book_id' => $book->id]);

        $transaction = LibraryTransaction::factory()->create([
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'lost',
        ]);

        $fine = $this->service->assessLostBookFine($transaction);

        // Default lost_book_fine setting is 100.00
        $this->assertEquals('100.00', $fine->amount);
    }

    // ==================== STATUS CALCULATION ====================

    public function test_get_outstanding_balance(): void {
        $fine = LibraryFine::factory()->create([
            'amount' => '20.00',
            'amount_paid' => '5.00',
            'amount_waived' => '3.00',
        ]);

        // outstanding = 20.00 - 5.00 - 3.00 = 12.00
        $this->assertEquals(12.00, $fine->outstanding);
    }

    public function test_calculate_status_waived(): void {
        $status = $this->service->calculateStatus('10.00', '0.00', '10.00');
        $this->assertEquals('waived', $status);
    }

    public function test_calculate_status_paid(): void {
        $status = $this->service->calculateStatus('10.00', '10.00', '0.00');
        $this->assertEquals('paid', $status);
    }
}
