<?php

namespace App\Http\Controllers;

use App\Helpers\TermHelper;
use App\Models\CriteriaBasedStudentTest;
use App\Models\CriteriaBasedTest;
use Illuminate\Http\Request;
use App\Models\GradeSubject;
use App\Models\Grade;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Klass;
use Illuminate\Support\Facades\Log;
use Exception;

class CriteriaBasedTestController extends Controller{

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(){
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;
        $grades = Grade::where('term_id',$selectedTermId)->get();
        $classes = Klass::where('term_id',$selectedTermId)->get();

        $currentTerm = TermHelper::getCurrentTerm();
        $terms = StudentController::terms();

        return view('classes.criteria-tests-index',['classes' => $classes,'grades' => $grades,'currentTerm' => $currentTerm,'terms' => $terms]);
    }

    public function getCriteriaBasedTestsByTermAndGrade($gradeId){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        try {
            $tests = CriteriaBasedTest::with(['subject.subject', 'grade'])
                ->where('term_id', $selectedTermId)
                ->where('grade_id', $gradeId)
                ->whereHas('subject.subject')
                ->get();

            // Get all available subjects for copying (Reception type with components)
            // Same query as createTest() method - get all Reception subjects with components
            $availableSubjects = GradeSubject::with(['subject', 'grade'])
                ->where('term_id', $selectedTermId)
                ->where('type', 1) // Reception/Pre-school type
                ->whereHas('subject', function($query) {
                    $query->where('components', true);
                })
                ->get()
                ->filter(function($gradeSubject) {
                    return $gradeSubject->subject !== null;
                })
                ->map(function($gradeSubject) {
                    return [
                        'id' => $gradeSubject->id,
                        'name' => $gradeSubject->grade->name . ' | ' . $gradeSubject->subject->name,
                        'subject_name' => $gradeSubject->subject->name,
                        'grade_id' => $gradeSubject->grade_id
                    ];
                })
                ->values()
                ->toArray();

            if ($tests->isEmpty()) {
                return view('classes.criteria-based-tests-list', [
                    'groupedTests' => collect(),
                    'availableSubjects' => $availableSubjects
                ]);
            }

            $groupedTests = $tests->filter(function ($test) {
                return $test->subject && $test->subject->subject && $test->subject->subject->name;
            })->groupBy(function ($test) {
                return $test->subject->subject->name;
            })->map(function ($subjectTests) {
                return $subjectTests->sortBy(function ($test) {
                    return strtolower($test->type) === 'exam' ? PHP_INT_MAX : ($test->sequence ?? 0);
                });
            });

            return view('classes.criteria-based-tests-list', [
                'groupedTests' => $groupedTests,
                'availableSubjects' => $availableSubjects
            ]);
        } catch (Exception $e) {
            Log::error('Error loading criteria-based tests', [
                'grade_id' => $gradeId,
                'term_id' => $selectedTermId,
                'error' => $e->getMessage()
            ]);

            return view('classes.criteria-based-tests-list', [
                'groupedTests' => collect(),
                'availableSubjects' => [],
                'error' => 'An unexpected error occurred while loading tests.'
            ]);
        }
    }

    public function createTest(){
        $selectedTermId = session('selected_term_id',TermHelper::getCurrentTerm()->id);
        $subjects = GradeSubject::where('term_id',$selectedTermId)->where('type',1)->whereHas('subject', function($query){
            $query->where('components',true);
        })->get();

        $terms = StudentController::terms();
        $currentTerm = TermHelper::getCurrentTerm();
        $grades = Grade::where('term_id',$selectedTermId)->get();

        return view('assessment.shared.criteria-based-test-setup',['terms' => $terms,'subjects' => $subjects,'currentTerm' => $currentTerm,'grades' => $grades]);
    }

