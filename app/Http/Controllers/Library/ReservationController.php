<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\CancelReservationRequest;
use App\Http\Requests\Library\PlaceReservationRequest;
use App\Models\Book;
use App\Models\Library\LibraryReservation;
use App\Services\Library\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReservationController extends Controller {
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService) {
        $this->middleware('auth');
        $this->reservationService = $reservationService;
    }

    /**
     * Display the reservation management page with filters and summary stats.
     */
    public function index(Request $request): View {
        Gate::authorize('manage-library');

        $query = LibraryReservation::with(['book', 'borrower']);

        // Filter by status (default: active = pending + ready)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->active();
        }

        // Filter by book
        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(25);

        // Summary stats (global aggregates, not filtered)
        $stats = [
            'pending' => LibraryReservation::where('status', 'pending')->count(),
            'ready' => LibraryReservation::where('status', 'ready')->count(),
            'fulfilled_today' => LibraryReservation::where('status', 'fulfilled')
                ->whereDate('fulfilled_at', today())->count(),
            'expired_today' => LibraryReservation::where('status', 'expired')
                ->whereDate('cancelled_at', today())->count(),
        ];

        return view('library.reservations.index', compact('reservations', 'stats'));
    }

    /**
     * Place a reservation on behalf of a borrower (librarian action).
     */
    public function store(PlaceReservationRequest $request): JsonResponse {
        try {
            $reservation = $this->reservationService->placeReservation(
                $request->validated()['book_id'],
                $request->validated()['borrower_type'],
                $request->validated()['borrower_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Reservation placed successfully. Queue position: ' . $reservation->queue_position,
                'data' => [
                    'reservation_id' => $reservation->id,
                    'queue_position' => $reservation->queue_position,
                    'status' => $reservation->status,
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
     * Cancel a reservation (librarian action).
     */
    public function cancel(CancelReservationRequest $request, LibraryReservation $reservation): JsonResponse {
        try {
            $this->reservationService->cancelReservation(
                $reservation,
                $request->validated()['reason'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Reservation cancelled successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Borrower self-service reservation from catalog.
     *
     * Staff (users) who have access-library gate can reserve books.
     * Students do not log in, so this is staff-only.
     */
    public function reserve(Request $request, Book $book): JsonResponse {
        try {
            $reservation = $this->reservationService->placeReservation(
                $book->id,
                'user',
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Reservation placed successfully. Queue position: ' . $reservation->queue_position,
                'data' => [
                    'reservation_id' => $reservation->id,
                    'queue_position' => $reservation->queue_position,
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
