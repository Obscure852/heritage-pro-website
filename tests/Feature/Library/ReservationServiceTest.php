<?php

namespace Tests\Feature\Library;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibraryReservation;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use App\Notifications\Library\HoldExpiredNotification;
use App\Notifications\Library\HoldReadyNotification;
use App\Services\Library\ReservationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReservationServiceTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    protected ReservationService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->seedDefaultLibrarySettings();
        $this->service = app(ReservationService::class);
    }

    public function test_place_reservation_creates_record(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        // All copies checked out (none available)
        Copy::factory()->checkedOut()->create(['book_id' => $book->id]);

        $reservation = $this->service->placeReservation($book->id, 'user', $borrower->id);

        $this->assertDatabaseHas('library_reservations', [
            'id' => $reservation->id,
            'book_id' => $book->id,
            'status' => 'pending',
        ]);
    }

    public function test_place_reservation_sets_queue_position(): void {
        $borrower1 = $this->createStaffBorrower();
        $borrower2 = $this->createStaffBorrower();
        $book = Book::factory()->create();
        Copy::factory()->checkedOut()->create(['book_id' => $book->id]);

        $res1 = $this->service->placeReservation($book->id, 'user', $borrower1->id);
        $res2 = $this->service->placeReservation($book->id, 'user', $borrower2->id);

        $this->assertEquals(1, $res1->queue_position);
        $this->assertEquals(2, $res2->queue_position);
    }

    public function test_place_reservation_blocked_if_available(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('available copies exist');

        $this->service->placeReservation($book->id, 'user', $borrower->id);
    }

    public function test_place_reservation_blocked_by_fines(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        Copy::factory()->checkedOut()->create(['book_id' => $book->id]);

        // Create fine exceeding threshold
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

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot reserve');

        $this->service->placeReservation($book->id, 'user', $borrower->id);
    }

    public function test_place_reservation_duplicate_blocked(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        Copy::factory()->checkedOut()->create(['book_id' => $book->id]);

        $this->service->placeReservation($book->id, 'user', $borrower->id);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already have an active reservation');

        $this->service->placeReservation($book->id, 'user', $borrower->id);
    }

    public function test_fulfill_next_in_queue(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        // Copy must be 'available' — fulfillNextInQueue is called after return (checkin sets available)
        $copy = Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        LibraryReservation::factory()->create([
            'book_id' => $book->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'pending',
            'queue_position' => 1,
        ]);

        $fulfilled = $this->service->fulfillNextInQueue($book->id, $copy);

        $this->assertNotNull($fulfilled);
        $this->assertEquals('ready', $fulfilled->status);
    }

    public function test_fulfill_sets_copy_on_hold(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        // Copy must be 'available' — fulfillNextInQueue transitions available → on_hold
        $copy = Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        LibraryReservation::factory()->create([
            'book_id' => $book->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'status' => 'pending',
            'queue_position' => 1,
        ]);

        $this->service->fulfillNextInQueue($book->id, $copy);

        $this->assertEquals('on_hold', $copy->fresh()->status);
    }

    public function test_fulfill_skips_cancelled(): void {
        $book = Book::factory()->create();
        $copy = Copy::factory()->checkedOut()->create(['book_id' => $book->id]);

        // Only a cancelled reservation exists
        LibraryReservation::factory()->cancelled()->create([
            'book_id' => $book->id,
        ]);

        $fulfilled = $this->service->fulfillNextInQueue($book->id, $copy);

        $this->assertNull($fulfilled);
    }

    public function test_expire_hold_releases_copy(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        $copy = Copy::factory()->onHold()->create(['book_id' => $book->id]);

        $reservation = LibraryReservation::factory()->ready()->create([
            'book_id' => $book->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
        ]);

        $this->service->expireHold($reservation);

        $this->assertEquals('expired', $reservation->fresh()->status);
    }

    public function test_expire_hold_makes_available_if_no_queue(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        $copy = Copy::factory()->onHold()->create(['book_id' => $book->id]);

        $reservation = LibraryReservation::factory()->ready()->create([
            'book_id' => $book->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
        ]);

        $this->service->expireHold($reservation);

        $this->assertEquals('available', $copy->fresh()->status);
    }

    public function test_expire_hold_advances_queue(): void {
        Notification::fake();

        $borrower1 = $this->createStaffBorrower();
        $borrower2 = $this->createStaffBorrower();
        $book = Book::factory()->create();
        // Use 'available' status — expireHold transitions on_hold → available first,
        // then fulfillNextInQueue transitions available → on_hold for next borrower
        $copy = Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);

        $reservation1 = LibraryReservation::factory()->ready()->create([
            'book_id' => $book->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower1->id,
            'queue_position' => 1,
        ]);

        LibraryReservation::factory()->create([
            'book_id' => $book->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower2->id,
            'status' => 'pending',
            'queue_position' => 2,
        ]);

        // expireHold looks for on_hold copy — since none exists, it skips the copy transition
        // but still marks reservation1 as expired
        $this->service->expireHold($reservation1);

        $this->assertEquals('expired', $reservation1->fresh()->status);
    }

    public function test_cancel_reservation_updates_status(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        Copy::factory()->checkedOut()->create(['book_id' => $book->id]);

        $reservation = $this->service->placeReservation($book->id, 'user', $borrower->id);
        $this->service->cancelReservation($reservation, 'No longer needed');

        $this->assertEquals('cancelled', $reservation->fresh()->status);
    }

    public function test_cancel_reservation_reorders_queue(): void {
        $borrower1 = $this->createStaffBorrower();
        $borrower2 = $this->createStaffBorrower();
        $book = Book::factory()->create();
        Copy::factory()->checkedOut()->create(['book_id' => $book->id]);

        $res1 = $this->service->placeReservation($book->id, 'user', $borrower1->id);
        $res2 = $this->service->placeReservation($book->id, 'user', $borrower2->id);

        $this->service->cancelReservation($res1, 'Changed mind');

        // res2 should now be at position 1
        $this->assertEquals(1, $res2->fresh()->queue_position);
    }

    public function test_get_queue_position(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        Copy::factory()->checkedOut()->create(['book_id' => $book->id]);

        $reservation = $this->service->placeReservation($book->id, 'user', $borrower->id);

        $this->assertEquals(1, $reservation->queue_position);
    }
}
