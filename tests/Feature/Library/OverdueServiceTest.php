<?php

namespace Tests\Feature\Library;

use App\Models\Copy;
use App\Models\Library\LibraryAuditLog;
use App\Models\Library\LibraryTransaction;
use App\Services\Library\OverdueService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OverdueServiceTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    protected OverdueService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->seedDefaultLibrarySettings();
        $this->service = app(OverdueService::class);
    }

    public function test_detect_marks_overdue_transactions(): void {
        $borrower = $this->createStaffBorrower();
        $copy = Copy::factory()->checkedOut()->create();

        LibraryTransaction::factory()->create([
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'checked_out',
            'due_date' => now()->subDays(3),
        ]);

        $result = $this->service->detectAndMarkOverdue();

        $this->assertEquals(1, $result['marked_overdue']);
    }

    public function test_detect_skips_already_overdue(): void {
        $borrower = $this->createStaffBorrower();

        // Create already-overdue transaction
        $this->createOverdueTransaction($borrower, 5);

        $result = $this->service->detectAndMarkOverdue();

        // Should not re-mark it (0 newly marked, but already_overdue counts it)
        $this->assertEquals(0, $result['marked_overdue']);
        $this->assertGreaterThan(0, $result['already_overdue']);
    }

    public function test_detect_skips_returned_transactions(): void {
        $borrower = $this->createStaffBorrower();

        LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'returned',
            'due_date' => now()->subDays(3),
            'return_date' => now()->subDays(1),
        ]);

        $result = $this->service->detectAndMarkOverdue();

        $this->assertEquals(0, $result['marked_overdue']);
    }

    public function test_get_overdue_by_bracket(): void {
        $borrower = $this->createStaffBorrower();

        // Create transactions in different brackets
        $this->createOverdueTransaction($borrower, 3);  // 1-7 bracket
        $this->createOverdueTransaction($borrower, 10); // 8-14 bracket
        $this->createOverdueTransaction($borrower, 20); // 15-30 bracket
        $this->createOverdueTransaction($borrower, 45); // 30+ bracket

        $brackets = $this->service->getOverdueBrackets();

        $this->assertCount(1, $brackets['1-7']);
        $this->assertCount(1, $brackets['8-14']);
        $this->assertCount(1, $brackets['15-30']);
        $this->assertCount(1, $brackets['30+']);
    }

    public function test_declare_lost_updates_status(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 10);

        $this->service->declareLost($transaction);

        $this->assertEquals('lost', $transaction->fresh()->status);
        $this->assertEquals('lost', $copy->fresh()->status);
    }

    public function test_declare_lost_assesses_fine(): void {
        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 10);

        $this->service->declareLost($transaction);

        $this->assertDatabaseHas('library_fines', [
            'library_transaction_id' => $transaction->id,
            'fine_type' => 'lost',
        ]);
    }

    public function test_get_overdue_transactions(): void {
        $borrower = $this->createStaffBorrower();
        $this->createOverdueTransaction($borrower, 5);

        $transactions = $this->service->getOverdueTransactions();

        $this->assertCount(1, $transactions);
        $this->assertTrue($transactions->first()->days_overdue >= 4);
    }

    public function test_detect_creates_audit_log(): void {
        $borrower = $this->createStaffBorrower();
        $copy = Copy::factory()->checkedOut()->create();

        LibraryTransaction::factory()->create([
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'checked_out',
            'due_date' => now()->subDays(3),
        ]);

        $this->service->detectAndMarkOverdue();

        $this->assertDatabaseHas('library_audit_logs', [
            'action' => 'overdue_detected',
        ]);
    }
}
