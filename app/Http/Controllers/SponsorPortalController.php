<?php

namespace App\Http\Controllers;

use App\Helpers\AssessmentHelper;
use App\Helpers\TermHelper;
use App\Models\Fee\FeePayment;
use App\Models\Fee\StudentInvoice;
use App\Models\GradeSubject;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\Term;
use App\Models\Test;
use App\Services\SchoolModeResolver;
use App\Services\Fee\BalanceService;
use App\Services\Fee\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Log;
use Illuminate\Http\Request;

class SponsorPortalController extends Controller{

    public function __construct(){
        $this->middleware('auth:sponsor');
    }

    public function setTermSession(Request $request) {
        session(['selected_term_id' => $request->term_id]);

        $currentTerm = TermHelper::getCurrentTerm();
        $is_past_term = $request->term_id < $currentTerm->id;

        $request->session()->put('is_past_term', $is_past_term);
        return response()->json(['message' => 'Term set in session.']);
    }
    
    public function index(){
        // For sponsors: Get actual current term based on date
        $currentTerm = $this->getActualCurrentTerm();

        // For sponsors: Get ALL terms (including closed) so they can view historical data
        $terms = Term::orderBy('year', 'asc')
                     ->orderBy('term', 'asc')
                     ->get();

        return view('sponsors.portal.dashboard.index', compact('terms', 'currentTerm'));
    }

    public function getDashboardTermData(){
        $selectedTermId = session('selected_term_id', $this->getActualCurrentTerm()->id);
        $sponsor = auth('sponsor')->user();

        $school_data = SchoolSetup::first();

        $children = $sponsor->students()->whereHas('terms', function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            })->get();

