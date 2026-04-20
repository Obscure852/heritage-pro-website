<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\RecordFinePaymentRequest;
use App\Http\Requests\Library\WaiveFineRequest;
use App\Models\Library\LibraryFine;
use App\Models\SchoolSetup;
use App\Services\Library\FineService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FineController extends Controller {
    protected FineService $fineService;

    public function __construct(FineService $fineService) {
        $this->middleware('auth');
        $this->fineService = $fineService;
    }

    /**
     * Display the fine management page with filters and summary stats.
     */
    public function index(Request $request): View {
        Gate::authorize('manage-library');

        $query = LibraryFine::with(['transaction.copy.book', 'borrower', 'waivedBy']);

        // Filter: status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: fine_type
        if ($request->filled('fine_type')) {
            $query->where('fine_type', $request->fine_type);
        }

        // Filter: date range
        if ($request->filled('date_from')) {
            $query->whereDate('fine_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('fine_date', '<=', $request->date_to);
        }

        // Filter: borrower search (name match across polymorphic types)
        if ($request->filled('borrower_search')) {
            $search = $request->borrower_search;
            $query->where(function ($q) use ($search) {
                // Search students by name or admission_number
                $q->whereHasMorph('borrower', ['*'], function ($sub) use ($search) {
                    $sub->where(function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%")
                              ->orWhere('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                    });
                });
            });
        }

        // Get fines ordered by date descending
        $fines = $query->orderBy('fine_date', 'desc')
                       ->orderBy('created_at', 'desc')
                       ->get();

        // Summary stats (computed from all fines, not filtered)
        $allFines = LibraryFine::select(
            DB::raw('SUM(amount) as total_fines'),
            DB::raw('SUM(amount_paid) as total_paid'),
            DB::raw('SUM(amount_waived) as total_waived'),
            DB::raw('SUM(amount - amount_paid - amount_waived) as total_outstanding')
        )->first();

        $totalFines = $allFines->total_fines ?? '0.00';
        $totalPaid = $allFines->total_paid ?? '0.00';
        $totalWaived = $allFines->total_waived ?? '0.00';
        $totalOutstanding = $allFines->total_outstanding ?? '0.00';

        return view('library.fines.index', compact(
            'fines',
            'totalFines',
            'totalPaid',
            'totalWaived',
            'totalOutstanding'
        ));
    }

    /**
     * Record a partial or full payment on a fine.
     */
    public function recordPayment(RecordFinePaymentRequest $request, LibraryFine $fine): JsonResponse {
        try {
            $updatedFine = $this->fineService->recordPayment(
                $fine,
                (float) $request->validated()['amount'],
                auth()->id(),
                $request->validated()['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'data' => [
                    'fine_id' => $updatedFine->id,
                    'amount_paid' => number_format($updatedFine->amount_paid, 2),
                    'outstanding' => number_format($updatedFine->outstanding, 2),
                    'status' => $updatedFine->status,
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
     * Generate and stream a PDF receipt for a fine.
     */
    public function printReceipt(LibraryFine $fine): Response {
        Gate::authorize('manage-library');

        $fine->load(['transaction.copy.book', 'borrower', 'waivedBy']);
        $school = SchoolSetup::first();

        $pdf = Pdf::loadView('library.fines.receipt-pdf', [
            'fine' => $fine,
            'school' => $school,
            'generatedBy' => auth()->user()->name ?? 'Librarian',
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("fine-receipt-LF-{$fine->id}.pdf");
    }

    /**
     * Waive all or part of a fine (administrator only).
     */
    public function waive(WaiveFineRequest $request, LibraryFine $fine): JsonResponse {
        try {
            $updatedFine = $this->fineService->waiveFine(
                $fine,
                (float) $request->validated()['amount'],
                auth()->id(),
                $request->validated()['reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Fine waiver applied successfully.',
                'data' => [
                    'fine_id' => $updatedFine->id,
                    'amount_waived' => number_format($updatedFine->amount_waived, 2),
                    'outstanding' => number_format($updatedFine->outstanding, 2),
                    'status' => $updatedFine->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
