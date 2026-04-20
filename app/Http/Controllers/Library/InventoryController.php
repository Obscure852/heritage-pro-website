<?php

namespace App\Http\Controllers\Library;

use App\Exports\Library\InventoryDiscrepancyExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Library\StartInventoryRequest;
use App\Models\Book;
use App\Models\Library\InventorySession;
use App\Services\Library\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller {
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService) {
        $this->middleware('auth');
        $this->inventoryService = $inventoryService;
    }

    // ==================== INDEX ====================

    public function index(): View {
        Gate::authorize('manage-library');

        $activeSession = InventorySession::where('status', 'in_progress')->first();

        $sessions = InventorySession::with('startedByUser')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('library.inventory.index', compact('activeSession', 'sessions'));
    }

    // ==================== CREATE ====================

    public function create(): View {
        Gate::authorize('manage-library');

        // Redirect if active session exists
        $activeSession = InventorySession::where('status', 'in_progress')->first();

        if ($activeSession) {
            return redirect()->route('library.inventory.show', $activeSession)
                ->with('warning', 'An inventory session is already in progress.');
        }

        $locations = Book::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $genres = Book::whereNotNull('genre')
            ->where('genre', '!=', '')
            ->distinct()
            ->orderBy('genre')
            ->pluck('genre');

        return view('library.inventory.create', compact('locations', 'genres'));
    }

    // ==================== STORE ====================

    public function store(StartInventoryRequest $request) {
        try {
            $session = $this->inventoryService->startSession(
                $request->validated()['scope_type'],
                $request->validated()['scope_value'] ?? null,
                auth()->id()
            );

            return redirect()->route('library.inventory.show', $session)
                ->with('success', 'Inventory session started successfully.');
        } catch (\RuntimeException $e) {
            return redirect()->route('library.inventory.index')
                ->with('error', $e->getMessage());
        }
    }

    // ==================== SHOW (SCANNING PAGE) ====================

    public function show(InventorySession $session): View {
        Gate::authorize('manage-library');

        // Redirect completed/cancelled sessions to report
        if (in_array($session->status, ['completed', 'cancelled'])) {
            return redirect()->route('library.inventory.report', $session);
        }

        $recentItems = $session->items()
            ->with(['copy.book', 'scannedByUser'])
            ->orderByDesc('scanned_at')
            ->limit(50)
            ->get();

        return view('library.inventory.show', compact('session', 'recentItems'));
    }

    // ==================== SCAN (AJAX) ====================

    public function scan(Request $request, InventorySession $session): JsonResponse {
        Gate::authorize('manage-library');

        $request->validate([
            'accession_number' => ['required', 'string', 'max:100'],
        ]);

        try {
            $copyData = $this->inventoryService->scanCopy($session, $request->input('accession_number'));

            return response()->json([
                'success' => true,
                'message' => "Copy '{$copyData['accession_number']}' scanned successfully.",
                'data' => $copyData,
                'scanned_count' => $session->fresh()->scanned_count,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ==================== COMPLETE ====================

    public function complete(Request $request, InventorySession $session) {
        Gate::authorize('manage-library');

        try {
            $this->inventoryService->completeSession($session, auth()->id());

            return redirect()->route('library.inventory.report', $session)
                ->with('success', 'Inventory session completed successfully.');
        } catch (\RuntimeException $e) {
            return redirect()->route('library.inventory.show', $session)
                ->with('error', $e->getMessage());
        }
    }

    // ==================== CANCEL ====================

    public function cancel(Request $request, InventorySession $session) {
        Gate::authorize('manage-library');

        try {
            $this->inventoryService->cancelSession($session, auth()->id());

            return redirect()->route('library.inventory.index')
                ->with('success', 'Inventory session cancelled.');
        } catch (\RuntimeException $e) {
            return redirect()->route('library.inventory.show', $session)
                ->with('error', $e->getMessage());
        }
    }

    // ==================== REPORT ====================

    public function report(InventorySession $session) {
        Gate::authorize('manage-library');

        // Redirect in-progress sessions to scanning page
        if ($session->status === 'in_progress') {
            return redirect()->route('library.inventory.show', $session);
        }

        $discrepancies = $this->inventoryService->getDiscrepancies($session);

        $scannedItems = $session->items()
            ->with(['copy.book', 'scannedByUser'])
            ->orderByDesc('scanned_at')
            ->get();

        return view('library.inventory.report', compact('session', 'discrepancies', 'scannedItems'));
    }

    // ==================== MARK MISSING ====================

    public function markMissing(Request $request, InventorySession $session) {
        Gate::authorize('manage-library');

        $validated = $request->validate([
            'copy_ids' => ['required', 'array', 'min:1'],
            'copy_ids.*' => ['required', 'integer', 'exists:copies,id'],
        ]);

        $result = $this->inventoryService->markCopiesAsMissing($session, $validated['copy_ids']);

        $message = "{$result['success']} " . str_plural('copy', $result['success']) . " marked as missing.";

        if (!empty($result['errors'])) {
            $message .= ' ' . count($result['errors']) . ' ' . str_plural('error', count($result['errors'])) . ' occurred.';
            return redirect()->back()->with('warning', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    // ==================== EXPORT ====================

    public function export(InventorySession $session) {
        Gate::authorize('manage-library');

        $discrepancies = $this->inventoryService->getDiscrepancies($session);

        $filename = 'inventory-discrepancies-' . $session->id . '-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(
            new InventoryDiscrepancyExport($discrepancies, $session),
            $filename
        );
    }
}
