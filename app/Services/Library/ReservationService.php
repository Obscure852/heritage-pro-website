<?php

namespace App\Services\Library;

use App\Models\Copy;
use App\Models\Library\LibraryAuditLog;
use App\Models\Library\LibraryReservation;
use App\Models\Library\LibrarySetting;
use App\Notifications\Library\HoldExpiredNotification;
use App\Notifications\Library\HoldReadyNotification;
use Illuminate\Support\Facades\DB;

class ReservationService {
    // ==================== PLACE RESERVATION ====================

    /**
     * Place a FIFO-ordered reservation on a book.
     *
     * Validates that no copies are available, no duplicate exists,
     * borrower is eligible, and max reservation limit is not exceeded.
     *
     * @param int $bookId The book to reserve
     * @param string $borrowerType Morph type: 'student' or 'user'
     * @param int $borrowerId The borrower's ID
     * @return LibraryReservation
     *
     * @throws \RuntimeException If reservation cannot be placed
     */
    public function placeReservation(int $bookId, string $borrowerType, int $borrowerId): LibraryReservation {
        return DB::transaction(function () use ($bookId, $borrowerType, $borrowerId) {
            // Check ALL copies of book -- if any available, must checkout directly
            $availableCopy = Copy::where('book_id', $bookId)
                ->where('status', CopyStatusService::STATUS_AVAILABLE)
                ->first();

            if ($availableCopy) {
                throw new \RuntimeException('Cannot reserve -- available copies exist. Please check out directly.');
            }

            // Check no duplicate active reservation for same borrower + book
            $duplicateExists = LibraryReservation::where('book_id', $bookId)
                ->where('borrower_type', $borrowerType)
                ->where('borrower_id', $borrowerId)
                ->active()
                ->exists();

            if ($duplicateExists) {
                throw new \RuntimeException('You already have an active reservation for this book.');
            }

            // Check borrower eligibility (same block conditions as checkout)
            $blockReasons = app(CirculationService::class)->getBlockReasons($borrowerType, $borrowerId);
            if (!empty($blockReasons)) {
                throw new \RuntimeException('Cannot reserve: ' . implode(' ', $blockReasons));
            }

            // Check max reservations per borrower
            $maxSettings = LibrarySetting::get('max_reservations_per_borrower', ['student' => 2, 'staff' => 3]);
            $maxAllowed = (int) ($maxSettings[$this->settingsKey($borrowerType)] ?? 2);
            $activeCount = LibraryReservation::where('borrower_type', $borrowerType)
                ->where('borrower_id', $borrowerId)
                ->active()
                ->count();

            if ($activeCount >= $maxAllowed) {
                throw new \RuntimeException(
                    "Maximum reservation limit reached ({$activeCount}/{$maxAllowed})."
                );
            }

            // Calculate next queue position (FIFO)
            $nextPosition = (LibraryReservation::where('book_id', $bookId)
                ->active()
                ->lockForUpdate()
                ->max('queue_position') ?? 0) + 1;

            // Create reservation
            $reservation = LibraryReservation::create([
                'book_id' => $bookId,
                'borrower_type' => $borrowerType,
                'borrower_id' => $borrowerId,
                'status' => 'pending',
                'queue_position' => $nextPosition,
            ]);

            // Audit log
            LibraryAuditLog::log($reservation, 'reservation_placed', null, [
                'book_id' => $bookId,
                'queue_position' => $nextPosition,
            ]);

            return $reservation;
        });
    }

    // ==================== CANCEL RESERVATION ====================

