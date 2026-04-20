<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fee\GrantClearanceOverrideRequest;
use App\Helpers\TermHelper;
use App\Models\Fee\StudentClearance;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Term;
use App\Services\Fee\BalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BalanceController extends Controller
{
    protected BalanceService $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->middleware('auth');
        $this->balanceService = $balanceService;
    }

    /**
     * Display a listing of students with outstanding balances.
     */
    public function outstandingStudents(Request $request): View
    {
        Gate::authorize('collect-fees');

        // Default to current term's year
        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? (int) date('Y');

        $year = $request->filled('year')
            ? (int) $request->year
            : $currentTermYear;

        // Get optional filters
        $gradeId = $request->filled('grade_id') ? $request->grade_id : null;
        $search = $request->filled('search') ? $request->search : null;

        // Get outstanding students for the year
        $students = $this->balanceService->getOutstandingStudentsForYear($year);

        // Filter by grade if provided
        if ($gradeId) {
            $students = $students->filter(function ($item) use ($gradeId) {
                $studentGrade = $item['student']->currentGrade;
                return $studentGrade && $studentGrade->id == $gradeId;
            })->values();
        }

        // Filter by search term if provided
        if ($search) {
            $searchLower = strtolower($search);
            $students = $students->filter(function ($item) use ($searchLower) {
                $student = $item['student'];
                $fullName = strtolower($student->first_name . ' ' . $student->last_name);
                $studentNumber = strtolower($student->student_number ?? '');

                return str_contains($fullName, $searchLower)
                    || str_contains(strtolower($student->first_name), $searchLower)
                    || str_contains(strtolower($student->last_name), $searchLower)
                    || str_contains($studentNumber, $searchLower);
            })->values();
        }

        return view('fees.balance.outstanding-students', [
            'students' => $students,
            'years' => $this->getAvailableYears(),
            'grades' => Grade::where('active', true)->orderBy('name')->get(),
            'filters' => [
                'year' => $year,
                'grade_id' => $gradeId,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display clearance status for a specific student.
     */
    public function clearanceStatus(Student $student, Request $request): View
    {
        Gate::authorize('collect-fees');

        // Load student relationships
        $student->load(['sponsor', 'currentGrade']);

        // Default to current term's year
        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? (int) date('Y');

        $year = $request->filled('year')
            ? (int) $request->year
            : $currentTermYear;

        // Get clearance status
        $clearanceData = $this->balanceService->checkYearClearance($student->id, $year);
        $balanceData = $this->balanceService->getStudentBalance($student->id, $year);

        // Get clearance record if exists (for override details)
        $clearanceRecord = StudentClearance::forStudent($student->id)
            ->forYear($year)
            ->with('grantedBy')
            ->first();

        return view('fees.balance.clearance-status', [
            'student' => $student,
            'clearanceData' => $clearanceData,
            'balanceData' => $balanceData,
            'clearanceRecord' => $clearanceRecord,
            'years' => $this->getAvailableYears(),
            'selectedYear' => $year,
        ]);
    }

    /**
     * Grant a clearance override.
     */
    public function grantOverride(GrantClearanceOverrideRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $this->balanceService->grantClearanceOverride(
                $validated['student_id'],
                $validated['year'],
                auth()->user(),
                $validated['reason'],
                $validated['notes'] ?? null
            );

            return redirect()
                ->back()
                ->with('success', 'Clearance override granted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Revoke a clearance override.
     */
    public function revokeOverride(Request $request, Student $student, int $year): RedirectResponse
    {
        Gate::authorize('manage-fee-setup');

        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'A reason for revoking the override is required.',
            'reason.min' => 'Please provide a more detailed reason (at least 10 characters).',
        ]);

        try {
            $result = $this->balanceService->revokeClearanceOverride(
                $student->id,
                $year,
                auth()->user(),
                $request->reason
            );

            if ($result) {
                return redirect()
                    ->back()
                    ->with('success', 'Clearance override revoked successfully.');
            }

            return redirect()
                ->back()
                ->with('error', 'No active override found to revoke.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get available years from the terms table.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getAvailableYears()
    {
        return Term::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }
}
