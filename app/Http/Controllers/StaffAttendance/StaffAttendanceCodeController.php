<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Http\Controllers\Controller;
use App\Models\StaffAttendance\StaffAttendanceCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Controller for managing staff attendance codes.
 *
 * Provides CRUD operations for attendance status codes (P, A, L, etc.)
 * with authorization via the manage-staff-attendance-codes gate.
 */
class StaffAttendanceCodeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of attendance codes.
     *
     * @return View
     */
    public function index(): View
    {
        Gate::authorize('manage-staff-attendance-codes');

        $codes = StaffAttendanceCode::ordered()->get();

        $stats = [
            'total' => $codes->count(),
            'active' => $codes->where('is_active', true)->count(),
            'present_codes' => $codes->where('counts_as_present', true)->count(),
        ];

        return view('staff-attendance.codes.index', compact('codes', 'stats'));
    }

    /**
     * Get list of active codes for dropdowns (AJAX).
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $codes = StaffAttendanceCode::active()->ordered()->get();

        return response()->json($codes);
    }

    /**
     * Store a new attendance code.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('manage-staff-attendance-codes');

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:staff_attendance_codes,code',
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'counts_as_present' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        // Handle checkboxes - unchecked checkboxes are not sent by browser
        $validated['counts_as_present'] = $request->boolean('counts_as_present');
        $validated['is_active'] = $request->boolean('is_active', true); // Default active

        // Auto-assign order if not provided
        if (!isset($validated['order']) || $validated['order'] === null) {
            $validated['order'] = StaffAttendanceCode::max('order') + 1;
        }

        $code = StaffAttendanceCode::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'code' => $code]);
        }

        return back()->with('success', 'Attendance code created successfully');
    }

    /**
     * Update an existing attendance code.
     *
     * @param Request $request
     * @param StaffAttendanceCode $code
     * @return RedirectResponse|JsonResponse
     */
    public function update(Request $request, StaffAttendanceCode $code): RedirectResponse|JsonResponse
    {
        Gate::authorize('manage-staff-attendance-codes');

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:staff_attendance_codes,code,' . $code->id,
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'counts_as_present' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        // Handle checkboxes
        $validated['counts_as_present'] = $request->boolean('counts_as_present');
        $validated['is_active'] = $request->boolean('is_active');

        $code->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'code' => $code->fresh()]);
        }

        return back()->with('success', 'Attendance code updated successfully');
    }

    /**
     * Toggle active status of a code.
     *
     * @param StaffAttendanceCode $code
     * @return RedirectResponse
     */
    public function toggleActive(StaffAttendanceCode $code): RedirectResponse
    {
        Gate::authorize('manage-staff-attendance-codes');

        $code->update(['is_active' => !$code->is_active]);

        return back()->with('success', 'Attendance code ' . ($code->is_active ? 'activated' : 'deactivated'));
    }

    /**
     * Delete an attendance code.
     *
     * @param StaffAttendanceCode $code
     * @return RedirectResponse
     */
    public function destroy(StaffAttendanceCode $code): RedirectResponse
    {
        Gate::authorize('manage-staff-attendance-codes');

        // Check if code is in use
        if ($code->isInUse()) {
            return back()->with('error', 'Cannot delete code that is in use by attendance records');
        }

        $code->delete();

        return back()->with('success', 'Attendance code deleted successfully');
    }
}
