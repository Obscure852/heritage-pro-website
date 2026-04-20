<?php

namespace App\Http\Controllers\Leave;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\StorePublicHolidayRequest;
use App\Http\Requests\Leave\UpdatePublicHolidayRequest;
use App\Models\Leave\PublicHoliday;
use App\Models\Term;
use App\Services\Leave\PublicHolidayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for managing public holidays.
 *
 * Handles CRUD operations and calendar display for public holidays.
 */
class PublicHolidayController extends Controller {
    /**
     * The public holiday service instance.
     *
     * @var PublicHolidayService
     */
    protected PublicHolidayService $publicHolidayService;

    /**
     * Create a new controller instance.
     *
     * @param PublicHolidayService $publicHolidayService
     */
    public function __construct(PublicHolidayService $publicHolidayService) {
        $this->publicHolidayService = $publicHolidayService;
    }

    /**
     * Display list of public holidays.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View {
        // Get current year from TermHelper
        $currentTerm = TermHelper::getCurrentTerm();
        $currentYear = $currentTerm ? (int) $currentTerm->year : (int) date('Y');
        $year = (int) $request->get('year', $currentYear);

        $holidays = $this->publicHolidayService->getForYear($year);

        // Calculate stats
        $totalCount = $holidays->count();
        $activeCount = $holidays->where('is_active', true)->count();
        $recurringCount = $holidays->where('is_recurring', true)->count();

        // Available years from Terms table
        $availableYears = Term::distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // Group holidays by month for calendar
        $holidaysByMonth = $this->publicHolidayService->getHolidaysGroupedByMonth($year);

        return view('leave.holidays.index', compact(
            'holidays',
            'year',
            'currentYear',
            'totalCount',
            'activeCount',
            'recurringCount',
            'availableYears',
            'holidaysByMonth'
        ));
    }

    /**
     * Display calendar view of public holidays.
     *
     * @param Request $request
     * @return View
     */
    public function calendar(Request $request): View {
        $currentYear = (int) date('Y');
        $year = (int) $request->get('year', $currentYear);

        $holidays = $this->publicHolidayService->getActiveForYear($year);
        $holidaysByMonth = $this->publicHolidayService->getHolidaysGroupedByMonth($year);

        $availableYears = range($currentYear - 1, $currentYear + 2);

        return view('leave.holidays.calendar', compact(
            'holidays',
            'holidaysByMonth',
            'year',
            'currentYear',
            'availableYears'
        ));
    }

    /**
     * Show the form for creating a new public holiday.
     *
     * @return View
     */
    public function create(): View {
        return view('leave.holidays.create');
    }

    /**
     * Store a newly created public holiday.
     *
     * @param StorePublicHolidayRequest $request
     * @return RedirectResponse
     */
    public function store(StorePublicHolidayRequest $request): RedirectResponse {
        $validated = $request->validated();

        $this->publicHolidayService->create($validated);

        return redirect()
            ->route('leave.holidays.index')
            ->with('message', 'Public holiday created successfully.');
    }

    /**
     * Show the form for editing a public holiday.
     *
     * @param PublicHoliday $holiday
     * @return View
     */
    public function edit(PublicHoliday $holiday): View {
        return view('leave.holidays.edit', compact('holiday'));
    }

    /**
     * Update the specified public holiday.
     *
     * @param UpdatePublicHolidayRequest $request
     * @param PublicHoliday $holiday
     * @return RedirectResponse
     */
    public function update(UpdatePublicHolidayRequest $request, PublicHoliday $holiday): RedirectResponse {
        $validated = $request->validated();

        $this->publicHolidayService->update($holiday, $validated);

        return redirect()
            ->route('leave.holidays.index')
            ->with('message', 'Public holiday updated successfully.');
    }

    /**
     * Remove the specified public holiday.
     *
     * @param PublicHoliday $holiday
     * @return RedirectResponse
     */
    public function destroy(PublicHoliday $holiday): RedirectResponse {
        $this->publicHolidayService->delete($holiday);

        return redirect()
            ->route('leave.holidays.index')
            ->with('message', 'Public holiday deleted successfully.');
    }

    /**
     * Toggle the active status of a public holiday.
     *
     * @param PublicHoliday $holiday
     * @return JsonResponse
     */
    public function toggleStatus(PublicHoliday $holiday): JsonResponse {
        $updatedHoliday = $this->publicHolidayService->toggleStatus($holiday);

        return response()->json([
            'success' => true,
            'is_active' => $updatedHoliday->is_active,
            'message' => $updatedHoliday->is_active
                ? 'Holiday activated successfully.'
                : 'Holiday deactivated successfully.',
        ]);
    }
}
