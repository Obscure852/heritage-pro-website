<?php

namespace App\Services\Library;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Library\InventoryItem;
use App\Models\Library\InventorySession;
use App\Models\Library\LibraryAuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService {
    protected CopyStatusService $copyStatusService;

    public function __construct(CopyStatusService $copyStatusService) {
        $this->copyStatusService = $copyStatusService;
    }

    /**
     * Start a new inventory session.
     *
     * @throws \RuntimeException If an active session already exists
     */
    public function startSession(string $scopeType, ?string $scopeValue, int $userId): InventorySession {
        return DB::transaction(function () use ($scopeType, $scopeValue, $userId) {
            // Check for active session (lockForUpdate to prevent race condition)
            $activeSession = InventorySession::lockForUpdate()
                ->where('status', 'in_progress')
                ->first();

            if ($activeSession) {
                throw new \RuntimeException('An inventory session is already in progress. Please complete or cancel it before starting a new one.');
            }

            // Count expected copies based on scope
            $expectedCount = $this->getExpectedCopiesQuery($scopeType, $scopeValue)->count();

            $session = InventorySession::create([
                'scope_type' => $scopeType,
                'scope_value' => $scopeValue,
                'status' => 'in_progress',
                'expected_count' => $expectedCount,
                'scanned_count' => 0,
                'discrepancy_count' => 0,
                'started_by' => $userId,
                'started_at' => now(),
            ]);

            LibraryAuditLog::log(
                $session,
                'inventory_started',
                null,
                [
                    'scope_type' => $scopeType,
                    'scope_value' => $scopeValue,
                    'expected_count' => $expectedCount,
                ]
            );

            return $session;
        });
    }

    /**
     * Scan a copy during an inventory session.
     *
     * @throws \RuntimeException If session is not in progress, copy not found, or duplicate scan
     */
    public function scanCopy(InventorySession $session, string $accessionNumber): array {
        return DB::transaction(function () use ($session, $accessionNumber) {
            // Reload with lock to prevent race conditions
            $session = InventorySession::lockForUpdate()->findOrFail($session->id);

            if ($session->status !== 'in_progress') {
                throw new \RuntimeException('This inventory session is no longer in progress.');
            }

            // Find the copy
            $copy = Copy::where('accession_number', $accessionNumber)->first();

            if (!$copy) {
                throw new \RuntimeException("No copy found with accession number '{$accessionNumber}'.");
            }

            // Check if copy is in scope
            if (!$this->isCopyInScope($session, $copy)) {
                throw new \RuntimeException("Copy '{$accessionNumber}' is not within the scope of this inventory session.");
            }

            // Check for duplicate scan
            $alreadyScanned = InventoryItem::where('inventory_session_id', $session->id)
                ->where('copy_id', $copy->id)
                ->exists();

            if ($alreadyScanned) {
                throw new \RuntimeException("Copy '{$accessionNumber}' has already been scanned in this session.");
            }

            // Create inventory item
            InventoryItem::create([
                'inventory_session_id' => $session->id,
                'copy_id' => $copy->id,
                'scanned_by' => auth()->id(),
                'scanned_at' => now(),
            ]);

            // Update scanned count
            $session->increment('scanned_count');

            return $this->formatCopyData($copy);
        });
    }

    /**
     * Build query for expected copies based on scope.
     */
    public function getExpectedCopiesQuery(string $scopeType, ?string $scopeValue) {
        $query = Copy::join('books', 'copies.book_id', '=', 'books.id')
            ->whereIn('copies.status', ['available', 'in_repair'])
            ->select('copies.*');

        if ($scopeType === 'location' && $scopeValue) {
            $query->where('books.location', $scopeValue);
        } elseif ($scopeType === 'genre' && $scopeValue) {
            $query->where('books.genre', $scopeValue);
        }
        // 'all' type — no additional filter

        return $query;
    }

    /**
     * Check if a copy is within the scope of a session.
     */
    public function isCopyInScope(InventorySession $session, Copy $copy): bool {
        if ($session->scope_type === 'all') {
            return true;
        }

        $book = $copy->book;

        if (!$book) {
            return false;
        }

        if ($session->scope_type === 'location') {
            return $book->location === $session->scope_value;
        }

        if ($session->scope_type === 'genre') {
            return $book->genre === $session->scope_value;
        }

        return false;
    }

    /**
     * Format copy data for scan response.
     */
    public function formatCopyData(Copy $copy): array {
        $book = $copy->book;

        return [
            'accession_number' => $copy->accession_number,
            'book_title' => $book ? $book->title : 'Unknown',
            'status' => $copy->status,
        ];
    }

    /**
     * Complete an inventory session: calculate discrepancies, update status.
     *
     * @throws \RuntimeException If session is not in progress
     */
    public function completeSession(InventorySession $session, int $userId): InventorySession {
        return DB::transaction(function () use ($session, $userId) {
            $session = InventorySession::lockForUpdate()->findOrFail($session->id);

            if ($session->status !== 'in_progress') {
                throw new \RuntimeException('This inventory session is not in progress.');
            }

            $discrepancies = $this->getDiscrepancies($session);

            $session->update([
                'status' => 'completed',
                'completed_by' => $userId,
                'completed_at' => now(),
                'discrepancy_count' => $discrepancies->count(),
            ]);

            LibraryAuditLog::log(
                $session,
                'inventory_completed',
                null,
                [
                    'scanned_count' => $session->scanned_count,
                    'expected_count' => $session->expected_count,
                    'discrepancy_count' => $discrepancies->count(),
                ]
            );

            return $session;
        });
    }

    /**
     * Cancel an inventory session.
     *
     * @throws \RuntimeException If session is not in progress
     */
    public function cancelSession(InventorySession $session, int $userId): InventorySession {
        return DB::transaction(function () use ($session, $userId) {
            $session = InventorySession::lockForUpdate()->findOrFail($session->id);

            if ($session->status !== 'in_progress') {
                throw new \RuntimeException('This inventory session is not in progress.');
            }

            $session->update([
                'status' => 'cancelled',
                'completed_by' => $userId,
                'completed_at' => now(),
            ]);

            LibraryAuditLog::log(
                $session,
                'inventory_cancelled',
                null,
                [
                    'scanned_count' => $session->scanned_count,
                    'expected_count' => $session->expected_count,
                ]
            );

            return $session;
        });
    }

    /**
     * Get copies that were expected but not scanned (discrepancies).
     */
    public function getDiscrepancies(InventorySession $session): Collection {
        $scannedCopyIds = InventoryItem::where('inventory_session_id', $session->id)
            ->pluck('copy_id');

        return $this->getExpectedCopiesQuery($session->scope_type, $session->scope_value)
            ->whereNotIn('copies.id', $scannedCopyIds)
            ->with('book')
            ->get();
    }

    /**
     * Mark copies as missing via CopyStatusService.
     *
     * @return array{success: int, errors: array}
     */
    public function markCopiesAsMissing(InventorySession $session, array $copyIds): array {
        $success = 0;
        $errors = [];

        foreach ($copyIds as $copyId) {
            $copy = Copy::find($copyId);

            if (!$copy) {
                $errors[] = "Copy #{$copyId} not found.";
                continue;
            }

            try {
                $this->copyStatusService->transition(
                    $copy,
                    CopyStatusService::STATUS_MISSING,
                    "Marked as missing during inventory session #{$session->id}"
                );
                $success++;
            } catch (\Exception $e) {
                $errors[] = "Copy '{$copy->accession_number}': {$e->getMessage()}";
            }
        }

        if ($success > 0) {
            LibraryAuditLog::log(
                $session,
                'inventory_mark_missing',
                null,
                [
                    'copies_marked' => $success,
                    'errors' => count($errors),
                ]
            );
        }

        return compact('success', 'errors');
    }
}
