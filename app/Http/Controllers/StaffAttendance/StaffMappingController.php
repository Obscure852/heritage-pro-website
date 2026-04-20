<?php

namespace App\Http\Controllers\StaffAttendance;

use App\Http\Controllers\Controller;
use App\Models\StaffAttendance\BiometricIdMapping;
use App\Services\StaffAttendance\StaffMappingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for managing biometric ID to user mappings.
 *
 * Provides endpoints for viewing unmapped IDs, creating manual mappings,
 * deleting mappings, and viewing staff without mappings.
 */
class StaffMappingController extends Controller
{
    /**
     * The staff mapping service instance.
     *
     * @var StaffMappingService
     */
    protected StaffMappingService $staffMappingService;

    /**
     * Create a new controller instance.
     *
     * @param StaffMappingService $staffMappingService
     */
    public function __construct(StaffMappingService $staffMappingService)
    {
        $this->staffMappingService = $staffMappingService;
    }

    /**
     * Display a listing of unmapped biometric IDs.
     *
     * Shows IDs that could not be auto-matched to users,
     * with stats and a modal for manual mapping.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Get unmapped biometric IDs (recent 30 days by default)
        $unmappedIds = $this->staffMappingService->getUnmappedBiometricIds();

        // Get stats for header display
        $stats = $this->staffMappingService->getMappingStats();

        // Get staff without mapping for the dropdown modal
        $unmappedStaff = $this->staffMappingService->getStaffWithoutMapping();

        return view('staff-attendance.mapping.index', compact('unmappedIds', 'stats', 'unmappedStaff'));
    }

    /**
     * Store a new manual mapping.
     *
     * Creates a mapping between a biometric employee number and a user,
     * and removes the ID from the unmapped tracking.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_number' => 'required|string|max:50',
            'user_id' => 'required|exists:users,id',
        ]);

        $this->staffMappingService->createManualMapping(
            $validated['employee_number'],
            (int) $validated['user_id'],
            auth()->id()
        );

        return redirect()
            ->back()
            ->with('message', 'Biometric ID mapped successfully.');
    }

    /**
     * Remove the specified mapping.
     *
     * Deletes the mapping, allowing the ID to be remapped if encountered again.
     *
     * @param BiometricIdMapping $mapping
     * @return RedirectResponse
     */
    public function destroy(BiometricIdMapping $mapping): RedirectResponse
    {
        $this->staffMappingService->deleteMapping($mapping);

        return redirect()
            ->back()
            ->with('message', 'Mapping deleted successfully.');
    }

    /**
     * Display staff members without biometric mappings.
     *
     * Shows current staff who have no biometric ID associated with them,
     * useful for identifying who needs to be enrolled on devices.
     *
     * @return View
     */
    public function unmappedStaff(): View
    {
        // Get staff without mapping
        $staff = $this->staffMappingService->getStaffWithoutMapping();

        // Get stats for header display
        $stats = $this->staffMappingService->getMappingStats();

        return view('staff-attendance.mapping.unmapped-staff', compact('staff', 'stats'));
    }
}