    /**
     * Cancel a reservation and re-normalize queue positions.
     *
     * If the reservation was 'ready' (copy on hold), handles releasing
     * the copy to the next in queue or back to available.
     *
     * @param LibraryReservation $reservation The reservation to cancel
     * @param string|null $reason Optional cancellation reason
     *
     * @throws \RuntimeException If reservation is not in an active state
     */
    public function cancelReservation(LibraryReservation $reservation, ?string $reason = null): void {
        DB::transaction(function () use ($reservation, $reason) {
            // Re-fetch with lock to prevent race conditions
            $reservation = LibraryReservation::lockForUpdate()->findOrFail($reservation->id);

            // Validate reservation is active
            if (!in_array($reservation->status, ['pending', 'ready'])) {
                throw new \RuntimeException('Cannot cancel: reservation is not active (status: ' . $reservation->status . ').');
            }

            $oldStatus = $reservation->status;

            // If status was 'ready', handle the on_hold copy
            if ($oldStatus === 'ready') {
                $copy = Copy::where('book_id', $reservation->book_id)
                    ->where('status', CopyStatusService::STATUS_ON_HOLD)
                    ->first();

                if ($copy) {
                    // Check if there's another pending reservation for this book
                    $nextPending = LibraryReservation::where('book_id', $reservation->book_id)
                        ->where('status', 'pending')
                        ->where('id', '!=', $reservation->id)
                        ->orderBy('queue_position')
                        ->first();

                    if ($nextPending) {
                        $this->fulfillNextInQueue($reservation->book_id, $copy);
                    } else {
                        app(CopyStatusService::class)->transition(
                            $copy,
                            CopyStatusService::STATUS_AVAILABLE,
                            'Hold cancelled -- no more reservations in queue'
                        );
                    }
                }
            }

            // Update reservation
            $reservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'notes' => $reason
                    ? (($reservation->notes ? $reservation->notes . ' | ' : '') . 'Cancelled: ' . $reason)
                    : $reservation->notes,
            ]);

            // Re-normalize queue positions for remaining active reservations
            LibraryReservation::where('book_id', $reservation->book_id)
                ->active()
                ->where('queue_position', '>', $reservation->queue_position)
                ->decrement('queue_position');

            // Audit log
            LibraryAuditLog::log($reservation, 'reservation_cancelled', [
                'status' => $oldStatus,
            ], [
                'status' => 'cancelled',
                'reason' => $reason,
            ]);
        });
    }

    // ==================== FULFILL NEXT IN QUEUE ====================

    /**
     * Fulfill the next pending reservation when a book is returned.
     *
     * Transitions the copy to on_hold, updates the reservation to 'ready',
     * and sends a notification to the borrower.
     *
     * @param int $bookId The book being returned
     * @param Copy $copy The copy being returned
     * @return LibraryReservation|null The fulfilled reservation, or null if no queue
     */
    public function fulfillNextInQueue(int $bookId, Copy $copy): ?LibraryReservation {
        return DB::transaction(function () use ($bookId, $copy) {
            // Get the next pending reservation (FIFO order)
            $reservation = LibraryReservation::where('book_id', $bookId)
                ->where('status', 'pending')
                ->orderBy('queue_position')
                ->lockForUpdate()
                ->first();

            if (!$reservation) {
                return null;
            }

            // Transition copy to on_hold
            app(CopyStatusService::class)->transition(
                $copy,
                CopyStatusService::STATUS_ON_HOLD,
                'Reserved book returned -- holding for next borrower'
            );

            // Get pickup window from settings
            $pickupDays = LibrarySetting::get('reservation_pickup_window', ['days' => 3])['days'];

            // Update reservation to ready
            $reservation->update([
                'status' => 'ready',
                'notified_at' => now(),
                'expires_at' => now()->addDays($pickupDays),
            ]);

            // Send notification to borrower (null-check)
            if ($reservation->borrower) {
                $reservation->borrower->notify(new HoldReadyNotification($reservation));
            }

            // Audit log
            LibraryAuditLog::log($reservation, 'reservation_fulfilled', null, [
                'copy_id' => $copy->id,
                'expires_at' => $reservation->expires_at->toDateTimeString(),
            ], 'Hold ready -- borrower notified');

            return $reservation;
        });
    }

    // ==================== EXPIRE HOLD ====================

    /**
     * Expire an uncollected hold and release to next in queue or available.
     *
     * @param LibraryReservation $reservation The 'ready' reservation to expire
     */
    public function expireHold(LibraryReservation $reservation): void {
        DB::transaction(function () use ($reservation) {
            // Re-fetch with lock
            $reservation = LibraryReservation::lockForUpdate()->findOrFail($reservation->id);

            // Update reservation to expired
            $reservation->update([
                'status' => 'expired',
                'cancelled_at' => now(),
            ]);

            // Send expiry notification to borrower (null-check)
            if ($reservation->borrower) {
                $reservation->borrower->notify(new HoldExpiredNotification($reservation));
            }

            // Find the on_hold copy
            $copy = Copy::where('book_id', $reservation->book_id)
                ->where('status', CopyStatusService::STATUS_ON_HOLD)
                ->first();

            if ($copy) {
                // Try to fulfill next in queue
                $nextFulfilled = $this->fulfillNextInQueue($reservation->book_id, $copy);

                // If no next reservation, release copy to available
                if (!$nextFulfilled) {
                    app(CopyStatusService::class)->transition(
                        $copy,
                        CopyStatusService::STATUS_AVAILABLE,
                        'Hold expired -- no more reservations in queue'
                    );
                }
            }

            // Audit log
            LibraryAuditLog::log($reservation, 'reservation_expired', null, [
                'status' => 'expired',
            ], 'Hold expired -- borrower did not collect within pickup window');
        });
    }

    // ==================== SETTINGS HELPERS ====================

    /**
     * Map morph borrower_type to settings key.
     *
     * CRITICAL: Morph map uses 'student' and 'user' but settings use 'student' and 'staff'.
     * 'student' -> 'student', anything else (including 'user') -> 'staff'
     */
    protected function settingsKey(string $borrowerType): string {
        return $borrowerType === 'student' ? 'student' : 'staff';
    }
}
