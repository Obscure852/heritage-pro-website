<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Http\Controllers\Controller;
use App\Services\StaffAttendance\SelfServiceClockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for self-service clock in/out AJAX endpoints.
 *
 * Provides JSON API for the clock widget to check status and perform clock actions.
 * All endpoints require authentication.
 */
class SelfServiceClockController extends Controller
{
    /**
     * The self-service clock service instance.
     *
     * @var SelfServiceClockService
     */
    protected SelfServiceClockService $service;

    /**
     * Create a new controller instance.
     *
     * @param SelfServiceClockService $service
     */
    public function __construct(SelfServiceClockService $service){
        $this->middleware('auth');
        $this->service = $service;
    }

    /**
     * Display the self-service clock in/out page.
     *
     * @return View
     */
    public function index(): View {
        return view('staff-attendance.self-service.index');
    }

    /**
     * Get current clock status for authenticated user.
     *
     * Returns whether user is clocked in/out and related times.
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse{
        $result = $this->service->getStatus(auth()->user());
        return response()->json($result);
    }

    /**
     * Handle clock-in action for authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clockIn(Request $request): JsonResponse{
        try {
            $result = $this->service->clockIn(
                auth()->user(),
                $request->ip()
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Handle clock-out action for authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clockOut(Request $request): JsonResponse{
        try {
            $result = $this->service->clockOut(
                auth()->user(),
                $request->ip()
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
