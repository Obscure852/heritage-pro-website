<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\TermHelper;
use App\Models\Lms\Enrollment;
use App\Models\Lms\ContentProgress;
use App\Models\Comment;
use App\Models\SchoolSetup;
use App\Models\Term;
use App\Models\Grade;
use App\Models\BookAllocation;
use App\Http\Controllers\Assessment\JuniorAssessmentController;
use App\Http\Controllers\Assessment\PrimaryAssessmentController;
use App\Http\Controllers\Assessment\SeniorAssessmentController;
use App\Services\SchoolModeResolver;

class StudentPortalController extends Controller {

    public function __construct() {
        $this->middleware('auth:student');
    }

    public function setTermSession(Request $request) {
        session(['selected_term_id' => $request->term_id]);

        $currentTerm = TermHelper::getCurrentTerm();
        $is_past_term = $request->term_id < $currentTerm->id;

        $request->session()->put('is_past_term', $is_past_term);
        return response()->json(['message' => 'Term set in session.']);
    }

    public function setGradeSession(Request $request) {
        if ($request->grade_id === 'all') {
            session()->forget('selected_grade_id');
        } else {
            session(['selected_grade_id' => $request->grade_id]);
        }
        return response()->json(['message' => 'Grade set in session.']);
    }

    public function index() {
        $currentTerm = TermHelper::getCurrentTerm();
        $terms = TermHelper::getTerms();
        $student = auth('student')->user();

        // Get active grades for the selector
        $activeGrades = Grade::where('active', 1)->orderBy('sequence')->get();

        // Get current class grade as default
        $currentGradeId = session('selected_grade_id', $student->currentClass?->grade_id);

        return view('students.portal.dashboard.index', compact('terms', 'currentTerm', 'student', 'activeGrades', 'currentGradeId'));
    }

    public function getDashboardTermData() {
        $student = auth('student')->user();
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedGradeId = session('selected_grade_id');

        // Get student's course enrollments with course and progress data
        $query = Enrollment::where('student_id', $student->id)
            ->with(['course.modules.contentItems', 'course.instructor', 'course.grade']);

        // Filter by grade name if selected (grades are versioned, so match by name)
        if ($selectedGradeId) {
            $selectedGrade = Grade::find($selectedGradeId);
            if ($selectedGrade) {
                $query->whereHas('course.grade', function ($q) use ($selectedGrade) {
                    $q->where('name', $selectedGrade->name);
                });
            }
        }

        $enrollments = $query->get();

        // Calculate LMS stats
        $totalCourses = $enrollments->count();
        $completedCourses = $enrollments->where('status', 'completed')->count();
        $inProgressCourses = $enrollments->where('status', 'active')->count();

        // Get content progress for all enrolled courses
        $enrollmentIds = $enrollments->pluck('id');
        $contentProgress = ContentProgress::whereIn('enrollment_id', $enrollmentIds)
            ->with(['contentItem.module.course'])
            ->get();

        $termData = [
            'enrollments' => $enrollments,
            'totalCourses' => $totalCourses,
            'completedCourses' => $completedCourses,
            'inProgressCourses' => $inProgressCourses,
            'contentProgress' => $contentProgress,
        ];

        return view('students.portal.dashboard.dashboard-term', compact('termData', 'currentTerm'));
    }

    public function academicIndex() {
        $student = auth('student')->user();
        $currentTerm = TermHelper::getCurrentTerm();
        $terms = TermHelper::getTerms();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        // Get student's tests for the selected term (term_id is on tests table, not pivot)
        $tests = $student->tests()
            ->with(['subject.subject'])
            ->where('tests.term_id', $selectedTermId)
            ->get();

        // Calculate stats
        $caTests = $tests->where('type', 'CA');
        $examTests = $tests->where('type', 'Exam');
        $subjects = $tests->pluck('subject.subject.name')->unique()->filter();
        $avgPercentage = $tests->avg('pivot.percentage') ?? 0;

        $stats = [
            'totalSubjects' => $subjects->count(),
            'caTestsCount' => $caTests->count(),
            'examsCount' => $examTests->count(),
            'avgPercentage' => round($avgPercentage, 1),
        ];

        return view('students.portal.academic.index', compact('terms', 'currentTerm', 'student', 'stats'));
    }