    public function addReceptionTest(Request $request){
        try {
            $validatedData = $request->validate([
                'sequence' => 'required|integer|min:1|max:5',
                'name' => 'required|string|max:255|min:3',
                'abbrev' => 'required|string|max:10|min:2',
                'subject' => 'required|exists:grade_subject,id',
                'type' => 'required|in:CA,Exam',
                'assessment' => 'required|boolean',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'term' => 'required|exists:terms,id',
                'grade_id' => 'required|exists:grades,id',
            ]);

            DB::beginTransaction();
            $duplicate = CriteriaBasedTest::where([
                'grade_subject_id' => $validatedData['subject'],
                'term_id' => $validatedData['term'],
                'sequence' => $validatedData['sequence'],
                'type' => $validatedData['type']
            ])->exists();

            if ($duplicate) {
                return redirect()->back()->with('error', "A {$validatedData['type']} test with sequence {$validatedData['sequence']} already exists for this subject and term.");
            }

            if (!GradeSubject::where('id', $validatedData['subject'])->where('grade_id', $validatedData['grade_id'])->exists()) {
                return redirect()->back()->with('error', 'The selected subject does not belong to the selected grade.');
            }

            $test = CriteriaBasedTest::create([
                'sequence' => $validatedData['sequence'],
                'name' => trim($validatedData['name']),
                'abbrev' => strtoupper(trim($validatedData['abbrev'])),
                'grade_subject_id' => $validatedData['subject'],
                'term_id' => $validatedData['term'],
                'grade_id' => $validatedData['grade_id'],
                'type' => $validatedData['type'],
                'assessment' => $validatedData['assessment'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->back()->with('message', "Test '{$test->name}' created successfully.");
        }catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function getCriteriaBaseTest($testId){
        try {
            if (!$testId || !is_numeric($testId)) {
                return redirect()->back()->withErrors('Invalid test ID provided.');
            }

            $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
            
            $test = CriteriaBasedTest::with(['subject.subject', 'grade'])
                ->where('id', $testId)
                ->where('term_id', $selectedTermId)
                ->firstOrFail();

            $subjects = GradeSubject::with('subject')
                ->where('term_id', $selectedTermId)
                ->whereHas('subject')
                ->orderBy('grade_id')
                ->get();

            $grades = Grade::where('term_id', $selectedTermId)
                ->where('active', true)
                ->orderBy('sequence')
                ->get();

            if ($subjects->isEmpty() || $grades->isEmpty()) {
                return redirect()->back()->withErrors('Required data not available for the current term.');
            }

            return view('assessment.shared.criteria-based-test-setup-update', [
                'test' => $test,
                'grades' => $grades,
                'subjects' => $subjects
            ]);

        }catch (Exception $e) {
            Log::error('Error loading criteria-based test', [
                'test_id' => $testId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->withErrors('An error occurred while loading the test.');
        }
    }

    public function updatedCriteriaBaseTest(Request $request, $testId){
        try {
            $validatedData = $request->validate([
                'sequence' => 'required|integer|min:1|max:5',
                'name' => 'required|string|max:255|min:3',
                'abbrev' => 'required|string|max:10|min:2',
                'subject' => 'required|exists:grade_subject,id',
                'type' => 'required|in:CA,Exam',
                'assessment' => 'required|boolean',
                'grade_id' => 'required|exists:grades,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $test = CriteriaBasedTest::findOrFail($testId);
            DB::beginTransaction();
            $duplicate = CriteriaBasedTest::where([
                'grade_subject_id' => $validatedData['subject'],
                'term_id' => $test->term_id,
                'sequence' => $validatedData['sequence'],
                'type' => $validatedData['type']
            ])->where('id', '!=', $testId)->exists();

            if ($duplicate) {
                return redirect()->back()->with('error', "A {$validatedData['type']} test with sequence {$validatedData['sequence']} already exists for this subject and term.");
            }

            if (!GradeSubject::where('id', $validatedData['subject'])->where('grade_id', $validatedData['grade_id'])->exists()) {
                return redirect()->back()->with('error', "The selected subject does not belong to the selected grade.");
            }

            $test->update([
                'sequence' => $validatedData['sequence'],
                'name' => trim($validatedData['name']),
                'abbrev' => strtoupper(trim($validatedData['abbrev'])),
                'grade_subject_id' => $validatedData['subject'],
                'type' => $validatedData['type'],
                'assessment' => $validatedData['assessment'],
                'grade_id' => $validatedData['grade_id'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'updated_by' => auth()->id(),
            ]);
            DB::commit();
            return redirect()->back()->with('message', "Test '{$test->name}' updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Test update failed', [
                'test_id' => $testId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }


    public function storeCriteriaAssessment(Request $request){
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.*.*' => 'required|integer|exists:grade_options,id',
        ]);

        DB::transaction(function () use ($validated, $request) {
            foreach ($validated['scores'] as $subjectId => $components) {
                foreach ($components as $componentId => $tests) {
                    foreach ($tests as $testId => $gradeOptionId) {
                        CriteriaBasedStudentTest::updateOrCreate(
                            [
                                'grade_subject_id' => $subjectId,
                                'component_id' => $componentId,
                                'reception_test_id' => $testId,
                                'student_id' => $request->input('student_id'),
                            ],
                            [
                                'grade_option_id' => $gradeOptionId,
                                'klass_id' => $request->input('klass_id'),
                                'term_id' => $request->input('term_id'),
                                'grade_id' => $request->input('grade_id')
                            ]
                        );
                    }
                }
            }
        });
        return redirect()->back()->with('message', 'Assessments saved successfully.');
    }

    public function deleteCriteriaBaseTest($testId){
        $test = CriteriaBasedTest::findOrFail($testId);
        $associatedGradingCount = CriteriaBasedStudentTest::where('criteria_based_test_id', $testId)->count();

        if ($associatedGradingCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete test. It is associated with existing gradings.Remove assessment then delete!');
        }
        $test->delete();
        return redirect()->with('message', 'Test deleted successfully.');
    }

    public function copy(Request $request){
        $request->validate([
            'test_id' => 'required|integer|exists:criteria_based_tests,id',
            'target_subject_id' => 'required|integer|exists:grade_subject,id',
        ]);

        DB::beginTransaction();
        try {
            $sourceTest = CriteriaBasedTest::where('id', $request->test_id)->lockForUpdate()->first();

            if (!$sourceTest) {
                throw new Exception('Source test not found.');
            }

            $targetSubject = GradeSubject::where('id', $request->target_subject_id)->lockForUpdate()->first();

            if (!$targetSubject) {
                throw new Exception('Target subject not found.');
            }

            if ($sourceTest->grade_id !== $targetSubject->grade_id) {
                throw new Exception('Cannot copy test across different grades.');
            }

            $existingTest = CriteriaBasedTest::where('grade_subject_id', $targetSubject->id)
                ->where('sequence', $sourceTest->sequence)
                ->where('type', $sourceTest->type)
                ->where('term_id', $sourceTest->term_id)
                ->lockForUpdate()
                ->first();

            if ($existingTest) {
                DB::rollBack();
                return redirect()->back()->with('error', "A {$sourceTest->type} test with sequence {$sourceTest->sequence} already exists for the target subject.");
            }

            $newTest = CriteriaBasedTest::create([
                'sequence' => $sourceTest->sequence,
                'name' => $sourceTest->name,
                'abbrev' => $sourceTest->abbrev,
                'grade_subject_id' => $targetSubject->id,
                'term_id' => $sourceTest->term_id,
                'grade_id' => $sourceTest->grade_id,
                'type' => $sourceTest->type,
                'assessment' => $sourceTest->assessment,
                'start_date' => $sourceTest->start_date,
                'end_date' => $sourceTest->end_date,
            ]);

            DB::commit();

            Log::info('Criteria-based test copied successfully', [
                'source_test_id' => $sourceTest->id,
                'new_test_id' => $newTest->id,
                'target_subject_id' => $targetSubject->id,
                'copied_by' => auth()->id(),
            ]);

            return redirect()->back()->with('message', "Test '{$sourceTest->name}' copied successfully to {$targetSubject->subject->name}.");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to copy criteria-based test', [
                'test_id' => $request->test_id,
                'target_subject_id' => $request->target_subject_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to copy test: ' . $e->getMessage());
        }
    }

    public function storeCriteriaTestAssessment(Request $request){
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'term_id' => 'required|exists:terms,id',
            'klass_id' => 'required|exists:klasses,id',
            'grade_id' => 'required|exists:grades,id',
            'scores' => 'required|array',
            'scores.*.*.*' => 'required|exists:grade_options,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['scores'] as $subjectId => $components) {
                foreach ($components as $componentId => $tests) {
                    foreach ($tests as $testId => $gradeOptionId) {
                        CriteriaBasedStudentTest::updateOrCreate(
                            [
                                'grade_subject_id' => $subjectId,
                                'component_id' => $componentId,
                                'criteria_based_test_id' => $testId,
                                'student_id' => $validated['student_id'],
                            ],
                            [
                                'grade_option_id' => $gradeOptionId,
                                'klass_id' => $validated['klass_id'],
                                'term_id' => $validated['term_id'],
                                'grade_id' => $validated['grade_id'],
                            ]
                        );
                    }
                }
            }
        });

        return redirect()->back()->with('message', 'Assessments saved successfully.');
    }


    public function showPreHTMLReportCard($id){
        $student = Student::with('criteriaBasedStudentTests', 'overallComments')->findOrFail($id);
        $class = $student->currentClassRelation->first();
        $gradeId = $class->grade_id;
    
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
    
        $school_head = User::where('position', 'School Head')->first();
        $currentTerm = TermHelper::getCurrentTerm();
        $school_setup = SchoolSetup::first();
    
        $nextTermStartDate = $this->getNextTermStartDate($currentTerm->term, $currentTerm->year);
    
        $gradeSubjects = GradeSubject::with(['components', 'gradeOptionSets.gradeOptions', 'criteriaBasedTests'])
            ->where('term_id', $selectedTermId)
            ->where('grade_id', $gradeId)
            ->get();
    
        $overallComments = $student->overallComments->where('term_id', $selectedTermId)->first();
        $classTeacherRemarks = $overallComments->class_teacher_remarks ?? 'No remarks provided.';
        $headTeachersRemarks = $overallComments->school_head_remarks ?? 'No remarks provided.';
    
        $data = [
            'student' => $student,
            'currentClass' => $class,
            'currentTerm' => $currentTerm,
            'school_setup' => $school_setup,
            'school_head' => $school_head,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'gradeSubjects' => $gradeSubjects,
            'nextTermStartDate' => $nextTermStartDate
        ];

        return view('assessment.pre.pre-html-report-card',$data);
    }

    public function showPrePDFReportCard($id){
        $student = Student::with('criteriaBasedStudentTests', 'overallComments')->findOrFail($id);
        $class = $student->currentClassRelation->first();
        $gradeId = $class->grade_id;
    
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
    
        $school_head = User::where('position', 'School Head')->first();
        $currentTerm = TermHelper::getCurrentTerm();
        $school_setup = SchoolSetup::first();
    
        $nextTermStartDate = $this->getNextTermStartDate($currentTerm->term, $currentTerm->year);

        $gradeSubjects = GradeSubject::with(['components', 'gradeOptionSets.gradeOptions', 'criteriaBasedTests'])
            ->where('term_id', $selectedTermId)
            ->where('grade_id', $gradeId)
            ->get();
    
        $overallComments = $student->overallComments->where('term_id', $selectedTermId)->first();
        $classTeacherRemarks = $overallComments->class_teacher_remarks ?? 'No remarks provided.';
        $headTeachersRemarks = $overallComments->school_head_remarks ?? 'No remarks provided.';
    
        $data = [
            'student' => $student,
            'currentClass' => $class,
            'currentTerm' => $currentTerm,
            'school_setup' => $school_setup,
            'school_head' => $school_head,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'gradeSubjects' => $gradeSubjects,
            'nextTermStartDate' => $nextTermStartDate
        ];
    
        $pdf = PDF::loadView('assessment.pre.pre-student-pdf-report-card', $data)->setPaper('a5');
        return $pdf->stream('student-report-cards.pdf');
    }

    public function generateRECClassReportCardsPDF($classId){
        $pdf = $this->generateEmailRECClassListReportCards($classId);
        return $pdf->stream('class-report-cards.pdf');
    }

    public function generateEmailRECClassListReportCards($classId){
        ini_set('memory_limit', '512M');
        set_time_limit(300); 
    
        $class = Klass::with(['students.criteriaBasedStudentTests', 'students.overallComments'])->findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();
        $nextTermStartDate = $this->getNextTermStartDate($currentTerm->term, $currentTerm->year);
    
        $reportCardsData = [];
    
        foreach ($class->students as $student) {
            if (!$student->pivot->active || $student->pivot->term_id != $currentTerm->id) {
                continue;
            }
    
            $class = $student->currentClassRelation->first();
            $gradeId = $class->grade_id;
            $gradeSubjects = GradeSubject::with(['components', 'gradeOptionSets.gradeOptions', 'criteriaBasedTests'])
                ->where('term_id', $selectedTermId)
                ->where('grade_id', $gradeId)
                ->get();
    
            $overallComments = $student->overallComments->where('term_id', $selectedTermId)->first();
            $classTeacherRemarks = $overallComments->class_teacher_remarks ?? 'No remarks provided.';
            $headTeachersRemarks = $overallComments->school_head_remarks ?? 'No remarks provided.';
    
            $reportCardsData[] = [
                'student' => $student,
                'gradeSubjects' => $gradeSubjects,
                'class' => $class,
                'classTeacherRemarks' => $classTeacherRemarks,
                'headTeachersRemarks' => $headTeachersRemarks,
            ];
        }
    
        $data = [
            'reportCards' => $reportCardsData,
            'school_setup' => $school_setup,
            'school_head' => $school_head,
            'nextTermStartDate' => $nextTermStartDate,
            'currentTerm' => $currentTerm
        ];
    
        $pdf = PDF::loadView('assessment.pre.all-pre-pdf-report-card', $data);
        $pdf->setPaper('A5', 'portrait');
        $filename = strtolower($class->name . '_term_' . $currentTerm->term . '_report_cards.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }
    
    public function generateRECPrimaryReportCardPDF($id){
        $student = Student::with('criteriaBasedStudentTests', 'overallComments')->findOrFail($id);
        $class = $student->currentClassRelation->first();
        $gradeId = $class->grade_id;
    
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
    
        $school_head = User::where('position', 'School Head')->first();
        $currentTerm = TermHelper::getCurrentTerm();
        $school_setup = SchoolSetup::first();
    
        $nextTermStartDate = $this->getNextTermStartDate($currentTerm->term, $currentTerm->year);
        $gradeSubjects = GradeSubject::with(['components', 'gradeOptionSets.gradeOptions', 'criteriaBasedTests'])
            ->where('term_id', $selectedTermId)
            ->where('grade_id', $gradeId)
            ->get();
    
        $overallComments = $student->overallComments->where('term_id', $selectedTermId)->first();
        $classTeacherRemarks = $overallComments->class_teacher_remarks ?? 'No remarks provided.';
        $headTeachersRemarks = $overallComments->school_head_remarks ?? 'No remarks provided.';
    
        $data = [
            'student' => $student,
            'currentClass' => $class,
            'currentTerm' => $currentTerm,
            'school_setup' => $school_setup,
            'school_head' => $school_head,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'gradeSubjects' => $gradeSubjects,
            'nextTermStartDate' => $nextTermStartDate
        ];

        $pdf = PDF::loadView('assessment.pre.pre-student-pdf-report-card', $data)->setPaper('a5');
        $filename = strtolower($student->first_name . '_' . $student->last_name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }


    public function getNextTermStartDate(int $currentTerm, int $currentYear){
        $nextTerm = Term::where('year', $currentYear)
                        ->where('term', '>', $currentTerm) 
                        ->orderBy('start_date', 'asc')
                        ->first();
        return $nextTerm ? $nextTerm->start_date : null;
    }

    public function getNextAcademicYearStartDate(){
        $currentYear = now()->year; 
        $nextYear = $currentYear + 1;
        $nextYearFirstTerm = Term::where('year', $nextYear)->orderBy('start_date', 'asc') ->first(); 
        return $nextYearFirstTerm ? $nextYearFirstTerm->start_date->toDateString() : null;
    }
}
