<?php

namespace Tests\Feature\Library;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Library\InventoryItem;
use App\Models\Library\InventorySession;
use App\Services\Library\InventoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventoryServiceTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    protected InventoryService $service;
    protected $librarian;

    protected function setUp(): void {
        parent::setUp();
        $this->seedDefaultLibrarySettings();
        $this->service = app(InventoryService::class);
        $this->librarian = $this->createLibrarianUser();

        // Cancel any stale in_progress sessions from prior test runs or seeds
        InventorySession::where('status', 'in_progress')
            ->update(['status' => 'cancelled', 'completed_at' => now()]);
    }

    public function test_start_session_creates_record(): void {
        $session = $this->service->startSession('all', null, $this->librarian->id);

        $this->assertDatabaseHas('inventory_sessions', [
            'id' => $session->id,
            'scope_type' => 'all',
            'status' => 'in_progress',
        ]);
    }

    public function test_start_session_scope_all(): void {
        // Create some copies with expected statuses
        $book = Book::factory()->create();
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'in_repair']);
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'checked_out']); // not counted

        $session = $this->service->startSession('all', null, $this->librarian->id);

        // Expected count should include only available + in_repair
        $this->assertGreaterThanOrEqual(2, $session->expected_count);
    }

    public function test_start_session_scope_genre(): void {
        $book = Book::factory()->create(['genre' => 'TestGenre']);
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        $otherBook = Book::factory()->create(['genre' => 'OtherGenre']);
        Copy::factory()->create(['book_id' => $otherBook->id, 'status' => 'available']);

        $session = $this->service->startSession('genre', 'TestGenre', $this->librarian->id);

        $this->assertEquals(1, $session->expected_count);
    }

    public function test_start_session_scope_location(): void {
        $book = Book::factory()->create(['location' => 'TestLocation']);
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        $session = $this->service->startSession('location', 'TestLocation', $this->librarian->id);

        $this->assertGreaterThanOrEqual(1, $session->expected_count);
    }

    public function test_start_session_prevents_concurrent(): void {
        $this->service->startSession('all', null, $this->librarian->id);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already in progress');

        $this->service->startSession('all', null, $this->librarian->id);
    }

    public function test_scan_item_records_found(): void {
        $book = Book::factory()->create();
        $copy = Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        $session = $this->service->startSession('all', null, $this->librarian->id);

        $this->actingAs($this->librarian);
        $result = $this->service->scanCopy($session, $copy->accession_number);

        $this->assertDatabaseHas('inventory_items', [
            'inventory_session_id' => $session->id,
            'copy_id' => $copy->id,
        ]);
    }

    public function test_scan_item_duplicate_prevented(): void {
        $book = Book::factory()->create();
        $copy = Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        $session = $this->service->startSession('all', null, $this->librarian->id);

        $this->actingAs($this->librarian);
        $this->service->scanCopy($session, $copy->accession_number);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already been scanned');

        $this->service->scanCopy($session, $copy->accession_number);
    }

    public function test_scan_item_wrong_session_rejected(): void {
        $book = Book::factory()->create(['genre' => 'ScopeA']);
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        $otherBook = Book::factory()->create(['genre' => 'ScopeB']);
        $otherCopy = Copy::factory()->create(['book_id' => $otherBook->id, 'status' => 'available']);

        $session = $this->service->startSession('genre', 'ScopeA', $this->librarian->id);

        $this->actingAs($this->librarian);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not within the scope');

        $this->service->scanCopy($session, $otherCopy->accession_number);
    }

    public function test_complete_session_calculates_discrepancies(): void {
        $book = Book::factory()->create(['genre' => 'CompleteTest']);
        $copy1 = Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);
        $copy2 = Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        $session = $this->service->startSession('genre', 'CompleteTest', $this->librarian->id);

        // Only scan one of two expected copies
        $this->actingAs($this->librarian);
        $this->service->scanCopy($session, $copy1->accession_number);

        $completed = $this->service->completeSession($session, $this->librarian->id);

        $this->assertEquals(1, $completed->discrepancy_count);
    }

    public function test_complete_session_updates_status(): void {
        $session = $this->service->startSession('all', null, $this->librarian->id);

        $completed = $this->service->completeSession($session, $this->librarian->id);

        $this->assertEquals('completed', $completed->status);
    }

    public function test_cancel_session_updates_status(): void {
        $session = $this->service->startSession('all', null, $this->librarian->id);

        $cancelled = $this->service->cancelSession($session, $this->librarian->id);

        $this->assertEquals('cancelled', $cancelled->status);
    }

    public function test_get_discrepancies_returns_unscanned(): void {
        $book = Book::factory()->create(['genre' => 'DiscTest']);
        $copy1 = Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);
        $copy2 = Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        $session = $this->service->startSession('genre', 'DiscTest', $this->librarian->id);

        // Scan only copy1
        $this->actingAs($this->librarian);
        $this->service->scanCopy($session, $copy1->accession_number);

        $discrepancies = $this->service->getDiscrepancies($session);

        $this->assertCount(1, $discrepancies);
        $this->assertEquals($copy2->id, $discrepancies->first()->id);
    }

    public function test_mark_copies_missing_updates_status(): void {
        $copy = $this->createAvailableCopy();
        $session = InventorySession::create([
            'scope_type' => 'all',
            'status' => 'completed',
            'expected_count' => 1,
            'scanned_count' => 0,
            'discrepancy_count' => 1,
            'started_by' => $this->librarian->id,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $result = $this->service->markCopiesAsMissing($session, [$copy->id]);

        $this->assertEquals(1, $result['success']);
        $this->assertEquals('missing', $copy->fresh()->status);
    }

    public function test_mark_copies_missing_skips_errors(): void {
        // Copy that can't transition to missing (e.g., checked_out -> missing is invalid)
        $copy = Copy::factory()->checkedOut()->create();
        $session = InventorySession::create([
            'scope_type' => 'all',
            'status' => 'completed',
            'expected_count' => 1,
            'scanned_count' => 0,
            'discrepancy_count' => 1,
            'started_by' => $this->librarian->id,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        $result = $this->service->markCopiesAsMissing($session, [$copy->id]);

        $this->assertEquals(0, $result['success']);
        $this->assertCount(1, $result['errors']);
    }
}