    public function getAcademicPerformance() {
        $student = auth('student')->user();
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        // Get student's tests for the selected term (term_id is on tests table, not pivot)
        $tests = $student->tests()
            ->with(['subject.subject'])
            ->where('tests.term_id', $selectedTermId)
            ->get();

        // Separate CA tests and Exam tests
        $caTests = $tests->where('type', 'CA')->groupBy(function ($test) {
            return $test->subject->subject->name ?? 'Unknown';
        });

        $examTests = $tests->where('type', 'Exam')->groupBy(function ($test) {
            return $test->subject->subject->name ?? 'Unknown';
        });

        return view('students.portal.academic.performance', compact('caTests', 'examTests', 'currentTerm'));
    }

    public function getReportCards() {
        $student = auth('student')->user();
        $currentTerm = TermHelper::getCurrentTerm();
        $terms = TermHelper::getTerms();
        $schoolType = $this->schoolModeResolver()->portalReportCardDriverForLevel(
            $this->schoolModeResolver()->levelForStudent($student)
        );

        // Get all terms where student has tests (term_id is on tests table)
        $termIds = $student->tests()->pluck('tests.term_id')->unique();
        $reportTerms = Term::whereIn('id', $termIds)->orderBy('year', 'desc')->orderBy('term', 'desc')->get();

        return view('students.portal.academic.report-cards-page', compact('reportTerms', 'schoolType', 'student', 'terms', 'currentTerm'));
    }

    public function getBooks(Request $request) {
        $student = auth('student')->user();
        $currentTerm = TermHelper::getCurrentTerm();

        // Get all grades where the student has book allocations
        $gradeIds = BookAllocation::where('student_id', $student->id)
            ->distinct()
            ->pluck('grade_id');
        $grades = Grade::whereIn('id', $gradeIds)->orderBy('name')->get();

        // Get selected grade from request or default to current class grade
        $selectedGradeId = $request->get('grade_id');
        if (!$selectedGradeId && $student->currentClass()) {
            $selectedGradeId = $student->currentClass()->grade_id;
        }
        if (!$selectedGradeId && $grades->isNotEmpty()) {
            $selectedGradeId = $grades->first()->id;
        }

        // Get book allocations for the selected grade
        $bookAllocations = BookAllocation::where('student_id', $student->id)
            ->when($selectedGradeId, function ($query) use ($selectedGradeId) {
                $query->where('grade_id', $selectedGradeId);
            })
            ->with(['copy.book.author', 'grade'])
            ->orderBy('allocation_date', 'desc')
            ->get();

        return view('students.portal.academic.books-page', compact(
            'bookAllocations',
            'grades',
            'selectedGradeId',
            'student',
            'currentTerm'
        ));
    }

    public function viewReportCardPdf(Request $request) {
        $student = auth('student')->user();
        $schoolType = $this->schoolModeResolver()->portalReportCardDriverForLevel(
            $this->schoolModeResolver()->levelForStudent($student, $request->integer('term_id'))
        );

        // Set the term in session if provided
        if ($request->has('term_id')) {
            session(['selected_term_id' => $request->term_id]);
        }

        // Call the appropriate controller based on school type
        switch ($schoolType) {
            case 'primary':
                $controller = app(PrimaryAssessmentController::class);
                return $controller->primaryPDFReportCard($student->id);
            case 'senior':
                $controller = app(SeniorAssessmentController::class);
                return $controller->pdfReportCardSenior($student->id);
            default: // junior
                $controller = app(JuniorAssessmentController::class);
                return $controller->pdfReportCardJunior3($student->id);
        }
    }

    private function schoolModeResolver(): SchoolModeResolver
    {
        return app(SchoolModeResolver::class);
    }

    public function profile() {
        $student = auth('student')->user();
        $student->load(['sponsor', 'currentGrade']);
        $currentTerm = TermHelper::getCurrentTerm();

        return view('students.portal.profile.index', compact('student', 'currentTerm'));
    }

    public function updateProfile(Request $request) {
        $student = auth('student')->user();

        $request->validate([
            'email' => 'nullable|email|unique:students,email,' . $student->id,
        ]);

        $student->update([
            'email' => $request->email,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request) {
        $student = auth('student')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!\Hash::check($request->current_password, $student->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $student->update([
            'password' => \Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
