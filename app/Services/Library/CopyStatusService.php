<?php

namespace App\Services\Library;

use App\Models\Copy;
use App\Models\Library\LibraryAuditLog;
use Illuminate\Support\Facades\DB;

class CopyStatusService {
    // All valid status values for the copies table
    const STATUS_AVAILABLE = 'available';
    const STATUS_CHECKED_OUT = 'checked_out';
    const STATUS_IN_REPAIR = 'in_repair';
    const STATUS_LOST = 'lost';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_MISSING = 'missing';

    // Allowed transitions map
    const TRANSITIONS = [
        'available' => ['checked_out', 'in_repair', 'lost', 'on_hold', 'missing'],
        'checked_out' => ['available', 'lost'],
        'in_repair' => ['available', 'lost', 'missing'],
        'lost' => ['available'],
        'on_hold' => ['checked_out', 'available'],
        'missing' => ['available'],
    ];

    /**
     * Transition a copy to a new status with validation.
     * Uses lockForUpdate() to prevent race conditions.
     * Wraps in a DB transaction.
     *
     * @throws \InvalidArgumentException if transition is not allowed
     * @throws \RuntimeException if copy has active textbook allocation
     */
    public function transition(Copy $copy, string $newStatus, ?string $reason = null): Copy {
        return DB::transaction(function () use ($copy, $newStatus, $reason) {
            // Re-fetch with lock to prevent race conditions
            $copy = Copy::lockForUpdate()->findOrFail($copy->id);
            $oldStatus = $copy->status;

            // Validate the transition is allowed
            if (!$this->isValidTransition($oldStatus, $newStatus)) {
                throw new \InvalidArgumentException(
                    "Invalid status transition from '{$oldStatus}' to '{$newStatus}' for copy #{$copy->id}."
                );
            }

            // If transitioning to checked_out, verify no active textbook allocation
            if ($newStatus === self::STATUS_CHECKED_OUT) {
                if (!$this->canLibraryCheckout($copy)) {
                    throw new \RuntimeException(
                        "Copy #{$copy->id} cannot be checked out: has active textbook allocation or is not available."
                    );
                }
            }

            // Perform the status update
            $copy->update(['status' => $newStatus]);

            // Log the transition
            LibraryAuditLog::log(
                $copy,
                'status_change',
                ['status' => $oldStatus],
                ['status' => $newStatus],
                $reason
            );

            return $copy;
        });
    }

    /**
     * Check if a copy can be borrowed by the library module.
     * Must have no active textbook allocation AND status = 'available'.
     */
    public function canLibraryCheckout(Copy $copy): bool {
        return $copy->status === self::STATUS_AVAILABLE
            && $copy->currentAllocation === null;
    }

    /**
     * Check if a status transition is valid.
     */
    public function isValidTransition(string $from, string $to): bool {
        if (!isset(self::TRANSITIONS[$from])) {
            return false;
        }

        return in_array($to, self::TRANSITIONS[$from]);
    }

    /**
     * Get all allowed transitions from the current status.
     */
    public function getAllowedTransitions(string $currentStatus): array {
        return self::TRANSITIONS[$currentStatus] ?? [];
    }
}