        $currentTerm = $this->getActualCurrentTerm();
        $notifications = $sponsor->notifications()->where('term_id', $selectedTermId)->orderByDesc('is_pinned')->orderByDesc('created_at')->get();
        return view('sponsors.portal.dashboard.dashboard-term', compact('children', 'currentTerm', 'notifications','school_data'));
    }

    public function assessmentIndex(){
        $sponsor = auth('sponsor')->user();
        $students = $sponsor->students()->with(['currentClassRelation.grade'])->get();

        // If only one student, redirect directly to their assessment page
        if ($students->count() === 1) {
            return redirect()->route('sponsor.assessment.student', $students->first()->id);
        }

        // For sponsors: Get actual current term based on date
        $currentTerm = $this->getActualCurrentTerm();
        $school_data = SchoolSetup::first();

        return view('sponsors.portal.assessment.index', compact('students', 'currentTerm', 'school_data'));
    }

    /**
     * Show individual student assessment page
     */
    public function assessmentStudentShow($studentId)
    {
        $sponsor = auth('sponsor')->user();

        // Security: Ensure student belongs to this sponsor
        $student = $sponsor->students()
            ->where('id', $studentId)
            ->with(['currentClassRelation.grade'])
            ->firstOrFail();

        // For sponsors: Get actual current term based on date
        $currentTerm = $this->getActualCurrentTerm();

        // For sponsors: Get ALL terms (including closed) so they can view historical data
        $terms = Term::orderBy('year', 'asc')
                     ->orderBy('term', 'asc')
                     ->get();

        $school_data = SchoolSetup::first();

        return view('sponsors.portal.assessment.show', compact('student', 'terms', 'currentTerm', 'school_data'));
    }

    /**
     * Get individual student's test data for a term (AJAX)
     */
    public function getStudentTestsData($studentId)
    {
        $sponsor = auth('sponsor')->user();
        $selectedTermId = session('selected_term_id', $this->getActualCurrentTerm()->id);

        // Security: Ensure student belongs to this sponsor
        $student = $sponsor->students()
            ->where('id', $studentId)
            ->with([
                'tests' => function ($q) use ($selectedTermId) {
                    $q->where('term_id', $selectedTermId)
                      ->with(['subject.subject', 'term']);
                },
                'subjectComments' => function ($q) use ($selectedTermId) {
                    $q->where('term_id', $selectedTermId);
                },
                'currentClassRelation' => function ($q) use ($selectedTermId) {
                    $q->wherePivot('term_id', $selectedTermId);
                },
                'currentClassRelation.grade',
                'currentClassRelation.teacher',
            ])
            ->firstOrFail();

        $student->setRelation('tests', $student->tests->unique('id'));

        $school_data = SchoolSetup::first();

        // Calculate term data based on school type
        $examTests = $student->tests->where('type', 'Exam');
        $termData = $this->calculateTermData($student, $examTests, $selectedTermId);

        $currentTerm = Term::find($selectedTermId);

        return view('sponsors.portal.assessment.student-term', compact(
            'student', 'currentTerm', 'school_data', 'termData', 'selectedTermId'
        ));
    }

    /**
     * Get the actual current term based on today's date (for sponsor portal)
     * This ignores the "unclosed term" logic used internally by the school
     */
    private function getActualCurrentTerm() {
        $today = now();

        // First try: Term where today falls within the date range
        $currentTerm = Term::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($currentTerm) {
            return $currentTerm;
        }

        // Second try: Most recent past term (for viewing historical data)
        $pastTerm = Term::where('end_date', '<', $today)
            ->orderBy('end_date', 'desc')
            ->first();

        if ($pastTerm) {
            return $pastTerm;
        }

        // Fallback: Next upcoming term
        return Term::where('start_date', '>', $today)
            ->orderBy('start_date', 'asc')
            ->first() ?? Term::orderBy('id', 'desc')->first();
    }

    public function getTestsData(){
        $selectedTermId = session('selected_term_id', $this->getActualCurrentTerm()->id);
        $sponsor = auth('sponsor')->user();
        $school_data = SchoolSetup::first();

        // Enhanced eager loading with subject comments and current class
        $children = $sponsor->students()->whereHas('terms', function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            })->with([
                'tests' => function ($q) use ($selectedTermId) {
                    $q->where('term_id', $selectedTermId)
                      ->with(['subject.subject', 'term']);
                },
                'subjectComments' => function ($q) use ($selectedTermId) {
                    $q->where('term_id', $selectedTermId);
                },
                'currentClassRelation' => function ($q) use ($selectedTermId) {
                    $q->wherePivot('term_id', $selectedTermId);
                },
                'currentClassRelation.grade',
                'currentClassRelation.teacher',
            ])->get();

        $children->each(function ($child) {
            $child->setRelation('tests', $child->tests->unique('id'));
        });

        // Calculate term data for each child based on school type
        $childrenTermData = [];
        foreach ($children as $child) {
            $examTests = $child->tests->where('type', 'Exam');
            $childrenTermData[$child->id] = $this->calculateTermData($child, $examTests, $selectedTermId);
        }

        $currentTerm = Term::find($selectedTermId);

        return view('sponsors.portal.assessment.assessment-term', compact(
            'children', 'currentTerm', 'school_data', 'childrenTermData', 'selectedTermId'
        ));
    }

    /**
     * Calculate term data for a student based on school type
     */
    private function calculateTermData($student, $examTests, $termId) {
        $driver = $this->schoolModeResolver()->assessmentDriverForLevel(
            $this->schoolModeResolver()->levelForStudent($student, $termId)
        );

        $result = [
            'driver' => $driver,
            'totalPoints' => 0,
            'overallGrade' => null,
            'averagePercentage' => 0,
            'bestSubjects' => [],
            'totalScore' => 0,
            'totalOutOf' => 0,
        ];

        if ($examTests->isEmpty()) {
            return $result;
        }

        $currentClass = $student->currentClassRelation ? $student->currentClassRelation->first() : null;
        $isForeigner = $student->nationality !== 'Motswana';

        try {
            if ($driver === 'junior') {
                // Junior: Use AssessmentHelper for mandatory/optional/core calculation
                if ($currentClass && $currentClass->grade_id) {
                    $gradeSubjects = GradeSubject::where('grade_id', $currentClass->grade_id)
                        ->where('term_id', $termId)->get();

                    if ($gradeSubjects->isNotEmpty()) {
                        list($m, $o, $c) = AssessmentHelper::calculatePoints($student, $gradeSubjects, $termId, $isForeigner);
                        $result['totalPoints'] = $m + $o + $c;
                        $result['overallGrade'] = AssessmentHelper::determineGrade($result['totalPoints'], $currentClass);
                    }
                }
            } elseif ($driver === 'senior') {
                // Senior: Best 6 subjects by points (slot-based)
                $scores = [];
                foreach ($examTests as $test) {
                    $points = $test->pivot->points ?? 0;
                    $subjectName = ($test->subject && $test->subject->subject) ? $test->subject->subject->name : '';
                    $slotsNeeded = (strtolower($subjectName) === 'double science') ? 2 : 1;
                    $scores[] = [
                        'subject' => $subjectName,
                        'points' => $points,
                        'slotsNeeded' => $slotsNeeded,
                    ];
                }
                // Sort by points descending
                usort($scores, fn($a, $b) => $b['points'] <=> $a['points']);

                // Select best 6 slots
                $totalSlots = 0;
                $bestSubjects = [];
                $totalPoints = 0;
                foreach ($scores as $score) {
                    if ($totalSlots + $score['slotsNeeded'] <= 6) {
                        $bestSubjects[] = $score;
                        $totalSlots += $score['slotsNeeded'];
                        $totalPoints += ($score['slotsNeeded'] === 2) ? $score['points'] * 2 : $score['points'];
                    }
                    if ($totalSlots >= 6) break;
                }
                $result['totalPoints'] = $totalPoints;
                $result['bestSubjects'] = $bestSubjects;
            } elseif ($driver === 'primary') {
                // Primary: Average percentage
                $totalScore = $examTests->sum(fn($t) => $t->pivot->score ?? 0);
                $totalOutOf = $examTests->sum(fn($t) => $t->out_of ?? 100);
                $averagePercentage = $totalOutOf > 0 ? round(($totalScore / $totalOutOf) * 100, 1) : 0;

                // Get overall grade from percentage
                $gradeId = ($currentClass && $currentClass->grade_id) ? $currentClass->grade_id : null;
                $overallGrade = $gradeId ? AssessmentController::getOverallGrade($gradeId, $averagePercentage) : null;

                $result['totalScore'] = $totalScore;
                $result['totalOutOf'] = $totalOutOf;
                $result['averagePercentage'] = $averagePercentage;
                $result['overallGrade'] = $overallGrade;
            }
        } catch (\Exception $e) {
            Log::warning('Term calculations failed for student in sponsor portal', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    private function schoolModeResolver(): SchoolModeResolver
    {
        return app(SchoolModeResolver::class);
    }

    public function assessmentReportCardTimeline(){
        $currentTerm = $this->getActualCurrentTerm();
        $sponsor = auth('sponsor')->user();

        // Get all terms up to the current term for timeline view
        $terms = Term::where('year', '<', $currentTerm->year)
                    ->orWhere(function($query) use ($currentTerm) {
                        $query->where('year', $currentTerm->year)
                            ->where('term', '<=', $currentTerm->term);
                    })->orderBy('year', 'asc')->orderBy('term', 'asc')->get();

        $students = $sponsor->students()->get();

        return view('sponsors.portal.assessment.assessment-report-card-timeline', compact('sponsor', 'terms', 'currentTerm', 'students'));
    }

    public function feesIndex(){
        $sponsor = auth('sponsor')->user();
        $students = $sponsor->students()->with(['currentClassRelation.grade'])->get();

        // If only one student, redirect directly to their fees page
        if ($students->count() === 1) {
            return redirect()->route('sponsor.fees.student', $students->first()->id);
        }

        // For sponsors: Get actual current term based on date
        $currentTerm = $this->getActualCurrentTerm();
        $school_data = SchoolSetup::first();

        return view('sponsors.portal.fees.index', compact('students', 'currentTerm', 'school_data'));
    }

    /**
     * Show individual student fees page
     */
    public function feesStudentShow($studentId)
    {
        $sponsor = auth('sponsor')->user();

        // Security: Ensure student belongs to this sponsor
        $student = $sponsor->students()
            ->where('id', $studentId)
            ->with(['currentClassRelation.grade'])
            ->firstOrFail();

        // For sponsors: Get actual current term based on date
        $currentTerm = $this->getActualCurrentTerm();

        // For sponsors: Get ALL terms (including closed) so they can view historical data
        $terms = Term::orderBy('year', 'asc')
                     ->orderBy('term', 'asc')
                     ->get();

        $school_data = SchoolSetup::first();

        return view('sponsors.portal.fees.show', compact('student', 'terms', 'currentTerm', 'school_data'));
    }

    /**
     * Get individual student's fees data for a term (AJAX)
     */
    public function getStudentFeesData($studentId, BalanceService $balanceService, PaymentService $paymentService)
    {
        $sponsor = auth('sponsor')->user();
        $selectedTermId = session('selected_term_id', $this->getActualCurrentTerm()->id);
        $currentTerm = Term::find($selectedTermId);

        // Security: Ensure student belongs to this sponsor
        $student = $sponsor->students()
            ->where('id', $studentId)
            ->with(['currentClassRelation.grade'])
            ->firstOrFail();

        // Get balance breakdown (BalanceService expects year, not term ID)
        $student->feeBalance = $balanceService->getStudentBalance($student->id, $currentTerm->year);

        // Get invoice for the year (invoices are annual, not per-term)
        $student->invoice = StudentInvoice::forStudent($student->id)
            ->forYear($currentTerm->year)
            ->active()
            ->with('items')
            ->first();

        // Get payments for the term
        $student->payments = $paymentService->getStudentPayments($student->id, $selectedTermId);

        return view('sponsors.portal.fees.student-term', compact('student', 'currentTerm', 'selectedTermId'));
    }

    public function getFeesTermData(BalanceService $balanceService, PaymentService $paymentService){
        $selectedTermId = session('selected_term_id', $this->getActualCurrentTerm()->id);
        $sponsor = auth('sponsor')->user();
        $currentTerm = Term::find($selectedTermId);

        // Get children enrolled in the selected term
        $children = $sponsor->students()->whereHas('terms', function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId);
        })->get();

        // Load fee data for each child
        foreach ($children as $child) {
            // Get balance breakdown (BalanceService expects year, not term ID)
            $child->feeBalance = $balanceService->getStudentBalance($child->id, $currentTerm->year);

            // Get invoice for the year (invoices are annual, not per-term)
            $child->invoice = StudentInvoice::forStudent($child->id)
                ->forYear($currentTerm->year)
                ->active()
                ->with('items')
                ->first();

            // Get payments for the term
            $child->payments = $paymentService->getStudentPayments($child->id, $selectedTermId);
        }

        return view('sponsors.portal.fees.fees-term', compact('children', 'currentTerm', 'selectedTermId'));
    }

    /**
     * Generate fee statement PDF for a student.
     *
     * @param Student $student
     * @param BalanceService $balanceService
     * @return \Illuminate\Http\Response
     */
    public function statementPdf(Student $student, BalanceService $balanceService)
    {
        // Verify the student belongs to the current sponsor
        $sponsor = auth('sponsor')->user();
        if (!$sponsor->students()->where('id', $student->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        // Get school data
        $school = SchoolSetup::first();

        // Get all invoices for student (across all years, not cancelled)
        $invoices = StudentInvoice::forStudent($student->id)
            ->active()
            ->with(['items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all payments for student (not voided)
        $payments = FeePayment::forStudent($student->id)
            ->notVoided()
            ->with('invoice')
            ->orderBy('payment_date', 'desc')
            ->get();

        // Calculate overall balance
        $balance = $balanceService->getStudentBalance($student->id);

        // Generate PDF
        $pdf = Pdf::loadView('sponsors.portal.fees.statement-pdf', [
            'student' => $student,
            'sponsor' => $sponsor,
            'school' => $school,
            'invoices' => $invoices,
            'payments' => $payments,
            'balance' => $balance,
            'generatedAt' => now(),
        ]);

        return $pdf->stream("Statement-{$student->student_number}.pdf");
    }

    /**
     * Show students list or redirect to single student profile
     */
    public function studentsIndex()
    {
        $sponsor = auth('sponsor')->user();
        $students = $sponsor->students()->with([
            'currentClassRelation.grade',
        ])->get();

        // If only one student, redirect directly to their profile
        if ($students->count() === 1) {
            return redirect()->route('sponsor.student.show', $students->first()->id);
        }

        $school_data = SchoolSetup::first();

        return view('sponsors.portal.students.index', compact('students', 'school_data'));
    }

    /**
     * Show individual student profile
     */
    public function studentShow($studentId)
    {
        $sponsor = auth('sponsor')->user();

        // Security: Ensure student belongs to this sponsor
        $student = $sponsor->students()
            ->where('id', $studentId)
            ->with([
                'currentClassRelation.grade',
                'currentClassRelation.teacher',
                'bookAllocations.book',
                'bookAllocations.grade',
                'studentMedicals',
                'studentbehaviour',
                'psle',
                'jce',
                'absentDays',
            ])
            ->firstOrFail();

        $school_data = SchoolSetup::first();
        $currentTerm = $this->getActualCurrentTerm();

        return view('sponsors.portal.students.show', compact('student', 'school_data', 'currentTerm'));
    }

    /**
     * Show sponsor profile page
     */
    public function profile()
    {
        $sponsor = auth('sponsor')->user();
        $school_data = SchoolSetup::first();

        return view('sponsors.portal.profile.index', compact('sponsor', 'school_data'));
    }

    /**
     * Update sponsor profile information
     */
    public function updateProfile(Request $request)
    {
        $sponsor = auth('sponsor')->user();
        $emailChanged = $sponsor->email !== $request->email;

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:sponsors,email,' . $sponsor->id,
        ]);

        $sponsor->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ]);

        // If email changed, log out and redirect to login
        if ($emailChanged) {
            auth('sponsor')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('sponsor.login')->with('message', 'Email updated successfully. Please log in with your new email.');
        }

        return redirect()->route('sponsor.profile')->with('message', 'Profile updated successfully.');
    }

    /**
     * Update sponsor password
     */
    public function updatePassword(Request $request)
    {
        $sponsor = auth('sponsor')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check current password
        if (!password_verify($request->current_password, $sponsor->password)) {
            return redirect()->route('sponsor.profile')->with('error', 'Current password is incorrect.');
        }

        $sponsor->update([
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('sponsor.profile')->with('message', 'Password updated successfully.');
    }

}
