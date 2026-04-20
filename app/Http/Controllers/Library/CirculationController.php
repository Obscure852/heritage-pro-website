<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\BulkCheckinRequest;
use App\Http\Requests\Library\BulkCheckoutRequest;
use App\Http\Requests\Library\CheckinRequest;
use App\Http\Requests\Library\CheckoutRequest;
use App\Http\Requests\Library\RenewalRequest;
use App\Models\Copy;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use App\Services\Library\CirculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CirculationController extends Controller {
    protected CirculationService $circulationService;

    public function __construct(CirculationService $circulationService) {
        $this->middleware('auth');
        $this->circulationService = $circulationService;
    }

    /**
     * Display the circulation desk page.
     */
    public function index(): View {
        return view('library.circulation.index');
    }

    /**
     * Check out a copy to a borrower.
     */
    public function checkout(CheckoutRequest $request): JsonResponse {
        try {
            $copy = Copy::where('accession_number', $request->accession_number)->firstOrFail();

            $transaction = $this->circulationService->checkout(
                $copy,
                $request->borrower_type,
                $request->borrower_id,
                auth()->id(),
                $request->notes
            );

            $copy->load('book');

            return response()->json([
                'success' => true,
                'message' => 'Book checked out successfully.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'book_title' => $copy->book->title ?? 'Unknown',
                    'accession_number' => $copy->accession_number,
                    'due_date' => $transaction->due_date->format('d M Y'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check in (return) a copy.
     */
    public function checkin(CheckinRequest $request): JsonResponse {
        try {
            $copy = Copy::where('accession_number', $request->accession_number)->firstOrFail();

            $transaction = $this->circulationService->checkin(
                $copy,
                auth()->id(),
                $request->notes
            );

            $transaction->load(['borrower', 'copy.book']);

            $borrowerName = 'Unknown';
            if ($transaction->borrower) {
                $borrowerName = $transaction->borrower->name
                    ?? ($transaction->borrower->first_name . ' ' . $transaction->borrower->last_name);
            }

            return response()->json([
                'success' => true,
                'message' => 'Book returned successfully.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'book_title' => $transaction->copy->book->title ?? 'Unknown',
                    'accession_number' => $copy->accession_number,
                    'borrower_name' => $borrowerName,
                    'return_date' => $transaction->return_date->format('d M Y'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Renew a checked-out transaction.
     */
    public function renew(RenewalRequest $request, LibraryTransaction $transaction): JsonResponse {
        try {
            $transaction = $this->circulationService->renew(
                $transaction,
                auth()->id(),
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Book renewed successfully.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'due_date' => $transaction->due_date->format('d M Y'),
                    'renewal_count' => $transaction->renewal_count,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Bulk checkout multiple copies to one borrower.
     */
    public function bulkCheckout(BulkCheckoutRequest $request): JsonResponse {
        try {
            // Resolve copy IDs from accession numbers
            $copyIds = Copy::whereIn('accession_number', $request->accession_numbers)
                ->pluck('id')
                ->toArray();

            $results = $this->circulationService->bulkCheckout(
                $copyIds,
                $request->borrower_type,
                $request->borrower_id,
                auth()->id(),
                $request->notes
            );

            $successCount = count($results['success']);
            $errorCount = count($results['errors']);

            return response()->json([
                'success' => $errorCount === 0,
                'message' => "{$successCount} book(s) checked out" . ($errorCount > 0 ? ", {$errorCount} failed." : ' successfully.'),
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Bulk checkin multiple copies.
     */
    public function bulkCheckin(BulkCheckinRequest $request): JsonResponse {
        try {
            $results = $this->circulationService->bulkCheckin(
                $request->accession_numbers,
                auth()->id()
            );

            $successCount = count($results['success']);
            $errorCount = count($results['errors']);

            return response()->json([
                'success' => $errorCount === 0,
                'message' => "{$successCount} book(s) returned" . ($errorCount > 0 ? ", {$errorCount} failed." : ' successfully.'),
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Look up a copy by accession number.
     *
     * Returns copy details and active transaction info (if any).
     */
    public function lookupCopy(Request $request): JsonResponse {
        $request->validate([
            'accession_number' => ['required', 'string'],
        ]);

        $copy = Copy::with(['book.author', 'book.authors'])
            ->where('accession_number', $request->accession_number)
            ->first();

        if (!$copy) {
            return response()->json([
                'success' => false,
                'message' => 'No copy found with this accession number.',
            ], 404);
        }

        // Check for active transaction
        $activeTransaction = LibraryTransaction::where('copy_id', $copy->id)
            ->active()
            ->with('borrower')
            ->first();

        $transactionData = null;
        if ($activeTransaction) {
            $borrowerName = 'Unknown';
            if ($activeTransaction->borrower) {
                $borrowerName = $activeTransaction->borrower->name
                    ?? ($activeTransaction->borrower->first_name . ' ' . $activeTransaction->borrower->last_name);
            }

            $transactionData = [
                'id' => $activeTransaction->id,
                'borrower_name' => $borrowerName,
                'borrower_type' => $activeTransaction->borrower_type,
                'checkout_date' => $activeTransaction->checkout_date->format('d M Y'),
                'due_date' => $activeTransaction->due_date->format('d M Y'),
                'is_overdue' => $activeTransaction->due_date->isPast(),
                'renewal_count' => $activeTransaction->renewal_count,
            ];
        }

        // Build authors string
        $authors = '';
        if ($copy->book) {
            if ($copy->book->authors && $copy->book->authors->isNotEmpty()) {
                $authors = $copy->book->authors->pluck('full_name')->join(', ');
            } elseif ($copy->book->author) {
                $authors = $copy->book->author->full_name ?? '';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'copy' => [
                    'id' => $copy->id,
                    'accession_number' => $copy->accession_number,
                    'status' => $copy->status,
                    'book_title' => $copy->book->title ?? 'Unknown',
                    'book_authors' => $authors,
                    'book_isbn' => $copy->book->isbn ?? '',
                ],
                'active_transaction' => $transactionData,
            ],
        ]);
    }

    /**
     * Get borrower status for circulation.
     *
     * Returns block reasons, current loans, and borrowing capacity.
     */
    public function borrowerStatus(Request $request): JsonResponse {
        $request->validate([
            'borrower_type' => ['required', 'string', 'in:student,user'],
            'borrower_id' => ['required', 'integer', 'min:1'],
        ]);

        $borrowerType = $request->borrower_type;
        $borrowerId = $request->borrower_id;

        // Get current active loans with book info
        $currentLoans = LibraryTransaction::forBorrower($borrowerType, $borrowerId)
            ->active()
            ->with('copy.book')
            ->get();

        // Get block reasons from service
        $blockReasons = $this->circulationService->getBlockReasons($borrowerType, $borrowerId);

        // Get max books setting
        $settingsKey = $borrowerType === 'student' ? 'student' : 'staff';
        $maxBooks = LibrarySetting::get('max_books', ['student' => 3, 'staff' => 5])[$settingsKey] ?? 3;

        return response()->json([
            'success' => true,
            'data' => [
                'can_borrow' => empty($blockReasons),
                'block_reasons' => $blockReasons,
                'current_loans_count' => $currentLoans->count(),
                'max_books' => (int) $maxBooks,
                'current_loans' => $currentLoans->map(function ($loan) {
                    return [
                        'id' => $loan->id,
                        'book_title' => $loan->copy->book->title ?? 'Unknown',
                        'accession_number' => $loan->copy->accession_number ?? '',
                        'due_date' => $loan->due_date->format('d M Y'),
                        'is_overdue' => $loan->due_date->isPast(),
                        'renewal_count' => $loan->renewal_count,
                    ];
                })->toArray(),
            ],
        ]);
    }
}
