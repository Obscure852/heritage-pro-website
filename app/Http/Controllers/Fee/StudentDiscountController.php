<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fee\StoreStudentDiscountRequest;
use App\Helpers\TermHelper;
use App\Models\Fee\DiscountType;
use App\Models\Fee\StudentDiscount;
use App\Models\Student;
use App\Models\Term;
use App\Services\Fee\DiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StudentDiscountController extends Controller
{
    protected DiscountService $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->middleware('auth');
        $this->discountService = $discountService;
    }

    /**
     * Display listing of student discounts with sibling suggestions.
     */
    public function index(Request $request): View
    {
        Gate::authorize('collect-fees');

        // Default to current term's year
        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? (int) date('Y');

        $year = $request->filled('year')
            ? (int) $request->year
            : $currentTermYear;

        // Get existing student discounts
        $query = StudentDiscount::with(['student', 'discountType', 'assignedBy'])
            ->forYear($year);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('discount_type_id')) {
            $query->where('discount_type_id', $request->discount_type_id);
        }

        $studentDiscounts = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get sibling candidates (students with siblings who don't have sibling discount)
        $siblingCandidates = $this->discountService->findSiblingCandidates($year);

        return view('fees.discounts.index', [
            'studentDiscounts' => $studentDiscounts,
            'siblingCandidates' => $siblingCandidates,
            'discountTypes' => DiscountType::active()->orderBy('name')->get(),
            'years' => $this->getAvailableYears(),
            'currentYear' => $year,
            'filters' => $request->only(['search', 'discount_type_id', 'year']),
        ]);
    }

    /**
     * Show form to assign discount to student.
     */
    public function create(Request $request): View
    {
        Gate::authorize('collect-fees');

        // Default to current term's year
        $currentTermYear = TermHelper::getCurrentTerm()?->year ?? (int) date('Y');

        $year = $request->filled('year')
            ? (int) $request->year
            : $currentTermYear;

        $preselectedStudent = $request->filled('student_id')
            ? Student::with('sponsor')->find($request->student_id)
            : null;

        // Get siblings if student is preselected
        $siblings = $preselectedStudent
            ? $this->discountService->getSiblingsForStudent($preselectedStudent, $year)
            : collect();

        // Get current students for dropdown (only those enrolled in a grade)
        $students = Student::where('status', 'Current')
            ->whereHas('currentGrade')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('fees.discounts.assign', [
            'discountTypes' => DiscountType::active()->orderBy('name')->get(),
            'years' => $this->getAvailableYears(),
            'currentYear' => $year,
            'preselectedStudent' => $preselectedStudent,
            'siblings' => $siblings,
            'students' => $students,
        ]);
    }

    /**
     * Store a new student discount assignment.
     */
    public function store(StoreStudentDiscountRequest $request): RedirectResponse
    {
        Gate::authorize('collect-fees');

        $validated = $request->validated();

        $this->discountService->assignDiscountToStudent(
            $validated,
            $request->user()
        );

        // Redirect to index with the year filter set to show the newly added discount
        return redirect()
            ->route('fees.discounts.index', ['year' => $validated['year']])
            ->with('success', 'Discount assigned to student successfully.');
    }

    /**
     * Remove a student discount assignment.
     */
    public function destroy(StudentDiscount $studentDiscount): RedirectResponse
    {
        Gate::authorize('collect-fees');

        $this->discountService->removeStudentDiscount($studentDiscount);

        return redirect()
            ->route('fees.discounts.index')
            ->with('success', 'Student discount removed successfully.');
    }

    /**
     * API: Get siblings for a student (for AJAX).
     */
    public function getSiblings(Request $request, Student $student): JsonResponse
    {
        Gate::authorize('collect-fees');

        $year = $request->get('year', (int) date('Y'));
        $siblings = $this->discountService->getSiblingsForStudent($student, $year);

        return response()->json([
            'siblings' => $siblings->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'grade' => $s->currentGrade?->name ?? 'N/A',
            ]),
            'sponsor_name' => $student->sponsor?->name ?? 'Unknown',
        ]);
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
