<?php

namespace Tests\Feature\Library;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Library\LibraryAuditLog;
use App\Models\Library\LibraryTransaction;
use App\Services\Library\DashboardService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardServiceTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    protected DashboardService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->seedDefaultLibrarySettings();
        $this->service = app(DashboardService::class);
    }

    public function test_today_stats_counts_checkouts(): void {
        $borrower = $this->createStaffBorrower();
        $librarian = $this->createLibrarianUser();

        LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'checked_out_by' => $librarian->id,
            'checkout_date' => today(),
        ]);

        $stats = $this->service->todayStats();

        $this->assertEquals(1, $stats['checkouts']);
    }

    public function test_today_stats_counts_returns(): void {
        $borrower = $this->createStaffBorrower();

        LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'returned',
            'checkout_date' => now()->subDays(5),
            'return_date' => today(),
        ]);

        $stats = $this->service->todayStats();

        $this->assertEquals(1, $stats['returns']);
    }

    public function test_today_stats_counts_new_registrations(): void {
        $borrower = $this->createStaffBorrower();
        $librarian = $this->createLibrarianUser();

        // First-ever checkout for this borrower — today
        LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'checked_out_by' => $librarian->id,
            'checkout_date' => today(),
        ]);

        $stats = $this->service->todayStats();

        $this->assertEquals(1, $stats['newRegistrations']);
    }

    public function test_due_today_lists_loans(): void {
        $borrower = $this->createStaffBorrower();

        LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'checked_out',
            'due_date' => today(),
        ]);

        $dueToday = $this->service->dueToday();

        $this->assertCount(1, $dueToday);
    }

    public function test_collection_summary_counts(): void {
        $book = Book::factory()->create();
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'checked_out']);
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'lost']);

        $summary = $this->service->collectionSummary();

        $this->assertGreaterThanOrEqual(1, $summary['total_books']);
        $this->assertGreaterThanOrEqual(3, $summary['total_copies']);
        $this->assertGreaterThanOrEqual(1, $summary['available']);
        $this->assertGreaterThanOrEqual(1, $summary['checked_out']);
        $this->assertGreaterThanOrEqual(1, $summary['lost']);
    }

    public function test_overdue_by_bracket_groups(): void {
        $borrower = $this->createStaffBorrower();

        // Create an overdue transaction in the 1-7 bracket
        LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'overdue',
            'due_date' => now()->subDays(3),
        ]);

        $result = $this->service->overdueByBracket();

        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertEquals(1, $result['total']);
    }

    public function test_popular_books_ranked(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        $copy = Copy::factory()->create(['book_id' => $book->id]);

        LibraryTransaction::factory()->count(3)->create([
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'checkout_date' => today(),
        ]);

        $popular = $this->service->popularBooks(10);

        $this->assertGreaterThanOrEqual(1, $popular->count());
    }

    public function test_recent_activity_filtered(): void {
        $librarian = $this->createLibrarianUser();
        $this->actingAs($librarian);

        $copy = Copy::factory()->create();

        // Create audit log entries with valid and invalid actions
        LibraryAuditLog::log($copy, 'checkout', null, ['test' => true]);
        LibraryAuditLog::log($copy, 'some_unknown_action', null, ['test' => true]);

        $activity = $this->service->recentActivity(20);

        // Only ACTION_LABELS keys should be included
        foreach ($activity as $entry) {
            $this->assertArrayHasKey($entry->action, DashboardService::ACTION_LABELS);
        }
    }
}
