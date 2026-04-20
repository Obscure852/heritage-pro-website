<?php

namespace App\Http\Controllers\Assessment;

use App\Helpers\AssessmentHelper;
use App\Helpers\TermHelper;
use App\Exports\JuniorGradeHousePerformanceExport;
use App\Exports\JuniorGradeHousePerformanceSimpleExport;
use App\Exports\JuniorHouseOverallPerformanceExport;
use App\Exports\JuniorHouseOverallPerformanceSimpleExport;
use App\Exports\JuniorSubjectsHouseStatisticsExport;
use App\Exports\JuniorSubjectsHouseStatisticsSimpleExport;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\User;
use App\Models\KlassSubject;
use App\Models\Klass;
use App\Models\GradeSubject;
use App\Models\SubjectComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClassPerformanceExport;
use App\Models\Grade;
use App\Models\House;
use App\Models\OptionalSubject;
use App\Models\OverallGradingMatrix;
use App\Models\PSLE;
use App\Models\StudentTest;
use App\Models\Term;
use App\Models\Test;
use Exception;
use Str;

/**
 * Junior Assessment Controller
 *
 * Handles all assessment functionality specific to Junior Secondary Schools (CJSS).
 * Includes report card generation, analysis reports, and junior-specific calculations.
 */
class JuniorAssessmentController extends BaseAssessmentController
{
    /**
     * Generate PDF report card for junior student (Version 1)
     */
    public function pdfReportCardJunior($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        $student = Student::with(['tests', 'overallComments'])->findOrFail($id);
        $currentClass = $student->currentClass();
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();
        $currentTerm = TermHelper::getCurrentTerm();

        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);

        $subjects = $student->tests->pluck('subject')->unique('id');
        $scores = [];

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        $mandatoryPoints = 0;
        $optionalPoints = [];
        $corePoints = [];

        foreach ($subjects as $subject) {

            $examTest = $student->tests
                ->where('term_id', $selectedTermId)
                ->where('grade_subject_id', $subject->id)
                ->where('type', 'Exam')
                ->first();

            $caTest = $student->tests
                ->where('term_id', $selectedTermId)
                ->where('grade_subject_id', $subject->id)
                ->where('type', 'CA')
                ->first();

            if ($examTest) {
                $subjectComment = SubjectComment::where('student_id', $student->id)
                    ->where('grade_subject_id', $subject->id)
                    ->first();

                $subjectType = $examTest->subject->type;
                $subjectMandatory = $examTest->subject->mandatory;


                if ($subjectMandatory && $subjectType) {
                    $mandatoryPoints += $examTest->pivot->points;
                }

                if (!$subjectMandatory && !$subjectType) {
                    $optionalPoints[] = $examTest->pivot->points;
                }

                if (!$subjectMandatory && $subjectType) {
                    $corePoints[] = $examTest->pivot->points;
                }

                $scores[] = [
                    'subject' => $subject->subject->name ?? '',
                    'points' => $examTest->pivot->points ?? 0,
                    'score' => $examTest->pivot->score ?? 0,
                    'percentage' => $examTest->pivot->percentage ?? 0,
                    'grade' => $examTest->pivot->grade ?? '',
                    'comments' => $subjectComment->remarks ?? 'N/A',
                    'caAverage' => $caTest->pivot->avg_score ?? 0,
                    'caAverageGrade' => $caTest->pivot->avg_grade ?? '',
                ];
            }
        }
        rsort($optionalPoints);
        rsort($corePoints);
        $totalPoints = $mandatoryPoints;

        if (!empty($optionalPoints)) {
            $totalPoints += $optionalPoints[0];
        }

        for ($i = 0; $i < min(2, count($corePoints)); $i++) {
            $totalPoints += $corePoints[$i];
        }

        $grade = $this->determineGrade($totalPoints, $currentClass);

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,
            'school_setup' => $school_setup,
            'school_head' => $school_head,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'scores' => $scores,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,
        ];

        $reportCard = PDF::loadView('assessment.junior.report-card-pdf-junior', $data);
        return $reportCard->stream('junior-student-report-card.pdf');
    }

    /**
     * Generate PDF report card for junior student (Version 2)
     */
    public function pdfReportCardJunior2($id)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)
                    ->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            }
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $studentRankings = $this->calculateClassRankings($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $position = array_search($id, array_column($studentRankings, 'id')) + 1;

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);
        $subjects = $student->tests->pluck('subject')->unique();

        $scores = [];
        $isForeigner = $student->nationality !== 'Motswana';

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints($student, $subjects, $selectedTermId, $isForeigner, 'Exam');

        $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
        $grade = $this->determineGrade($totalPoints, $currentClass);

        foreach ($subjects as $subject) {
            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);

            $examTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'Exam')->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'CA')->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'points' => $points ?? 0,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
            ];
        }

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,
            'position' => $position,
            'classAverage' => $classAverage,
            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,
            'scores' => $scores,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,
        ];

        $reportCard = PDF::loadView('assessment.junior.report-card-pdf-junior', $data);
        return $reportCard->stream('junior-student-report-card.pdf');
    }

    /**
     * Generate HTML report card for junior student (Version 3)
     */
    public function htmlReportCardJunior3($id)
    {

        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)
                    ->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            }
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $gradeId = $currentClass->grade->id;
        $currentClassId = $currentClass->id;

        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $studentRankings = $this->calculateClassRankings($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $classPosition = $this->getStudentPosition($studentRankings, $id);

        $gradeRankings = $this->calculateGradeRankings($gradeId, $selectedTermId);
        $gradeAverage = $this->calculateGradeAverage($gradeRankings);
        $gradePosition = $this->getStudentGradePosition($gradeRankings, $id);
        $totalStudentsInGrade = count($gradeRankings);

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);
        $coreSubjectIds = KlassSubject::where('klass_id', $currentClassId)->where('term_id', $selectedTermId)->pluck('grade_subject_id');
        $optionalSubjectIds = DB::table('student_optional_subjects as sos')
            ->join('optional_subjects as os', 'sos.optional_subject_id', '=', 'os.id')
            ->where('sos.student_id', $student->id)
            ->where('sos.term_id', $selectedTermId)
            ->pluck('os.grade_subject_id');

        $gradeSubjects = GradeSubject::whereIn('grade_subject.id', $coreSubjectIds->merge($optionalSubjectIds))
                                    ->where('grade_subject.term_id', $selectedTermId)
                                    ->with('subject')
                                    ->leftJoin('subjects', 'grade_subject.subject_id', '=', 'subjects.id')
                                    ->orderByRaw('CASE WHEN grade_subject.sequence IS NULL OR grade_subject.sequence = 0 THEN 9999 ELSE grade_subject.sequence END ASC')
                                    ->orderBy('subjects.name', 'asc')
                                    ->select('grade_subject.*')
                                    ->get();

        $scores = [];
        $isForeigner = $student->nationality !== 'Motswana';
        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments->where('term_id', $selectedTermId)->first()->school_head_remarks ?? 'No remarks provided.';
        list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints(
            $student, $gradeSubjects, $selectedTermId, $isForeigner, 'Exam'
        );

        $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
        $grade = $this->determineGrade($totalPoints, $currentClass);

        foreach ($gradeSubjects as $gradeSubject) {
            try {
                $examTest = $student->tests
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->where('type', 'Exam')
                    ->first();

                $caTest = $student->tests
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->where('type', 'CA')
                    ->sortByDesc('created_at')
                    ->first();

                $subjectComment = $student->subjectComments
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->first();

                $points = $this->getSubjectPoints($student, $gradeSubject, $selectedTermId);
                $klassSubject = KlassSubject::where('grade_subject_id', $gradeSubject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('klass_id', $currentClassId)
                    ->first();

                $teacherName = 'N/A';

                if ($klassSubject && $klassSubject->user_id) {
                    $teacher = User::find($klassSubject->user_id);
                    $teacherName = $teacher ? $teacher->lastname : 'N/A';
                } else {
                    $studentOptionalSubject = DB::table('student_optional_subjects')
                        ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                        ->where('student_optional_subjects.student_id', $student->id)
                        ->where('student_optional_subjects.term_id', $selectedTermId)
                        ->where('student_optional_subjects.klass_id', $currentClassId)
                        ->where('optional_subjects.grade_subject_id', $gradeSubject->id)
                        ->first();

                    if ($studentOptionalSubject && $studentOptionalSubject->user_id) {
                        $teacher = User::find($studentOptionalSubject->user_id);
                        $teacherName = $teacher ? $teacher->lastname : 'N/A';
                    }
                }

                $scores[] = [
                    'subject' => $gradeSubject->subject->name,
                    'points' => $points ?? 0,
                    'score' => $examTest ? $examTest->pivot->score : null,
                    'percentage' => $examTest ? $examTest->pivot->percentage : null,
                    'grade' => $examTest ? $examTest->pivot->grade : '',
                    'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                    'caAverage' => $caTest ? $caTest->pivot->avg_score : null,
                    'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                    'teacher' => $teacherName,
                    'has_score' => !empty($examTest) || !empty($caTest),
                ];

            } catch (Exception $e) {
                Log::error("Error processing subject {$gradeSubject->subject->name} for student {$student->id}: " . $e->getMessage());
                $scores[] = [
                    'subject' => $gradeSubject->subject->name,
                    'points' => 0,
                    'score' => null,
                    'percentage' => null,
                    'grade' => '',
                    'comments' => 'N/A',
                    'caAverage' => null,
                    'caAverageGrade' => '',
                    'teacher' => 'N/A',
                    'has_score' => false,
                ];
            }
        }

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,

            'classPosition' => $classPosition,
            'classAverage' => round($classAverage, 2),
            'totalStudentsInClass' => count($allStudents),

            'gradePosition' => $gradePosition,
            'gradeAverage' => round($gradeAverage, 2),
            'totalStudentsInGrade' => $totalStudentsInGrade,
            'gradeName' => $currentClass->grade->name,

            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,

            'scores' => $scores,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,

            'position' => $classPosition,
        ];

        return view('assessment.junior.report-card-html-junior', $data);
    }

    /**
     * Generate PDF report card for junior student (Version 3)
     */ 

    public function pdfReportCardJunior3($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)
                    ->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            }
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $gradeId = $currentClass->grade->id;
        $currentClassId = $currentClass->id;

        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $studentRankings = $this->calculateClassRankings($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $classPosition = $this->getStudentPosition($studentRankings, $id);

        $gradeRankings = $this->calculateGradeRankings($gradeId, $selectedTermId);
        $gradeAverage = $this->calculateGradeAverage($gradeRankings);
        $gradePosition = $this->getStudentGradePosition($gradeRankings, $id);
        $totalStudentsInGrade = count($gradeRankings);

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);
        $coreSubjectIds = KlassSubject::where('klass_id', $currentClassId)->where('term_id', $selectedTermId)->pluck('grade_subject_id');

        $optionalSubjectIds = DB::table('student_optional_subjects as sos')
            ->join('optional_subjects as os', 'sos.optional_subject_id', '=', 'os.id')
            ->where('sos.student_id', $student->id)
            ->where('sos.term_id', $selectedTermId)
            ->pluck('os.grade_subject_id');

        $gradeSubjects = GradeSubject::whereIn('grade_subject.id', $coreSubjectIds->merge($optionalSubjectIds))
            ->where('grade_subject.term_id', $selectedTermId)
            ->with('subject')
            ->leftJoin('subjects', 'grade_subject.subject_id', '=', 'subjects.id')
            ->orderByRaw('CASE WHEN grade_subject.sequence IS NULL OR grade_subject.sequence = 0 THEN 9999 ELSE grade_subject.sequence END ASC')
            ->orderBy('subjects.name', 'asc')
            ->select('grade_subject.*')
            ->get();

        $scores = [];
        $isForeigner = $student->nationality !== 'Motswana';

        $classTeacherRemarks = $student->overallComments->where('term_id', $selectedTermId)->first()->class_teacher_remarks ?? 'No remarks provided.';
        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        list($mandatoryPoints, $optionalPoints, $corePoints) = AssessmentHelper::calculatePoints(
            $student, $gradeSubjects, $selectedTermId, $isForeigner
        );

        $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
        $grade = AssessmentHelper::determineGrade($totalPoints, $currentClass);

        foreach ($gradeSubjects as $gradeSubject) {
            try {
                $subjectResult = AssessmentHelper::calculateSubjectScoresAnalysis(
                    $student, $gradeSubject, $selectedTermId, $gradeId
                );

                $klassSubject = KlassSubject::where('grade_subject_id', $gradeSubject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('klass_id', $currentClassId)
                    ->first();

                $teacher = null;
                $teacherName = 'N/A';

                if ($klassSubject && $klassSubject->user_id) {
                    $teacher = User::find($klassSubject->user_id);
                    $teacherName = $teacher ? $teacher->lastname : 'N/A';
                } else {
                    $studentOptionalSubject = DB::table('student_optional_subjects')
                        ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                        ->where('student_optional_subjects.student_id', $student->id)
                        ->where('student_optional_subjects.term_id', $selectedTermId)
                        ->where('student_optional_subjects.klass_id', $currentClassId)
                        ->where('optional_subjects.grade_subject_id', $gradeSubject->id)
                        ->first();

                    if ($studentOptionalSubject && $studentOptionalSubject->user_id) {
                        $teacher = User::find($studentOptionalSubject->user_id);
                        $teacherName = $teacher ? $teacher->lastname : 'N/A';
                    }
                }

                $caTest = $student->tests
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->where('type', 'CA')
                    ->sortByDesc('created_at')
                    ->first();

                $subjectComment = $student->subjectComments
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->first();

                $scores[] = [
                    'subject' => $gradeSubject->subject->name,
                    'points' => $subjectResult['points'] ?? 0,
                    'score' => $subjectResult['score'] ?? null,
                    'percentage' => $subjectResult['percentage'] ?? null,
                    'grade' => $subjectResult['grade'] ?? '',
                    'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                    'caAverage' => $caTest ? $caTest->pivot->avg_score : null,
                    'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                    'teacher' => $teacherName,
                    'has_score' => !empty($subjectResult['score']) || !empty($caTest),
                ];

                Log::info("Report Card - Subject: {$gradeSubject->subject->name}, Score: " .
                         ($subjectResult['score'] ?? 'N/A') . ", Percentage: " .
                         ($subjectResult['percentage'] ?? 'N/A') . ", Grade: " .
                         ($subjectResult['grade'] ?? 'N/A'));

            } catch (Exception $e) {
                Log::error("Error calculating subject scores for {$gradeSubject->subject->name}: " . $e->getMessage());

                $scores[] = [
                    'subject' => $gradeSubject->subject->name,
                    'points' => 0,
                    'score' => null,
                    'percentage' => null,
                    'grade' => '',
                    'comments' => 'N/A',
                    'caAverage' => null,
                    'caAverageGrade' => '',
                    'teacher' => 'N/A',
                    'has_score' => false,
                ];
            }
        }

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,

            // Class-level statistics
            'classPosition' => $classPosition,
            'classAverage' => round($classAverage, 2),
            'totalStudentsInClass' => count($allStudents),

            // Grade-level statistics
            'gradePosition' => $gradePosition,
            'gradeAverage' => round($gradeAverage, 2),
            'totalStudentsInGrade' => $totalStudentsInGrade,
            'gradeName' => $currentClass->grade->name,

            // School and remarks
            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,

            // Scores and grades
            'scores' => $scores,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,

            'position' => $classPosition,
        ];

        $reportCard = PDF::loadView('assessment.junior.report-card-pdf-junior', $data);
        return $reportCard->stream('junior-student-report-card.pdf');
    }

            /**
     * Email report cards to all students in a junior class
     */
    public function generateEmailJuniorClassListReportCards($classId){
        $klass = Klass::with(['students.tests.subject', 'students.overallComments'])->findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();
        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);

        // Calculate rankings and class average
        $studentRankings = $this->calculateClassRankings($klass->students, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);

        $gradeId = $klass->grade->id;
        $gradeRankings = $this->calculateGradeRankings($gradeId, $selectedTermId);
        $gradeAverage = $this->calculateGradeAverage($gradeRankings);
        $totalStudentsInGrade = count($gradeRankings);

        $reportCardsData = [];

        foreach ($klass->students as $student) {
            if (!$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                continue;
            }

            $isForeigner = $student->nationality !== 'Motswana';
            $subjects = $student->tests->pluck('subject')->unique('id');
            $scores = [];

            list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints($student, $subjects, $selectedTermId, $isForeigner, 'Exam');
            $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
            $grade = $this->determineGrade($totalPoints, $student->currentClass());

            $classTeacherRemarks = $student->overallComments->where('term_id', $selectedTermId)
                ->first()->class_teacher_remarks ?? 'No remarks provided.';

            $headTeachersRemarks = $student->overallComments->where('term_id', $selectedTermId)
                ->first()->school_head_remarks ?? 'No remarks provided.';

            foreach ($subjects as $subject) {
                $scores[] = $this->calculateSubjectScores($student, $subject, $selectedTermId);
            }

            // Get student's position
            $position = $this->getStudentPosition($studentRankings, $student->id);
            $gradePosition = $this->getStudentGradePosition($gradeRankings, $student->id);

            $reportCardsData[] = [
                'student' => $student,
                'scores' => $scores,
                'totalPoints' => $totalPoints,
                'grade' => $grade,
                'class_name' => $student->currentClass()->name ?? '',
                'position' => $position,
                'classPosition' => $position,
                'total_students' => count($studentRankings),
                'totalStudentsInClass' => count($studentRankings),

                'class_average' => round($classAverage, 2),
                'classAverage' => round($classAverage, 2),

                'gradePosition' => $gradePosition,
                'totalStudentsInGrade' => $totalStudentsInGrade,
                'gradeAverage' => round($gradeAverage, 2),
                'gradeName' => $klass->grade->name,


                'term_start_date' => $student->currentTerm->start_date ?? '',
                'term_end_date' => $student->currentTerm->end_date ?? '',
                'nextTermStartDate' => $nextTermStartDate,
                'classTeacherRemarks' => $classTeacherRemarks,
                'headTeachersRemarks' => $headTeachersRemarks,
                'class_teacher_signature' => $student->currentClass()->teacher->signature_path ?? '',
                'head_teacher_signature' => $school_head->signature_path ?? '',
            ];
        }

        $data = [
            'reportCards' => $reportCardsData,
            'school_setup' => $school_setup,
            'school_head' => $school_head,
        ];

        $pdf = PDF::loadView('assessment.junior.report-card-class-junior', $data);
        $filename = strtolower($klass->name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    /**
     * Generate PDF report card for junior student
     */
    public function generateJuniorReportCardPDF($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)
                    ->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            }
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $gradeId = $currentClass->grade->id;
        $currentClassId = $currentClass->id;

        // Class-level statistics
        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $studentRankings = $this->calculateClassRankings($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $classPosition = $this->getStudentPosition($studentRankings, $id);

        // Grade-level statistics
        $gradeRankings = $this->calculateGradeRankings($gradeId, $selectedTermId);
        $gradeAverage = $this->calculateGradeAverage($gradeRankings);
        $gradePosition = $this->getStudentGradePosition($gradeRankings, $id);
        $totalStudentsInGrade = count($gradeRankings);

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);
        $subjects = $student->tests->pluck('subject')->unique();

        $scores = [];
        $isForeigner = $student->nationality !== 'Motswana';

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints($student, $subjects, $selectedTermId, $isForeigner, 'Exam');

        $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
        $grade = $this->determineGrade($totalPoints, $currentClass);

        foreach ($subjects as $subject) {
            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);

            $examTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'Exam')->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'CA')->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();

            // Teacher lookup
            $klassSubject = KlassSubject::where('grade_subject_id', $subject->id)
                ->where('term_id', $selectedTermId)
                ->where('klass_id', $currentClassId)
                ->first();

            $teacher = null;
            $teacherName = 'N/A';

            if ($klassSubject && $klassSubject->user_id) {
                $teacher = User::find($klassSubject->user_id);
                $teacherName = $teacher ? $teacher->lastname : 'N/A';
            } else {
                $studentOptionalSubject = DB::table('student_optional_subjects')
                    ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                    ->where('student_optional_subjects.student_id', $student->id)
                    ->where('student_optional_subjects.term_id', $selectedTermId)
                    ->where('student_optional_subjects.klass_id', $currentClassId)
                    ->where('optional_subjects.grade_subject_id', $subject->id)
                    ->first();

                if ($studentOptionalSubject && $studentOptionalSubject->user_id) {
                    $teacher = User::find($studentOptionalSubject->user_id);
                    $teacherName = $teacher ? $teacher->lastname : 'N/A';
                }
            }

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'points' => $points ?? 0,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'teacher' => $teacherName,
            ];
        }

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,

            'classPosition' => $classPosition,
            'classAverage' => round($classAverage, 2),
            'totalStudentsInClass' => count($allStudents),

            'gradePosition' => $gradePosition,
            'gradeAverage' => round($gradeAverage, 2),
            'totalStudentsInGrade' => $totalStudentsInGrade,
            'gradeName' => $currentClass->grade->name,

            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,

            'scores' => $scores,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,

            'position' => $classPosition,
        ];

        $pdf = PDF::loadView('assessment.junior.report-card-pdf-junior', $data);
        $filename = strtolower($student->first_name . '_' . $student->last_name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    /**
     * Generate report cards for all students in a class (Version 1)
     */
    public function generateClassReportCards1($classId){
        $klass = Klass::with(['students.tests.subject', 'students.overallComments'])->findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();
        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);

        $reportCardsData = [];

        foreach ($klass->students as $student) {
            if (!$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                continue;
            }

            $isForeigner = $student->nationality !== 'Motswana';
            $subjects = $student->tests->pluck('subject')->unique('id');
            $scores = [];

            list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints($student, $subjects, $selectedTermId, $isForeigner, 'Exam');
            $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
            $grade = $this->determineGrade($totalPoints, $student->currentClass());

            $classTeacherRemarks = $student->overallComments->where('term_id', $selectedTermId)
                ->first()->class_teacher_remarks ?? 'No remarks provided.';

            $headTeachersRemarks = $student->overallComments->where('term_id', $selectedTermId)
                ->first()->school_head_remarks ?? 'No remarks provided.';

            foreach ($subjects as $subject) {
                $scores[] = $this->calculateSubjectScores($student, $subject, $selectedTermId);
            }

            $reportCardsData[] = [
                'student' => $student,
                'scores' => $scores,
                'totalPoints' => $totalPoints,
                'grade' => $grade,
                'class_name' => $student->currentClass()->name ?? '',
                'position' => 'N/A',
                'total_students' => $student->currentClass()->count() ?? '',
                'class_average' => 'N/A',
                'term_start_date' => $student->currentTerm->start_date ?? '',
                'term_end_date' => $student->currentTerm->end_date ?? '',
                'nextTermStartDate' => $nextTermStartDate,
                'classTeacherRemarks' => $classTeacherRemarks,
                'headTeachersRemarks' => $headTeachersRemarks,
                'class_teacher_signature' => $student->currentClass()->teacher->signature_path ?? '',
                'head_teacher_signature' => $school_head->signature_path ?? '',
            ];
        }
        $data = [
            'reportCards' => $reportCardsData,
            'school_setup' => $school_setup,
            'school_head' => $school_head,
        ];
        $pdf = PDF::loadView('assessment.junior.report-card-class-junior', $data);
        return $pdf->stream('class-report-cards.pdf');
    }

    /**
     * Generate report cards for all students in a junior class (list version)
     */
    public function generateListClassReportCards($classId){
        $klass = Klass::with(['students.tests.subject', 'students.overallComments', 'students.subjectComments'])
                      ->findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();
        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);

        $studentRankings = $this->calculateClassRankings($klass->students, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);

        $gradeId = $klass->grade->id;
        $gradeRankings = $this->calculateGradeRankings($gradeId, $selectedTermId);
        $gradeAverage = $this->calculateGradeAverage($gradeRankings);
        $totalStudentsInGrade = count($gradeRankings);

        // Get core subjects for the class (fetch once for all students)
        $coreSubjectIds = KlassSubject::where('klass_id', $classId)
                                      ->where('term_id', $selectedTermId)
                                      ->pluck('grade_subject_id');

        // Get all optional subject enrollments for students in this class (fetch once)
        $studentOptionalSubjects = DB::table('student_optional_subjects as sos')
            ->join('optional_subjects as os', 'sos.optional_subject_id', '=', 'os.id')
            ->whereIn('sos.student_id', $klass->students->pluck('id'))
            ->where('sos.term_id', $selectedTermId)
            ->select('sos.student_id', 'os.grade_subject_id', 'os.user_id')
            ->get()
            ->groupBy('student_id');

        // Get all teachers for class subjects (fetch once)
        $klassSubjectTeachers = KlassSubject::where('klass_id', $classId)
            ->where('term_id', $selectedTermId)
            ->pluck('user_id', 'grade_subject_id');

        // Cache all teacher names
        $teacherIds = $klassSubjectTeachers->values()
            ->merge($studentOptionalSubjects->flatten()->pluck('user_id'))
            ->unique()
            ->filter();

        $teachers = User::whereIn('id', $teacherIds)
            ->pluck('lastname', 'id');

        $reportCardsData = [];

        foreach ($klass->students as $student) {
            if (!$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                continue;
            }

            $isForeigner = $student->nationality !== 'Motswana';

            // Get student's optional subject IDs
            $studentOptionalGradeSubjectIds = isset($studentOptionalSubjects[$student->id])
                ? collect($studentOptionalSubjects[$student->id])->pluck('grade_subject_id')
                : collect();

            // Combine core and optional subject IDs for this student
            $studentSubjectIds = $coreSubjectIds->merge($studentOptionalGradeSubjectIds)->unique();

            // Get grade subjects for this student, ordered by sequence
            $gradeSubjects = GradeSubject::whereIn('grade_subject.id', $studentSubjectIds)
                                        ->where('grade_subject.term_id', $selectedTermId)
                                        ->with('subject')
                                        ->leftJoin('subjects', 'grade_subject.subject_id', '=', 'subjects.id')
                                        ->orderByRaw('CASE WHEN grade_subject.sequence IS NULL OR grade_subject.sequence = 0 THEN 9999 ELSE grade_subject.sequence END ASC')
                                        ->orderBy('subjects.name', 'asc')
                                        ->select('grade_subject.*')
                                        ->get();

            $scores = [];

            $classTeacherRemarks = $student->overallComments->where('term_id', $selectedTermId)
                ->first()->class_teacher_remarks ?? 'No remarks provided.';

            $headTeachersRemarks = $student->overallComments->where('term_id', $selectedTermId)
                ->first()->school_head_remarks ?? 'No remarks provided.';

            list($mandatoryPoints, $optionalPoints, $corePoints) = $this->calculatePoints(
                $student, $gradeSubjects, $selectedTermId, $isForeigner, 'Exam'
            );
            $totalPoints = $mandatoryPoints + $optionalPoints + $corePoints;
            $grade = $this->determineGrade($totalPoints, $student->currentClass());

            foreach ($gradeSubjects as $gradeSubject) {
                try {
                    $subjectResult = AssessmentHelper::calculateSubjectScoresAnalysis(
                        $student, $gradeSubject, $selectedTermId, $gradeId
                    );

                    // Get teacher name efficiently
                    $teacherName = 'N/A';

                    // Check if it's a core subject
                    if (isset($klassSubjectTeachers[$gradeSubject->id])) {
                        $teacherId = $klassSubjectTeachers[$gradeSubject->id];
                        $teacherName = $teachers[$teacherId] ?? 'N/A';
                    } else {
                        // Check if it's an optional subject for this student
                        if (isset($studentOptionalSubjects[$student->id])) {
                            $optionalSubject = collect($studentOptionalSubjects[$student->id])
                                ->firstWhere('grade_subject_id', $gradeSubject->id);
                            if ($optionalSubject && $optionalSubject->user_id) {
                                $teacherName = $teachers[$optionalSubject->user_id] ?? 'N/A';
                            }
                        }
                    }

                    // Get CA test
                    $caTest = $student->tests
                        ->where('grade_subject_id', $gradeSubject->id)
                        ->where('type', 'CA')
                        ->sortByDesc('created_at')
                        ->first();

                    // Get subject comment
                    $subjectComment = $student->subjectComments
                        ->where('grade_subject_id', $gradeSubject->id)
                        ->first();

                    // Add subject to scores array (including subjects without scores)
                    $scores[] = [
                        'subject' => $gradeSubject->subject->name,
                        'points' => $subjectResult['points'] ?? 0,
                        'score' => $subjectResult['score'] ?? null,
                        'percentage' => $subjectResult['percentage'] ?? null,
                        'grade' => $subjectResult['grade'] ?? '',
                        'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                        'caAverage' => $caTest ? $caTest->pivot->avg_score : null,
                        'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                        'teacher' => $teacherName,
                        'has_score' => !empty($subjectResult['score']) || !empty($caTest),
                    ];

                } catch (Exception $e) {
                    Log::error("Error calculating subject scores for student {$student->id}, subject {$gradeSubject->subject->name}: " . $e->getMessage());

                    // Still add the subject even if there's an error
                    $scores[] = [
                        'subject' => $gradeSubject->subject->name,
                        'points' => 0,
                        'score' => null,
                        'percentage' => null,
                        'grade' => '',
                        'comments' => 'N/A',
                        'caAverage' => null,
                        'caAverageGrade' => '',
                        'teacher' => 'N/A',
                        'has_score' => false,
                    ];
                }
            }

            $classPosition = $this->getStudentPosition($studentRankings, $student->id);
            $gradePosition = $this->getStudentGradePosition($gradeRankings, $student->id);

            $reportCardsData[] = [
                'student' => $student,
                'scores' => $scores,
                'totalPoints' => $totalPoints,
                'grade' => $grade,
                'class_name' => $student->currentClass()->name ?? '',

                'position' => $classPosition,
                'classPosition' => $classPosition,
                'total_students' => count($studentRankings),
                'totalStudentsInClass' => count($studentRankings),
                'class_average' => round($classAverage, 2),
                'classAverage' => round($classAverage, 2),

                'gradePosition' => $gradePosition,
                'totalStudentsInGrade' => $totalStudentsInGrade,
                'gradeAverage' => round($gradeAverage, 2),
                'gradeName' => $klass->grade->name,

                'term_start_date' => $student->currentTerm->start_date ?? '',
                'term_end_date' => $student->currentTerm->end_date ?? '',
                'nextTermStartDate' => $nextTermStartDate,
                'classTeacherRemarks' => $classTeacherRemarks,
                'headTeachersRemarks' => $headTeachersRemarks,
                'class_teacher_signature' => $student->currentClass()->teacher->signature_path ?? '',
                'head_teacher_signature' => $school_head->signature_path ?? '',
            ];
        }

        $data = [
            'reportCards' => $reportCardsData,
            'school_setup' => $school_setup,
            'school_head' => $school_head,

            'gradeAverage' => round($gradeAverage, 2),
            'totalStudentsInGrade' => $totalStudentsInGrade,
            'gradeName' => $klass->grade->name,
        ];

        $pdf = PDF::loadView('assessment.junior.report-card-class-junior', $data);
        return $pdf->stream('class-report-cards.pdf');
    }

    /**
     * Generate value addition analysis for junior school
     */
    public function generateValueAdditionAnalysis($classId, $type, $sequenceId){
        $klass = Klass::findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $term = Term::findOrFail($selectedTermId);

        $test = Test::where('term_id',$selectedTermId)->where('type',$type)->where('sequence',$sequenceId)->first();
        $school_setup = SchoolSetup::first();

        $allGradeSubjects = GradeSubject::where('grade_id', $klass->grade_id)
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->orderByRaw('CASE WHEN sequence IS NULL OR sequence = 0 THEN 1 ELSE 0 END')
            ->orderBy('sequence', 'asc')
            ->get();

        $subjectMapping = [];
        foreach ($allGradeSubjects as $gradeSubject) {
            if ($gradeSubject->subject) { 
                $subjectName = strtoupper(substr($gradeSubject->subject->name, 0, 3));
                $subjectMapping[$subjectName] = $gradeSubject;
            } else {
                Log::warning("GradeSubject ID {$gradeSubject->id} has no associated subject. It will be skipped.");
            }
        }

        $jcSubjects = array_keys($subjectMapping);
        $psleSubjectKeysInPsleModel = PSLE::getSubjects();

        $gradeCategories = ['M', 'A', 'B', 'C', 'D', 'E', 'U'];
        $psleGradeCategories = ['A', 'B', 'C', 'D', 'E', 'U'];

        $psleOverallGradeCounts = array_fill_keys($psleGradeCategories, 0);
        $jcOverallGradeCounts = array_fill_keys($gradeCategories, 0);
        $gradeShiftMatrix = array_fill_keys($psleGradeCategories, array_fill_keys($gradeCategories, 0));
        
        $highPsleAchievers = [];

        $gradeCounts = [];
        foreach ($jcSubjects as $subject) {
            $gradeCounts[$subject] = [
                'PSLE' => array_fill_keys($psleGradeCategories, 0),
                'JC' => array_fill_keys($gradeCategories, 0),
                'totalPSLE' => 0,
                'totalJC' => 0,
                'qualityPSLE' => 0,
                'quantityPSLE' => 0,
                'qualityJC' => 0,
                'quantityJC' => 0,
                'valueAddition' => 0,
            ];
        }

        $students = $klass->students()->with('psle')->wherePivot('term_id', $selectedTermId)->wherePivot('active', 1)->get();
        foreach ($students as $student) {
            $psleRecord = $student->psle;

            $overallGradePSLE = $psleRecord->overall_grade ?? 'U';
            if (array_key_exists($overallGradePSLE, $psleOverallGradeCounts)) {
                 $psleOverallGradeCounts[$overallGradePSLE]++;
            }

            $hasParticipatedInJC = false;

            foreach ($jcSubjects as $jcSubjectCode) {
                $gradeSubjectInstance = $subjectMapping[$jcSubjectCode] ?? null;

                if (!$gradeSubjectInstance || !$gradeSubjectInstance->subject) {
                    $gradeCounts[$jcSubjectCode]['PSLE']['U']++;
                    $gradeCounts[$jcSubjectCode]['totalPSLE']++;
                    $gradeCounts[$jcSubjectCode]['totalJC']++;
                    continue;
                }
                
                $studentGradeIdForSubject = $klass->grade_id;
                $jcSubjectPerformance = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                    $student,
                    $gradeSubjectInstance,
                    $selectedTermId,
                    $type,
                    $sequenceId,
                    $studentGradeIdForSubject
                );

                $jcActualGrade = $jcSubjectPerformance['grade'] ?? 'U';
                $jcPercentage = $jcSubjectPerformance['percentage'];

                if ($jcActualGrade === 'Merit') $jcActualGrade = 'M';
                if (array_key_exists($jcActualGrade, $gradeCounts[$jcSubjectCode]['JC'])) {
                    $gradeCounts[$jcSubjectCode]['JC'][$jcActualGrade]++;
                } else {
                    $gradeCounts[$jcSubjectCode]['JC']['U']++;
                }
                $gradeCounts[$jcSubjectCode]['totalJC']++;

                if (!is_null($jcPercentage)) {
                    $hasParticipatedInJC = true;
                }

                $psleSubjectAttribute = null;
                $fullJcSubjectNameLower = strtolower($gradeSubjectInstance->subject->name);

                foreach ($psleSubjectKeysInPsleModel as $psleKey) {
                    if (str_contains($fullJcSubjectNameLower, strtolower($psleKey))) {
                         $psleSubjectAttribute = $psleKey;
                         break;
                    }
                }
                
                $psleSubjectGrade = $overallGradePSLE;
                if ($psleSubjectAttribute && isset($psleRecord->{$psleSubjectAttribute})) {
                    $psleSubjectGrade = $psleRecord->{$psleSubjectAttribute} ?? 'U';
                }

                if (array_key_exists($psleSubjectGrade, $gradeCounts[$jcSubjectCode]['PSLE'])) {
                     $gradeCounts[$jcSubjectCode]['PSLE'][$psleSubjectGrade]++;
                } else {
                    $gradeCounts[$jcSubjectCode]['PSLE']['U']++;
                }
                $gradeCounts[$jcSubjectCode]['totalPSLE']++;
            }

            list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = $this->calculatePoints(
                $student,
                $allGradeSubjects,
                $selectedTermId,
                $student->nationality !== 'Motswana',
                $type,
                $sequenceId
            );
            $totalPointsJC = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;
            
            $overallGradeJC = $hasParticipatedInJC
                ? AssessmentHelper::determineGrade($totalPointsJC, $klass) 
                : 'U';
            if ($overallGradeJC === 'Merit') $overallGradeJC = 'M';

            if (array_key_exists($overallGradeJC, $jcOverallGradeCounts)) {
                 $jcOverallGradeCounts[$overallGradeJC]++;
            } else {
                $jcOverallGradeCounts['U']++;
            }

            if (array_key_exists($overallGradePSLE, $gradeShiftMatrix) && array_key_exists($overallGradeJC, $gradeShiftMatrix[$overallGradePSLE])) {
                $gradeShiftMatrix[$overallGradePSLE][$overallGradeJC]++;
            }

            if (in_array($overallGradePSLE, ['A','B','C'])) {
                $highPsleAchievers[] = [
                    'name'       => $student->full_name,
                    'psle_grade' => $overallGradePSLE,
                    'jc_grade'   => $overallGradeJC,
                    'jc_points'  => $totalPointsJC, 
                ];
            }
            
        }

        $valueAdditions = [];
        foreach ($jcSubjects as $subject) {
            $totalPSLEForSubject = max($gradeCounts[$subject]['totalPSLE'], 1);
            $totalJCForSubject = max($gradeCounts[$subject]['totalJC'], 1);

            $qualityPSLE = ($gradeCounts[$subject]['PSLE']['A'] ?? 0) + 
                           ($gradeCounts[$subject]['PSLE']['B'] ?? 0) +
                           ($gradeCounts[$subject]['PSLE']['C'] ?? 0);
            $qualityJC = ($gradeCounts[$subject]['JC']['M'] ?? 0) + 
                         ($gradeCounts[$subject]['JC']['A'] ?? 0) + 
                         ($gradeCounts[$subject]['JC']['B'] ?? 0) +
                         ($gradeCounts[$subject]['JC']['C'] ?? 0);
            
            $quantityPSLE = $qualityPSLE;
            $quantityJC = $qualityJC;

            $gradeCounts[$subject]['qualityPSLE'] = round(($qualityPSLE / $totalPSLEForSubject) * 100, 0);
            $gradeCounts[$subject]['quantityPSLE'] = round(($quantityPSLE / $totalPSLEForSubject) * 100, 0);
            $gradeCounts[$subject]['qualityJC'] = round(($qualityJC / $totalJCForSubject) * 100, 0);
            $gradeCounts[$subject]['quantityJC'] = round(($quantityJC / $totalJCForSubject) * 100, 0);

            $gradeCounts[$subject]['valueAddition'] = $gradeCounts[$subject]['qualityJC'] - $gradeCounts[$subject]['qualityPSLE'];
            $valueAdditions[$subject] = $gradeCounts[$subject]['valueAddition'];
        }

        $totalPsleStudentsOverall = max(array_sum($psleOverallGradeCounts), 1);
        $overallQualityPSLECount = ($psleOverallGradeCounts['A'] ?? 0) + 
                                   ($psleOverallGradeCounts['B'] ?? 0) +
                                   ($psleOverallGradeCounts['C'] ?? 0);
        $overallQualityPSLEPercent = round(($overallQualityPSLECount / $totalPsleStudentsOverall) * 100, 0);

        $totalJcStudentsOverall = max(array_sum($jcOverallGradeCounts), 1);
        $overallQualityJCCount = ($jcOverallGradeCounts['M'] ?? 0) + 
                                 ($jcOverallGradeCounts['A'] ?? 0) + 
                                 ($jcOverallGradeCounts['B'] ?? 0) +
                                 ($jcOverallGradeCounts['C'] ?? 0);
        $overallQualityJCPercent = round(($overallQualityJCCount / $totalJcStudentsOverall) * 100, 0);

        $valueAdditions['overall'] = $overallQualityJCPercent - $overallQualityPSLEPercent;

        arsort($valueAdditions);
        $rankedSubjects = array_keys(array_filter($valueAdditions, function($key) {
            return $key !== 'overall';
        }, ARRAY_FILTER_USE_KEY));

        usort($highPsleAchievers, function($a, $b) {
            $gradeOrder = ['A' => 1, 'B' => 2, 'C' => 3];
            $gradeComparison = $gradeOrder[$a['psle_grade']] - $gradeOrder[$b['psle_grade']];
            
            if ($gradeComparison === 0) {
                return strcmp($a['name'], $b['name']);
            }
            return $gradeComparison;
        });

        $data = [
            'klass' => $klass,
            'term' => $term,
            'test' => $test,
            'school_data' => $school_setup,
            'jcSubjects' => $jcSubjects,
            'gradeCategories' => $gradeCategories,
            'psleGradeCategories' => $psleGradeCategories,
            'gradeCounts' => $gradeCounts,
            'psleOverallGradeCounts' => $psleOverallGradeCounts,
            'jcOverallGradeCounts' => $jcOverallGradeCounts,
            'valueAdditions' => $valueAdditions,
            'rankedSubjects' => $rankedSubjects,
            'gradeShiftMatrix' => $gradeShiftMatrix,
            'highPsleAchievers' => $highPsleAchievers,
        ];

        $valueAdditionChartLabels = $rankedSubjects;
        $valueAdditionChartData = [];
        foreach($rankedSubjects as $subject) {
            $valueAdditionChartData[] = $valueAdditions[$subject];
        }
        $data['valueAdditionChart'] = [
            'labels' => $valueAdditionChartLabels,
            'data' => $valueAdditionChartData,
        ];

        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\ValueAdditionAnalysisExport($data),
                "Value_Addition_Analysis_{$klass->name}_" . date('Y-m-d') . ".xlsx"
            );
        } else {
            return view('assessment.shared.value-addition-analysis', $data);
        }
    }

    /**
     * Generate test comparison analysis for junior school
     */
    public function generateTestComparisonAnalysis(int $classId, string $type, int $sequenceId){
        $klass          = Klass::findOrFail($classId);
        $currentTerm    = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $schoolSetup    = SchoolSetup::first();

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;

        // Compare with same type and sequence from previous term
        $prevTerm     = TermHelper::getPreviousTerm($selectedTerm);
        $prevType     = $type;        // Same test type (CA or Exam)
        $prevSequence = $sequenceId;  // Same sequence number

        if (!$prevTerm) {
            return view('assessment.shared.test-comparison-analysis', [
                'error' => 'No previous term data available for comparison.',
                'school_data' => $schoolSetup,
                'klass' => $klass,
            ]);
        }

        $test = Test::where('term_id',$selectedTermId)->where('type',$type)->where('sequence',$sequenceId)->first();
        
        $prevTermId = $prevTerm->id;
        $allGradeSubjects = GradeSubject::where('grade_id', $klass->grade_id)->where('term_id', $selectedTermId)->where('active', 1)->with('subject')->orderByRaw('CASE WHEN sequence IS NULL OR sequence = 0 THEN 1 ELSE 0 END')->orderBy('sequence', 'asc')->get();
        $subjectMapping = [];

        foreach ($allGradeSubjects as $gs) {
            $code = strtoupper(substr($gs->subject->name, 0, 3));
            $subjectMapping[$code] = $gs;
        }

        $subjects        = array_keys($subjectMapping);
        $gradeCategories = ['A','B','C','D','E','U'];
    
        $gradeCounts = [];
        foreach ($subjects as $sub) {
            $gradeCounts[$sub] = [
                'prev'          => array_fill_keys($gradeCategories, 0),
                'curr'          => array_fill_keys($gradeCategories, 0),
                'totalPrev'     => 0,
                'totalCurr'     => 0,
                'qualityPrev'   => 0,
                'quantityPrev'  => 0,
                'qualityCurr'   => 0,
                'quantityCurr'  => 0,
                'valueAddition' => 0,
            ];
        }
    
        $students = $klass->students()->wherePivot('term_id', $selectedTermId)->wherePivot('active', 1)->get();
        foreach ($students as $student) {
            foreach ($subjects as $sub) {
                $gs = $subjectMapping[$sub];
    
                $prevResult = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                    $student,
                    $gs,
                    $prevTermId,
                    $prevType,
                    $prevSequence,
                    $klass->grade_id
                );
                $prevGrade = $prevResult['grade'] ?? 'U';
                $gradeCounts[$sub]['prev'][$prevGrade]++;
                $gradeCounts[$sub]['totalPrev']++;
    
                $currResult = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                    $student,
                    $gs,
                    $selectedTermId,
                    $type,
                    $sequenceId,
                    $klass->grade_id
                );
                $currGrade = $currResult['grade'] ?? 'U';
                $gradeCounts[$sub]['curr'][$currGrade]++;
                $gradeCounts[$sub]['totalCurr']++;
            }
        }
    
        $valueAdditions = [];
        $sumPrevAB = $sumCurrAB = 0;
    
        foreach ($subjects as $sub) {
            $totP  = max($gradeCounts[$sub]['totalPrev'], 1);
            $totC  = max($gradeCounts[$sub]['totalCurr'], 1);
            $qualP = $gradeCounts[$sub]['prev']['A'] + $gradeCounts[$sub]['prev']['B'];
            $qualC = $gradeCounts[$sub]['curr']['A'] + $gradeCounts[$sub]['curr']['B'];
            $quanP = $qualP + $gradeCounts[$sub]['prev']['C'];
            $quanC = $qualC + $gradeCounts[$sub]['curr']['C'];
    
            $gradeCounts[$sub]['qualityPrev']  = round(($qualP / $totP) * 100, 0);
            $gradeCounts[$sub]['quantityPrev'] = round(($quanP / $totP) * 100, 0);
            $gradeCounts[$sub]['qualityCurr']  = round(($qualC / $totC) * 100, 0);
            $gradeCounts[$sub]['quantityCurr'] = round(($quanC / $totC) * 100, 0);
    
            $gradeCounts[$sub]['valueAddition'] = round(
                $gradeCounts[$sub]['qualityCurr'] - $gradeCounts[$sub]['qualityPrev'],
                2
            );
    
            $valueAdditions[$sub] = $gradeCounts[$sub]['valueAddition'];
            $sumPrevAB += $qualP;
            $sumCurrAB += $qualC;
        }
    
        $valueAdditions['overall'] = $sumCurrAB - $sumPrevAB;
    
        $prevGradeCounts = array_fill_keys($gradeCategories, 0);
        $currGradeCounts = array_fill_keys($gradeCategories, 0);
        foreach ($gradeCounts as $counts) {
            foreach ($gradeCategories as $g) {
                $prevGradeCounts[$g] += $counts['prev'][$g];
                $currGradeCounts[$g] += $counts['curr'][$g];
            }
        }
    
        arsort($valueAdditions);
        $rankedSubjects = array_keys($valueAdditions);
    
        $data = [
            'school_data'     => $schoolSetup,
            'subjects'        => $subjects,
            'jcSubjects'      => $subjects,
            'gradeCounts'     => $gradeCounts,
            'prevGradeCounts' => $prevGradeCounts,
            'currGradeCounts' => $currGradeCounts,
            'valueAdditions'  => $valueAdditions,
            'rankedSubjects'  => $rankedSubjects,
            'prevType'        => $prevType,
            'prevSequence'    => $prevSequence,
            'type'            => $type,
            'sequenceId'      => $sequenceId,
            'klass'           => $klass,
            'selectedTerm'    => $selectedTerm,
            'prevTerm'        => $prevTerm,
            'test'            => $test  
        ];
    
        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\TestComparisonAnalysisExport($data),
                "Test_Comparison_{$klass->name}_" . date('Y-m-d') . ".xlsx"
            );
        }
        return view('assessment.shared.test-comparison-analysis', $data);
    }

    /**
     * Generate overall CA class performance report
     */
    public function generateOverallCAClassPerformanceReport($classId, $sequence){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $school_data = SchoolSetup::first();

        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;

        $test = Test::where('term_id',$selectedTermId)->where('type','CA')->where('sequence',$sequence)->first();
        $grade = Grade::with('klasses')->findOrFail($gradeId);
        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->orderByRaw('CASE WHEN sequence IS NULL OR sequence = 0 THEN 1 ELSE 0 END')
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get();

        Log::info('=== CA CLASS REPORT DEBUG ===');
        Log::info("classId={$classId}, sequence={$sequence}, selectedTermId={$selectedTermId}");
        Log::info("Klass: {$klass->name}, gradeId={$gradeId}, grade={$grade->name}");
        Log::info("Test found: " . ($test ? "id={$test->id} name={$test->name}" : "NULL"));
        Log::info("GradeSubjects count: " . $allGradeSubjects->count());
        Log::info("Grade klasses count: " . $grade->klasses->count());
        Log::info("CurrentTerm year: {$currentTerm->year}");

        $allSubjects = $allGradeSubjects->pluck('subject.name')->unique()->sort()->values()->toArray();
        $classPerformance = [];

        foreach ($grade->klasses as $class) {
            $termId = $selectedTermId;
            $year = $selectedTerm->year;
            $students = $class->currentStudents($termId, $year)->get();
            Log::info("Class {$class->name} (id={$class->id}): {$students->count()} students (termId={$termId}, year={$year})");
    
            $gradeCounts = [
                'Merit' => ['M' => 0, 'F' => 0],
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'U' => ['M' => 0, 'F' => 0],
                'total' => ['M' => 0, 'F' => 0],
            ];
    
            $reportCardsData = [];
            $validMaleCount = 0;
            $validFemaleCount = 0;
            $allMaleCount = 0;
            $allFemaleCount = 0;

            foreach ($students as $student) {
                $studentClass = $student->currentClass();
                if (!$studentClass) {
                    continue;
                }

                $gender = $student->gender === 'M' ? 'M' : 'F';
                if ($gender === 'M') { $allMaleCount++; } else { $allFemaleCount++; }

                $isForeigner = $student->is_foreigner;
                $studentGradeSubjects = $allGradeSubjects->where('grade_id', $studentClass->grade_id);

                if ($students->first()->id === $student->id) {
                    Log::info("  First student: id={$student->id}, gender={$student->gender}, class={$studentClass->name}, studentClass->grade_id={$studentClass->grade_id}");
                    Log::info("  studentGradeSubjects count: " . $studentGradeSubjects->count());
                }

                $subjectScores = [];
                $subjectPoints = [];
                $hasValidTestResults = false;

                foreach ($studentGradeSubjects as $gradeSubject) {
                    $subjectData = AssessmentHelper::calculateSubjectCAScoresAnalysis(
                        $student,
                        $gradeSubject,
                        $selectedTermId,
                        $sequence,
                        $studentClass->grade_id
                    );

                    if ($subjectData['percentage'] !== null && $subjectData['percentage'] > 0) {
                        $hasValidTestResults = true;
                    }

                    $subjectName = $gradeSubject->subject->name;

                    $subjectScores[$subjectName] = [
                        'percentage' => $subjectData['percentage'],
                        'grade' => $subjectData['grade']
                    ];
                    $subjectPoints[$gradeSubject->id] = $subjectData['points'];
                }

                if ($students->first()->id === $student->id) {
                    Log::info("  First student hasValidTestResults: " . ($hasValidTestResults ? 'YES' : 'NO'));
                    Log::info("  First student scores: " . json_encode($subjectScores));
                }

                if (!$hasValidTestResults) {
                    continue;
                }
            
                list(
                    $mandatoryPoints, 
                    $bestOptionalPoints, 
                    $bestCorePoints
                ) = AssessmentHelper::calculatePointsCA(
                    $student,
                    $studentGradeSubjects,
                    $selectedTermId,
                    $isForeigner,
                    $sequence
                );
            
                $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;

                if ($students->first()->id === $student->id) {
                    Log::info("  First student points: mandatory={$mandatoryPoints}, bestOpt={$bestOptionalPoints}, bestCore={$bestCorePoints}, total={$totalPoints}");
                }

                if ($totalPoints <= 0 || $totalPoints === null) {
                    continue;
                }

                $gradeValue = AssessmentHelper::determineGrade($totalPoints, $studentClass);

                if ($students->first()->id === $student->id) {
                    Log::info("  First student grade: {$gradeValue}");
                }

                $gender = $student->gender === 'M' ? 'M' : 'F';

                if (isset($gradeCounts[$gradeValue][$gender])) {
                    $gradeCounts[$gradeValue][$gender]++;
                    $gradeCounts['total'][$gender]++;

                    if ($gender === 'M') {
                        $validMaleCount++;
                    } else {
                        $validFemaleCount++;
                    }
                }
            
                $reportCardsData[] = [
                    'student' => $student,
                    'scores' => $subjectScores,
                    'totalPoints' => $totalPoints,
                    'grade' => $gradeValue,
                    'class_name' => $studentClass->name,
                ];
            }
    
            usort($reportCardsData, function ($a, $b) {
                return $b['totalPoints'] <=> $a['totalPoints'];
            });
    
            foreach ($reportCardsData as $index => &$data) {
                $data['position'] = $index + 1;
            }
            unset($data);
    
            $totalMale = $allMaleCount;
            $totalFemale = $allFemaleCount;

            Log::info("Class {$class->name} result: allM={$allMaleCount}, allF={$allFemaleCount}, validM={$validMaleCount}, validF={$validFemaleCount}, reportCards=" . count($reportCardsData));
            Log::info("Class {$class->name} gradeCounts: " . json_encode($gradeCounts));

            $mabCountM = $gradeCounts['Merit']['M'] + $gradeCounts['A']['M'] + $gradeCounts['B']['M'];
            $mabCountF = $gradeCounts['Merit']['F'] + $gradeCounts['A']['F'] + $gradeCounts['B']['F'];
    
            $mabPercentageM = $totalMale > 0 ? round(($mabCountM / $totalMale) * 100, 2) : 0;
            $mabPercentageF = $totalFemale > 0 ? round(($mabCountF / $totalFemale) * 100, 2) : 0;
    
            $mabcCountM = $mabCountM + $gradeCounts['C']['M'];
            $mabcCountF = $mabCountF + $gradeCounts['C']['F'];
    
            $mabcPercentageM = $totalMale > 0 ? round(($mabcCountM / $totalMale) * 100, 2) : 0;
            $mabcPercentageF = $totalFemale > 0 ? round(($mabcCountF / $totalFemale) * 100, 2) : 0;
    
            $mabcdCountM = $mabcCountM + $gradeCounts['D']['M'];
            $mabcdCountF = $mabcCountF + $gradeCounts['D']['F'];
    
            $mabcdPercentageM = $totalMale > 0 ? round(($mabcdCountM / $totalMale) * 100, 2) : 0;
            $mabcdPercentageF = $totalFemale > 0 ? round(($mabcdCountF / $totalFemale) * 100, 2) : 0;
    
            $deuCountM = $gradeCounts['D']['M'] + $gradeCounts['E']['M'] + $gradeCounts['U']['M'];
            $deuCountF = $gradeCounts['D']['F'] + $gradeCounts['E']['F'] + $gradeCounts['U']['F'];
    
            $deuPercentageM = $totalMale > 0 ? round(($deuCountM / $totalMale) * 100, 2) : 0;
            $deuPercentageF = $totalFemale > 0 ? round(($deuCountF / $totalFemale) * 100, 2) : 0;
    
            $classPerformance[$class->name] = [
                'grades' => $gradeCounts,
                'MAB%' => ['M' => $mabPercentageM, 'F' => $mabPercentageF],
                'MABC%' => ['M' => $mabcPercentageM, 'F' => $mabcPercentageF],
                'MABCD%' => ['M' => $mabcdPercentageM, 'F' => $mabcdPercentageF],
                'DEU%' => ['M' => $deuPercentageM, 'F' => $deuPercentageF],
                'totalMale' => $totalMale,
                'totalFemale' => $totalFemale
            ];
        }
    
        $overallTotals = [
            'grades' => [
                'Merit' => ['M' => 0, 'F' => 0],
                'A'     => ['M' => 0, 'F' => 0],
                'B'     => ['M' => 0, 'F' => 0],
                'C'     => ['M' => 0, 'F' => 0],
                'D'     => ['M' => 0, 'F' => 0],
                'E'     => ['M' => 0, 'F' => 0],
                'U'     => ['M' => 0, 'F' => 0],
            ],
            'MAB%'   => ['M' => 0, 'F' => 0],
            'MABC%'  => ['M' => 0, 'F' => 0],
            'MABCD%' => ['M' => 0, 'F' => 0],
            'DEU%'   => ['M' => 0, 'F' => 0],
            'totalMale'   => 0,
            'totalFemale' => 0,
        ];
        
        foreach ($classPerformance as $perf) {
            foreach (['Merit','A','B','C','D','E','U'] as $g) {
                $overallTotals['grades'][$g]['M'] += $perf['grades'][$g]['M'];
                $overallTotals['grades'][$g]['F'] += $perf['grades'][$g]['F'];
            }
            $overallTotals['totalMale']   += $perf['totalMale'];
            $overallTotals['totalFemale'] += $perf['totalFemale'];
        }
        
        $totM = max($overallTotals['totalMale'], 1);
        $totF = max($overallTotals['totalFemale'], 1);
        
        $overallTotals['MAB%']['M'] = round(100 * (
                $overallTotals['grades']['Merit']['M'] +
                $overallTotals['grades']['A']['M'] +
                $overallTotals['grades']['B']['M']
            ) / $totM, 2);
    
        $overallTotals['MAB%']['F'] = round(100 * (
                $overallTotals['grades']['Merit']['F'] +
                $overallTotals['grades']['A']['F'] +
                $overallTotals['grades']['B']['F']
            ) / $totF, 2);
        
        $overallTotals['MABC%']['M'] = round(100 * (
                $overallTotals['grades']['Merit']['M'] +
                $overallTotals['grades']['A']['M'] +
                $overallTotals['grades']['B']['M'] +
                $overallTotals['grades']['C']['M']
            ) / $totM, 2);
    
        $overallTotals['MABC%']['F'] = round(100 * (
                $overallTotals['grades']['Merit']['F'] +
                $overallTotals['grades']['A']['F'] +
                $overallTotals['grades']['B']['F'] +
                $overallTotals['grades']['C']['F']
            ) / $totF, 2);
        
        $overallTotals['MABCD%']['M'] = round(100 * (
                $overallTotals['grades']['Merit']['M'] +
                $overallTotals['grades']['A']['M'] +
                $overallTotals['grades']['B']['M'] +
                $overallTotals['grades']['C']['M'] +
                $overallTotals['grades']['D']['M']
            ) / $totM, 2);
        
        $overallTotals['MABCD%']['F'] = round(100 * (
                $overallTotals['grades']['Merit']['F'] +
                $overallTotals['grades']['A']['F'] +
                $overallTotals['grades']['B']['F'] +
                $overallTotals['grades']['C']['F'] +
                $overallTotals['grades']['D']['F']
            ) / $totF, 2);
        
        $overallTotals['DEU%']['M'] = round(100 * (
                $overallTotals['grades']['D']['M'] +
                $overallTotals['grades']['E']['M'] +
                $overallTotals['grades']['U']['M']
            ) / $totM, 2);
        
        $overallTotals['DEU%']['F'] = round(100 * (
                $overallTotals['grades']['D']['F'] +
                $overallTotals['grades']['E']['F'] +
                $overallTotals['grades']['U']['F']
            ) / $totF, 2);
    
        if (request()->has('export') && request()->get('export') === 'excel') {
            return $this->exportClassPerformanceToExcel($classPerformance,$overallTotals,$test);
        }
    
        return view('assessment.junior.overall-ca-classes-junior', [
            'classPerformance' => $classPerformance,
            'school_data' => $school_data,
            'currentTerm' => $selectedTerm,
            'test' => $test,
            'allSubjects' => $allSubjects,
            'overallTotals' => $overallTotals,
            'grade' => $grade,
        ]);
    }

    /**
     * Generate overall exam class performance report
     */
    public function generateOverallExamClassPerformanceReport($classId){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm = TermHelper::getCurrentTerm();
        
        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $school_data = SchoolSetup::first();

        $test = Test::where('term_id',$selectedTermId)->where('type','Exam')->where('sequence',1)->first();
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;
    
        $grade = Grade::with('klasses')->findOrFail($gradeId);
        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get();
    
        $allSubjects = $allGradeSubjects->pluck('subject.name')->unique()->sort()->values()->toArray();
        $classPerformance = [];
    
        foreach ($grade->klasses as $class) {
            $termId = $selectedTermId;
            $year = $selectedTerm->year;
            $students = $class->currentStudents($termId, $year)->get();
    
            $gradeCounts = [
                'Merit' => ['M' => 0, 'F' => 0],
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'U' => ['M' => 0, 'F' => 0],
                'total' => ['M' => 0, 'F' => 0],
            ];
    
            $reportCardsData = [];
            $validMaleCount = 0;
            $validFemaleCount = 0;
            $allMaleCount = 0;
            $allFemaleCount = 0;

            foreach ($students as $student) {
                $studentClass = $student->currentClass();
                if (!$studentClass) {
                    continue;
                }

                $gender = $student->gender === 'M' ? 'M' : 'F';
                if ($gender === 'M') { $allMaleCount++; } else { $allFemaleCount++; }

                $isForeigner = $student->nationality !== 'Motswana';
                $studentGradeSubjects = $allGradeSubjects->where('grade_id', $studentClass->grade_id);

                $subjectScores = [];
                $hasValidTestResults = false;

                foreach ($studentGradeSubjects as $gradeSubject) {
                    $subjectData = AssessmentHelper::calculateSubjectScoresAnalysis(
                        $student,
                        $gradeSubject,
                        $selectedTermId,
                        $studentClass->grade_id
                    );
    
                    if ($subjectData['percentage'] !== null && $subjectData['percentage'] > 0) {
                        $hasValidTestResults = true;
                    }
    
                    $subjectName = $gradeSubject->subject->name;
    
                    $subjectScores[$subjectName] = [
                        'percentage' => $subjectData['percentage'],
                        'grade' => $subjectData['grade']
                    ];
                    $subjectPoints[$gradeSubject->id] = $subjectData['points'];
                }
    
                if (!$hasValidTestResults) {
                    continue;
                }
    
                list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = AssessmentHelper::calculatePoints(
                    $student,
                    $studentGradeSubjects,
                    $selectedTermId,
                    $isForeigner
                );
    
                $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;
                
                if ($totalPoints <= 0 || $totalPoints === null) {
                    continue;
                }
                
                $gradeValue = AssessmentHelper::determineGrade($totalPoints, $studentClass);
                $gender = $student->gender === 'M' ? 'M' : 'F';
    
                if (isset($gradeCounts[$gradeValue][$gender])) {
                    $gradeCounts[$gradeValue][$gender]++;
                    $gradeCounts['total'][$gender]++;
                    
                    if ($gender === 'M') {
                        $validMaleCount++;
                    } else {
                        $validFemaleCount++;
                    }
                }
    
                $reportCardsData[] = [
                    'student' => $student,
                    'scores' => $subjectScores,
                    'totalPoints' => $totalPoints,
                    'grade' => $gradeValue,
                    'class_name' => $studentClass->name,
                ];
            }
    
            usort($reportCardsData, function ($a, $b) {
                return $b['totalPoints'] <=> $a['totalPoints'];
            });
    
            foreach ($reportCardsData as $index => &$data) {
                $data['position'] = $index + 1;
            }
            unset($data);
    
            $totalMale = $allMaleCount;
            $totalFemale = $allFemaleCount;

            $sumMerit = $gradeCounts['Merit']['M'] + $gradeCounts['Merit']['F'];
            $sumA = $gradeCounts['A']['M'] + $gradeCounts['A']['F'];
            $sumB = $gradeCounts['B']['M'] + $gradeCounts['B']['F'];
            $sumC = $gradeCounts['C']['M'] + $gradeCounts['C']['F'];
    
            $mbCountM = $gradeCounts['Merit']['M'] + $gradeCounts['B']['M'];
            $mbCountF = $gradeCounts['Merit']['F'] + $gradeCounts['B']['F'];
            
            $mbPercentageM = $totalMale > 0 ? round(($mbCountM / $totalMale) * 100, 2) : 0;
            $mbPercentageF = $totalFemale > 0 ? round(($mbCountF / $totalFemale) * 100, 2) : 0;
    
            $mabCount = $sumMerit + $sumA + $sumB;
            $mabcCount = $mabCount + $sumC;
    
            $mabCountM = $gradeCounts['Merit']['M'] + $gradeCounts['A']['M'] + $gradeCounts['B']['M'];
            $mabCountF = $gradeCounts['Merit']['F'] + $gradeCounts['A']['F'] + $gradeCounts['B']['F'];
    
            $mabPercentageM = $totalMale > 0 ? round(($mabCountM / $totalMale) * 100, 2) : 0;
            $mabPercentageF = $totalFemale > 0 ? round(($mabCountF / $totalFemale) * 100, 2) : 0;
    
            $mabcCountM = $mabCountM + $gradeCounts['C']['M'];
            $mabcCountF = $mabCountF + $gradeCounts['C']['F'];
    
            $mabcPercentageM = $totalMale > 0 ? round(($mabcCountM / $totalMale) * 100, 2) : 0;
            $mabcPercentageF = $totalFemale > 0 ? round(($mabcCountF / $totalFemale) * 100, 2) : 0;
    
            $mabcdCountM = $mabcCountM + $gradeCounts['D']['M'];
            $mabcdCountF = $mabcCountF + $gradeCounts['D']['F'];
    
            $mabcdPercentageM = $totalMale > 0 ? round(($mabcdCountM / $totalMale) * 100, 2) : 0;
            $mabcdPercentageF = $totalFemale > 0 ? round(($mabcdCountF / $totalFemale) * 100, 2) : 0;
    
            $deuCountM = $gradeCounts['D']['M'] + $gradeCounts['E']['M'] + $gradeCounts['U']['M'];
            $deuCountF = $gradeCounts['D']['F'] + $gradeCounts['E']['F'] + $gradeCounts['U']['F'];
    
            $deuPercentageM = $totalMale > 0 ? round(($deuCountM / $totalMale) * 100, 2) : 0;
            $deuPercentageF = $totalFemale > 0 ? round(($deuCountF / $totalFemale) * 100, 2) : 0;
    
            $classPerformance[$class->name] = [
                'grades' => $gradeCounts,
                'MB%' => ['M' => $mbPercentageM, 'F' => $mbPercentageF],
                'MAB%' => ['M' => $mabPercentageM, 'F' => $mabPercentageF],
                'MABC%' => ['M' => $mabcPercentageM, 'F' => $mabcPercentageF],
                'MABCD%' => ['M' => $mabcdPercentageM, 'F' => $mabcdPercentageF],
                'DEU%' => ['M' => $deuPercentageM, 'F' => $deuPercentageF],
                'totalMale' => $totalMale,
                'totalFemale' => $totalFemale
            ];
        }
    
        $overallTotals = [
            'grades' => [
                'Merit' => ['M' => 0, 'F' => 0],
                'A'     => ['M' => 0, 'F' => 0],
                'B'     => ['M' => 0, 'F' => 0],
                'C'     => ['M' => 0, 'F' => 0],
                'D'     => ['M' => 0, 'F' => 0],
                'E'     => ['M' => 0, 'F' => 0],
                'U'     => ['M' => 0, 'F' => 0],
            ],
            'MB%'    => ['M' => 0, 'F' => 0],
            'MAB%'   => ['M' => 0, 'F' => 0],
            'MABC%'  => ['M' => 0, 'F' => 0],
            'MABCD%' => ['M' => 0, 'F' => 0],
            'DEU%'   => ['M' => 0, 'F' => 0],
            'totalMale'   => 0,
            'totalFemale' => 0,
        ];
        
        foreach ($classPerformance as $perf) {
            foreach (['Merit','A','B','C','D','E','U'] as $g) {
                $overallTotals['grades'][$g]['M'] += $perf['grades'][$g]['M'];
                $overallTotals['grades'][$g]['F'] += $perf['grades'][$g]['F'];
            }
            $overallTotals['totalMale']   += $perf['totalMale'];
            $overallTotals['totalFemale'] += $perf['totalFemale'];
        }
        
        $totM = max($overallTotals['totalMale'],   1);
        $totF = max($overallTotals['totalFemale'], 1);
        
        $overallTotals['MB%']['M'] = round(100 * (
                $overallTotals['grades']['Merit']['M'] +
                $overallTotals['grades']['B']['M']
            ) / $totM, 2);
    
        $overallTotals['MB%']['F'] = round(100 * (
                $overallTotals['grades']['Merit']['F'] +
                $overallTotals['grades']['B']['F']
            ) / $totF, 2);
        
        $overallTotals['MAB%']['M'] = round(100 * (
                $overallTotals['grades']['Merit']['M'] +
                $overallTotals['grades']['A']['M'] +
                $overallTotals['grades']['B']['M']
            ) / $totM, 2);
    
        $overallTotals['MAB%']['F'] = round(100 * (
                $overallTotals['grades']['Merit']['F'] +
                $overallTotals['grades']['A']['F'] +
                $overallTotals['grades']['B']['F']
            ) / $totF, 2);
        
        $overallTotals['MABC%']['M'] = round(100 * (
                $overallTotals['grades']['Merit']['M'] +
                $overallTotals['grades']['A']['M'] +
                $overallTotals['grades']['B']['M'] +
                $overallTotals['grades']['C']['M']
            ) / $totM, 2);
    
        $overallTotals['MABC%']['F'] = round(100 * (
                $overallTotals['grades']['Merit']['F'] +
                $overallTotals['grades']['A']['F'] +
                $overallTotals['grades']['B']['F'] +
                $overallTotals['grades']['C']['F']
            ) / $totF, 2);
        
        $overallTotals['MABCD%']['M'] = round(100 * (
                $overallTotals['grades']['Merit']['M'] +
                $overallTotals['grades']['A']['M'] +
                $overallTotals['grades']['B']['M'] +
                $overallTotals['grades']['C']['M'] +
                $overallTotals['grades']['D']['M']
            ) / $totM, 2);
        
        $overallTotals['MABCD%']['F'] = round(100 * (
                $overallTotals['grades']['Merit']['F'] +
                $overallTotals['grades']['A']['F'] +
                $overallTotals['grades']['B']['F'] +
                $overallTotals['grades']['C']['F'] +
                $overallTotals['grades']['D']['F']
            ) / $totF, 2);
        
        $overallTotals['DEU%']['M'] = round(100 * (
                $overallTotals['grades']['D']['M'] +
                $overallTotals['grades']['E']['M'] +
                $overallTotals['grades']['U']['M']
            ) / $totM, 2);
        
        $overallTotals['DEU%']['F'] = round(100 * (
                $overallTotals['grades']['D']['F'] +
                $overallTotals['grades']['E']['F'] +
                $overallTotals['grades']['U']['F']
            ) / $totF, 2);
    
        if (request()->has('export') && request()->get('export') === 'excel') {
            return $this->exportClassPerformanceToExcel($classPerformance,$overallTotals,$test);
        }
    
        return view('assessment.junior.overall-ca-classes-junior', [
            'classPerformance' => $classPerformance,
            'school_data' => $school_data,
            'currentTerm' => $selectedTerm,
            'test' => $test,
            'allSubjects' => $allSubjects,
            'overallTotals' => $overallTotals,
            'grade' => $grade,
        ]);
    }

    /**
     * Generate Overall Classes Performance Report II (Exam)
     * - No gender breakdown (totals only)
     * - Separate tables per class
     * - Shows class teacher above each table
     */
    public function generateOverallExamClassPerformanceReportII($classId)
    {
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $selectedTerm = Term::find($selectedTermId) ?? TermHelper::getCurrentTerm();
        $school_data = SchoolSetup::first();

        $test = Test::where('term_id', $selectedTermId)
            ->where('type', 'Exam')
            ->where('sequence', 1)
            ->first();

        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;

        $grade = Grade::with(['klasses' => function ($query) use ($selectedTermId) {
            $query->with('teacher')->where('term_id', $selectedTermId);
        }])->findOrFail($gradeId);

        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get();

        $classPerformance = [];

        foreach ($grade->klasses as $class) {
            $year = $selectedTerm->year;
            $students = $class->currentStudents($selectedTermId, $year)->get();

            $gradeCounts = [
                'Merit' => 0, 'A' => 0, 'B' => 0, 'C' => 0,
                'D' => 0, 'E' => 0, 'U' => 0,
            ];
            $validStudentCount = 0;

            foreach ($students as $student) {
                $studentClass = $student->currentClass();
                if (!$studentClass) {
                    continue;
                }

                $isForeigner = $student->nationality !== 'Motswana';
                $studentGradeSubjects = $allGradeSubjects->where('grade_id', $studentClass->grade_id);

                $hasValidTestResults = false;

                foreach ($studentGradeSubjects as $gradeSubject) {
                    $subjectData = AssessmentHelper::calculateSubjectScoresAnalysis(
                        $student,
                        $gradeSubject,
                        $selectedTermId,
                        $studentClass->grade_id
                    );

                    if ($subjectData['percentage'] !== null && $subjectData['percentage'] > 0) {
                        $hasValidTestResults = true;
                    }
                }

                if (!$hasValidTestResults) {
                    continue;
                }

                list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = AssessmentHelper::calculatePoints(
                    $student,
                    $studentGradeSubjects,
                    $selectedTermId,
                    $isForeigner
                );

                $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;

                if ($totalPoints <= 0 || $totalPoints === null) {
                    continue;
                }

                $gradeValue = AssessmentHelper::determineGrade($totalPoints, $studentClass);

                if (isset($gradeCounts[$gradeValue])) {
                    $gradeCounts[$gradeValue]++;
                    $validStudentCount++;
                }
            }

            $total = max($validStudentCount, 1);
            $mabCount = $gradeCounts['Merit'] + $gradeCounts['A'] + $gradeCounts['B'];
            $mabcCount = $mabCount + $gradeCounts['C'];
            $mabcdCount = $mabcCount + $gradeCounts['D'];
            $deuCount = $gradeCounts['D'] + $gradeCounts['E'] + $gradeCounts['U'];

            $classPerformance[$class->name] = [
                'className' => $class->name,
                'classTeacher' => $class->teacher->full_name ?? 'Not Assigned',
                'grades' => $gradeCounts,
                'total' => $validStudentCount,
                'MAB%' => round(($mabCount / $total) * 100, 2),
                'MABC%' => round(($mabcCount / $total) * 100, 2),
                'MABCD%' => round(($mabcdCount / $total) * 100, 2),
                'DEU%' => round(($deuCount / $total) * 100, 2),
            ];
        }

        // Calculate overall totals
        $overallTotals = [
            'grades' => ['Merit' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'U' => 0],
            'total' => 0,
            'MAB%' => 0,
            'MABC%' => 0,
            'MABCD%' => 0,
            'DEU%' => 0,
        ];

        foreach ($classPerformance as $perf) {
            foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $g) {
                $overallTotals['grades'][$g] += $perf['grades'][$g];
            }
            $overallTotals['total'] += $perf['total'];
        }

        $grandTotal = max($overallTotals['total'], 1);
        $mabTotal = $overallTotals['grades']['Merit'] + $overallTotals['grades']['A'] + $overallTotals['grades']['B'];
        $mabcTotal = $mabTotal + $overallTotals['grades']['C'];
        $mabcdTotal = $mabcTotal + $overallTotals['grades']['D'];
        $deuTotal = $overallTotals['grades']['D'] + $overallTotals['grades']['E'] + $overallTotals['grades']['U'];

        $overallTotals['MAB%'] = round(($mabTotal / $grandTotal) * 100, 2);
        $overallTotals['MABC%'] = round(($mabcTotal / $grandTotal) * 100, 2);
        $overallTotals['MABCD%'] = round(($mabcdTotal / $grandTotal) * 100, 2);
        $overallTotals['DEU%'] = round(($deuTotal / $grandTotal) * 100, 2);

        return view('assessment.junior.overall-classes-analysis-ii', [
            'classPerformance' => $classPerformance,
            'school_data' => $school_data,
            'currentTerm' => $selectedTerm,
            'test' => $test,
            'overallTotals' => $overallTotals,
            'grade' => $grade,
            'reportType' => 'Exam',
        ]);
    }

    /**
     * Generate Overall Classes Performance Report II (CA)
     * - No gender breakdown (totals only)
     * - Separate tables per class
     * - Shows class teacher above each table
     */
    public function generateOverallCAClassPerformanceReportII($classId, $sequence)
    {
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $selectedTerm = Term::find($selectedTermId) ?? TermHelper::getCurrentTerm();
        $school_data = SchoolSetup::first();

        $test = Test::where('term_id', $selectedTermId)
            ->where('type', 'CA')
            ->where('sequence', $sequence)
            ->first();

        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;

        $grade = Grade::with(['klasses' => function ($query) use ($selectedTermId) {
            $query->with('teacher')->where('term_id', $selectedTermId);
        }])->findOrFail($gradeId);

        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get();

        $classPerformance = [];

        foreach ($grade->klasses as $class) {
            $year = $selectedTerm->year;
            $students = $class->currentStudents($selectedTermId, $year)->get();

            $gradeCounts = [
                'Merit' => 0, 'A' => 0, 'B' => 0, 'C' => 0,
                'D' => 0, 'E' => 0, 'U' => 0,
            ];
            $validStudentCount = 0;

            foreach ($students as $student) {
                $studentClass = $student->currentClass();
                if (!$studentClass) {
                    continue;
                }

                $isForeigner = $student->is_foreigner;
                $studentGradeSubjects = $allGradeSubjects->where('grade_id', $studentClass->grade_id);

                $hasValidTestResults = false;

                foreach ($studentGradeSubjects as $gradeSubject) {
                    $subjectData = AssessmentHelper::calculateSubjectScoresAnalysis(
                        $student,
                        $gradeSubject,
                        $selectedTermId,
                        $studentClass->grade_id
                    );

                    if ($subjectData['percentage'] !== null && $subjectData['percentage'] > 0) {
                        $hasValidTestResults = true;
                    }
                }

                if (!$hasValidTestResults) {
                    continue;
                }

                list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = AssessmentHelper::calculatePoints(
                    $student,
                    $studentGradeSubjects,
                    $selectedTermId,
                    $isForeigner
                );

                $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;

                if ($totalPoints <= 0 || $totalPoints === null) {
                    continue;
                }

                $gradeValue = AssessmentHelper::determineGrade($totalPoints, $studentClass);

                if (isset($gradeCounts[$gradeValue])) {
                    $gradeCounts[$gradeValue]++;
                    $validStudentCount++;
                }
            }

            $total = max($validStudentCount, 1);
            $mabCount = $gradeCounts['Merit'] + $gradeCounts['A'] + $gradeCounts['B'];
            $mabcCount = $mabCount + $gradeCounts['C'];
            $mabcdCount = $mabcCount + $gradeCounts['D'];
            $deuCount = $gradeCounts['D'] + $gradeCounts['E'] + $gradeCounts['U'];

            $classPerformance[$class->name] = [
                'className' => $class->name,
                'classTeacher' => $class->teacher->full_name ?? 'Not Assigned',
                'grades' => $gradeCounts,
                'total' => $validStudentCount,
                'MAB%' => round(($mabCount / $total) * 100, 2),
                'MABC%' => round(($mabcCount / $total) * 100, 2),
                'MABCD%' => round(($mabcdCount / $total) * 100, 2),
                'DEU%' => round(($deuCount / $total) * 100, 2),
            ];
        }

        // Calculate overall totals
        $overallTotals = [
            'grades' => ['Merit' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'U' => 0],
            'total' => 0,
            'MAB%' => 0,
            'MABC%' => 0,
            'MABCD%' => 0,
            'DEU%' => 0,
        ];

        foreach ($classPerformance as $perf) {
            foreach (['Merit', 'A', 'B', 'C', 'D', 'E', 'U'] as $g) {
                $overallTotals['grades'][$g] += $perf['grades'][$g];
            }
            $overallTotals['total'] += $perf['total'];
        }

        $grandTotal = max($overallTotals['total'], 1);
        $mabTotal = $overallTotals['grades']['Merit'] + $overallTotals['grades']['A'] + $overallTotals['grades']['B'];
        $mabcTotal = $mabTotal + $overallTotals['grades']['C'];
        $mabcdTotal = $mabcTotal + $overallTotals['grades']['D'];
        $deuTotal = $overallTotals['grades']['D'] + $overallTotals['grades']['E'] + $overallTotals['grades']['U'];

        $overallTotals['MAB%'] = round(($mabTotal / $grandTotal) * 100, 2);
        $overallTotals['MABC%'] = round(($mabcTotal / $grandTotal) * 100, 2);
        $overallTotals['MABCD%'] = round(($mabcdTotal / $grandTotal) * 100, 2);
        $overallTotals['DEU%'] = round(($deuTotal / $grandTotal) * 100, 2);

        return view('assessment.junior.overall-classes-analysis-ii', [
            'classPerformance' => $classPerformance,
            'school_data' => $school_data,
            'currentTerm' => $selectedTerm,
            'test' => $test,
            'overallTotals' => $overallTotals,
            'grade' => $grade,
            'reportType' => 'CA',
        ]);
    }

    /**
     * Generate subject grade distribution by class
     */
    public function subjectGradeDistributionByClass($classId, $type, $sequence){
        $klass = Klass::findOrFail($classId);
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        
        $klassSubjects = KlassSubject::with(['subject.subject', 'teacher'])
            ->where('klass_subject.klass_id', $classId)
            ->where('klass_subject.term_id', $termId)
            ->join('grade_subject as gs', 'klass_subject.grade_subject_id', '=', 'gs.id')
            ->orderByRaw('CASE WHEN gs.sequence IS NULL OR gs.sequence = 0 THEN 1 ELSE 0 END')
            ->orderBy('gs.sequence', 'asc')
            ->select('klass_subject.*')
            ->get();
        
        $students = $klass->students()->wherePivot('term_id', $termId)->get();
        
        $studentIds = $students->pluck('id')->toArray();
        $optionalSubjects = OptionalSubject::whereHas('students', function($query) use ($studentIds, $termId) {
            $query->whereIn('students.id', $studentIds)->where('student_optional_subjects.term_id', $termId);
        })->with(['gradeSubject.subject', 'teacher'])
          ->where('optional_subjects.term_id', $termId)
          ->join('grade_subject as gs', 'optional_subjects.grade_subject_id', '=', 'gs.id')
          ->orderByRaw('CASE WHEN gs.sequence IS NULL OR gs.sequence = 0 THEN 1 ELSE 0 END')
          ->orderBy('gs.sequence', 'asc')
          ->select('optional_subjects.*')
          ->get();
        
        $subjectsData = [];
        foreach ($klassSubjects as $klassSubject) {
            $gradeSubject = $klassSubject->subject;
            $subject = $gradeSubject->subject;
            
            $test = Test::where('grade_subject_id', $gradeSubject->id)
                ->where('type', $type)
                ->where('sequence', $sequence)
                ->where('term_id', $termId)
                ->first();
            
            
            if ($test) {
                $subjectsData[] = $this->calculateGradeDistribution($test, $students, $subject->name, $klassSubject->teacher);
            }
        }
        foreach ($optionalSubjects as $optionalSubject) {
            $gradeSubject = $optionalSubject->gradeSubject;
            $subject = $gradeSubject->subject;
            
            $test = Test::where('grade_subject_id', $gradeSubject->id)
                ->where('type', $type)
                ->where('sequence', $sequence)
                ->where('term_id', $termId)
                ->first();
            
            if ($test) {
                $subjectStudents = $optionalSubject->students()
                    ->wherePivot('term_id', $termId)
                    ->whereIn('students.id', $studentIds)
                    ->get();
                
                $subjectsData[] = $this->calculateGradeDistribution($test, $subjectStudents, $subject->name . ' (Optional)', $optionalSubject->teacher);
            }
        }
        
        $chartData = $this->prepareChartData($subjectsData);

        if (request()->has('export')) {
            $schoolData = SchoolSetup::first();
            return Excel::download(
                new \App\Exports\SubjectGradeDistributionExport(
                    $subjectsData, 
                    $klass, 
                    $type, 
                    $sequence, 
                    $schoolData ? $schoolData->school_name : ''
                ),
                "Subject_Grade_Distribution_{$klass->name}_{$type}_{$sequence}_" . date('Y-m-d') . ".xlsx"
            );
        }
        
        return view('assessment.shared.class-subject-grade-distribution-analysis', [
            'klass' => $klass,
            'test' => $test,
            'subjectsData' => $subjectsData,
            'chartData' => $chartData,
            'school_data' => SchoolSetup::first(),
        ]);
    }

    /**
     * Generate exam by grade analysis
     */
    public function generateExamByGradeAnalysis($classId){
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade->id;
        $currentTerm = TermHelper::getCurrentTerm();
    
        $students = Student::whereHas('classes', function ($query) use ($gradeId) {
            $query->whereHas('grade', function ($query) use ($gradeId) {
                $query->where('id', $gradeId);
            });
        })->get();
    
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $term = Term::find($selectedTermId);
    
        $school_setup = SchoolSetup::first();
        $allGradeSubjects = GradeSubject::where('grade_id', $klass->grade_id)
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get();
    
        $allSubjects = $allGradeSubjects->pluck('subject.name')->unique()->sort()->values()->toArray();
        $gradeCounts = [
            'M' => ['M' => 0, 'F' => 0],
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
            'X' => ['M' => 0, 'F' => 0],
        ];
    
        $subjectGradeCounts = [];
        foreach ($allSubjects as $subject) {
            $subjectGradeCounts[$subject] = [
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'U' => ['M' => 0, 'F' => 0],
                'X' => ['M' => 0, 'F' => 0],
                'total' => ['M' => 0, 'F' => 0],
                'enrolled' => ['M' => 0, 'F' => 0],
                'no_scores' => ['M' => 0, 'F' => 0],
            ];
        }
    
        $psleGradeCounts = [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
        ];
    
        $reportCardsData = [];
        foreach ($students as $student) {
            $isForeigner = $student->nationality !== 'Motswana';
            $psleGrade = optional($student->psle)->overall_grade;
    
            if ($psleGrade && isset($psleGradeCounts[$psleGrade])) {
                $gender = $student->gender === 'M' ? 'M' : 'F';
                $psleGradeCounts[$psleGrade][$gender]++;
            }
    
            $hasParticipated = false;
            $subjectScores = [];
            $studentSubjectEnrollments = [];
    
            foreach ($allGradeSubjects as $gradeSubject) {
                $subjectName = optional($gradeSubject->subject)->name ?? 'Unknown Subject';
                
                $isEnrolled = false;
                $currentClass = $student->currentClass();
                
                if ($currentClass) {
                    $isInClassCurriculum = $currentClass->subjectClasses()
                        ->whereHas('subject', function($q) use ($gradeSubject) {
                            $q->where('grade_subject_id', $gradeSubject->id);
                        })->exists();
                        
                    $isInOptionalSubjects = $student->optionalSubjects()
                        ->where('grade_subject_id', $gradeSubject->id)
                        ->exists();
                        
                    $isEnrolled = $isInClassCurriculum || $isInOptionalSubjects;
                }
                
                if (!isset($studentSubjectEnrollments[$student->id])) {
                    $studentSubjectEnrollments[$student->id] = [];
                }
                $studentSubjectEnrollments[$student->id][$subjectName] = $isEnrolled;
                
                $subjectData = AssessmentHelper::calculateSubjectScoresAnalysis(
                    $student,
                    $gradeSubject,
                    $selectedTermId,
                    $klass->grade_id
                );
            
                if (!is_null($subjectData['percentage'])) {
                    $hasParticipated = true;
                }
            
                $subGrade = $subjectData['grade'] ?? 'X';
                $subPercentage = $subjectData['percentage'] ?? null;
                $subPoints = $subjectData['points'] ?? null;
            
                $subjectScores[$subjectName] = [
                    'percentage' => $subPercentage,
                    'grade' => $subGrade,
                    'enrolled' => $isEnrolled
                ];
                $subjectPoints[$gradeSubject->id] = $subPoints;
            }

            foreach ($allSubjects as $subject) {
                if (!isset($subjectScores[$subject])) {
                    $subjectScores[$subject] = [
                        'percentage' => null,
                        'grade' => 'X',
                        'enrolled' => false
                    ];
                }
            }
    
            list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = AssessmentHelper::calculatePoints(
                $student,
                $allGradeSubjects,
                $selectedTermId,
                $isForeigner
            );
            $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;
            $currentClass = $student->currentClass();
            $className = $currentClass ? $currentClass->name : '';
    
            $overallGrade = $hasParticipated
                ? AssessmentHelper::determineGrade($totalPoints, $currentClass)
                : 'X';
    
            if ($overallGrade === 'Merit') {
                $overallGrade = 'M';
            }
    
            $reportCardsData[] = [
                'student' => $student,
                'scores' => $subjectScores,
                'totalPoints' => $hasParticipated ? $totalPoints : 'X',
                'grade' => $overallGrade,
                'class_name' => $className,
            ];
        }
    
        usort($reportCardsData, function($a, $b) {
            $aPts = is_numeric($a['totalPoints']) ? $a['totalPoints'] : -1;
            $bPts = is_numeric($b['totalPoints']) ? $b['totalPoints'] : -1;
            if ($aPts !== $bPts) {
                return $bPts <=> $aPts;
            }
    
            $sumA = array_sum(array_map(fn($s) => is_numeric($s['percentage']) ? $s['percentage'] : 0, $a['scores']));
            $sumB = array_sum(array_map(fn($s) => is_numeric($s['percentage']) ? $s['percentage'] : 0, $b['scores']));
            return $sumB <=> $sumA;
        });
    
        foreach ($reportCardsData as $reportCard) {
            if (!isset($reportCard['student'])) {
                continue;
            }
        
            $gender = $reportCard['student']->gender === 'M' ? 'M' : 'F';
            foreach ($reportCard['scores'] as $subject => $data) {
                if (!is_array($data)) {
                    continue;
                }
                
                $isEnrolled = isset($data['enrolled']) ? $data['enrolled'] : false;
                if ($isEnrolled && isset($subjectGradeCounts[$subject]['enrolled'][$gender])) {
                    $subjectGradeCounts[$subject]['enrolled'][$gender]++;
                }
                
                if (!$isEnrolled) {
                    continue;
                }
                
                $grade = isset($data['grade']) ? $data['grade'] : 'X';
                $hasScore = isset($data['percentage']) && !is_null($data['percentage']);
                
                if (!$hasScore && isset($subjectGradeCounts[$subject]['no_scores'][$gender])) {
                    $subjectGradeCounts[$subject]['no_scores'][$gender]++;
                }
                
                if (isset($subjectGradeCounts[$subject][$grade][$gender])) {
                    $subjectGradeCounts[$subject][$grade][$gender]++;
                    $subjectGradeCounts[$subject]['total'][$gender]++;
                }
            }
        }
        
        foreach ($reportCardsData as $index => &$data) {
            $data['position'] = $index + 1;
        }
        unset($data);
        $maleCount = 0;
        $femaleCount = 0;
    
        foreach ($reportCardsData as $reportCard) {
            $grade = $reportCard['grade'] ?? 'X';
            $gender = $reportCard['student']->gender === 'M' ? 'M' : 'F';
    
            if (isset($gradeCounts[$grade][$gender])) {
                $gradeCounts[$grade][$gender]++;
            }
    
            if ($gender === 'M') {
                $maleCount++;
            } else {
                $femaleCount++;
            }
        }
    
        $totalStudents = count($reportCardsData);
        $safePercentage = function($count, $total) {
            return AssessmentHelper::formatPercentage($count, $total);
        };
    
        $sumM = $gradeCounts['M']['M'] + $gradeCounts['M']['F'];
        $sumA = $gradeCounts['A']['M'] + $gradeCounts['A']['F'];
        $sumB = $gradeCounts['B']['M'] + $gradeCounts['B']['F'];
        $sumC = $gradeCounts['C']['M'] + $gradeCounts['C']['F'];
        $sumD = $gradeCounts['D']['M'] + $gradeCounts['D']['F'];
        $sumE = $gradeCounts['E']['M'] + $gradeCounts['E']['F'];
        $sumU = $gradeCounts['U']['M'] + $gradeCounts['U']['F'];
        $sumX_M = $gradeCounts['X']['M'];
        $sumX_F = $gradeCounts['X']['F'];
        $sumX = $sumX_M + $sumX_F;
    
        $m_M = $gradeCounts['M']['M']; $m_F = $gradeCounts['M']['F'];
        $a_M = $gradeCounts['A']['M']; $a_F = $gradeCounts['A']['F'];
        $b_M = $gradeCounts['B']['M']; $b_F = $gradeCounts['B']['F'];
        $c_M = $gradeCounts['C']['M']; $c_F = $gradeCounts['C']['F'];
        $d_M = $gradeCounts['D']['M']; $d_F = $gradeCounts['D']['F'];
        $e_M = $gradeCounts['E']['M']; $e_F = $gradeCounts['E']['F'];
        $u_M = $gradeCounts['U']['M']; $u_F = $gradeCounts['U']['F'];
        $x_M = $gradeCounts['X']['M']; $x_F = $gradeCounts['X']['F'];
    
        $mabCount = $sumM + $sumA + $sumB; 
        $mabcCount = $mabCount + $sumC;
        $mabcdCount = $mabcCount + $sumD;
        $deuCount = $sumD + $sumE + $sumU;
    
        $mab_M = $m_M + $a_M + $b_M;
        $mabc_M = $mab_M + $c_M;
        $mabcd_M = $mabc_M + $d_M;
        $deu_M = $d_M + $e_M + $u_M;
    
        $mab_F = $m_F + $a_F + $b_F;
        $mabc_F = $mab_F + $c_F;
        $mabcd_F = $mabc_F + $d_F;
        $deu_F = $d_F + $e_F + $u_F;
    
        $mab_T = $mab_M + $mab_F;
        $mabc_T = $mabc_M + $mabc_F;
        $mabcd_T = $mabcd_M + $mabcd_F;
        $deu_T = $deu_M + $deu_F;
        $x_T = $x_M + $x_F;
    
        $mabPercentage = $safePercentage($mabCount, $totalStudents);
        $mabcPercentage = $safePercentage($mabcCount, $totalStudents);
        $mabcdPercentage = $safePercentage($mabcdCount, $totalStudents);
        $deuPercentage = $safePercentage($deuCount, $totalStudents);
    
        $mab_M_Percentage = $safePercentage($mab_M, $maleCount);
        $mab_F_Percentage = $safePercentage($mab_F, $femaleCount);
        $mab_T_percentage = $safePercentage($mab_T, $totalStudents);
        $mabc_T_percentage = $safePercentage($mabc_T, $totalStudents);
        $mabcd_T_percentage = $safePercentage($mabcd_T, $totalStudents);
        $deu_T_percentage = $safePercentage($deu_T, $totalStudents);
        $x_T_Percentange = $safePercentage($x_T, $totalStudents);
    
        $mabc_M_Percentage = $safePercentage($mabc_M, $maleCount);
        $mabc_F_Percentage = $safePercentage($mabc_F, $femaleCount);
    
        $mabcd_M_Percentage = $safePercentage($mabcd_M, $maleCount);
        $mabcd_F_Percentage = $safePercentage($mabcd_F, $femaleCount);
    
        $deu_M_Percentage = $safePercentage($deu_M, $maleCount);
        $deu_F_Percentage = $safePercentage($deu_F, $femaleCount);
    
        $x_M_Percentage = $safePercentage($x_M, $maleCount);
        $x_F_Percentage = $safePercentage($x_F, $femaleCount);
    
        foreach ($subjectGradeCounts as $subject => &$counts) {
            foreach (['M', 'F'] as $gender) {
                $enrolled = $counts['enrolled'][$gender];
        
                $abCount = $counts['A'][$gender] + $counts['B'][$gender];
                $abcCount = $abCount + $counts['C'][$gender];
                $abcdCount = $abcCount + $counts['D'][$gender];
                $deuCount = $counts['D'][$gender] + $counts['E'][$gender] + $counts['U'][$gender];
                $xCount = $counts['X'][$gender];
        
                $counts['AB%'][$gender] = $enrolled > 0 ? round(($abCount / $enrolled) * 100, 2) : 0;
                $counts['ABC%'][$gender] = $enrolled > 0 ? round(($abcCount / $enrolled) * 100, 2) : 0;
                $counts['ABCD%'][$gender] = $enrolled > 0 ? round(($abcdCount / $enrolled) * 100, 2) : 0;
                $counts['DEU%'][$gender] = $enrolled > 0 ? round(($deuCount / $enrolled) * 100, 2) : 0;
                $counts['X%'][$gender] = $enrolled > 0 ? round(($xCount / $enrolled) * 100, 2) : 0;
            }
        }
        
        unset($counts);
    
        $subjectTotals = [
            'A'=>['M'=>0,'F'=>0], 'B'=>['M'=>0,'F'=>0], 'C'=>['M'=>0,'F'=>0],
            'D'=>['M'=>0,'F'=>0], 'E'=>['M'=>0,'F'=>0], 'U'=>['M'=>0,'F'=>0],
            'X'=>['M'=>0,'F'=>0],
            'AB%'=>['M'=>0,'F'=>0],'ABC%'=>['M'=>0,'F'=>0],
            'ABCD%'=>['M'=>0,'F'=>0],'DEU%'=>['M'=>0,'F'=>0],'X%'=>['M'=>0,'F'=>0],
            'total'=>['M'=>0,'F'=>0],
            'enrolled'=>['M'=>0,'F'=>0],
            'no_scores'=>['M'=>0,'F'=>0]
        ];
    
        foreach ($subjectGradeCounts as $subj => $c) {
            foreach (['A','B','C','D','E','U','X'] as $g) {
                $subjectTotals[$g]['M'] += $c[$g]['M'];
                $subjectTotals[$g]['F'] += $c[$g]['F'];
            }
            $subjectTotals['total']['M'] += $c['total']['M'];
            $subjectTotals['total']['F'] += $c['total']['F'];
            $subjectTotals['enrolled']['M'] += $c['enrolled']['M'];
            $subjectTotals['enrolled']['F'] += $c['enrolled']['F'];
            $subjectTotals['no_scores']['M'] += $c['no_scores']['M'];
            $subjectTotals['no_scores']['F'] += $c['no_scores']['F'];
    
            foreach (['AB%','ABC%','ABCD%','DEU%','X%'] as $k) {
                $subjectTotals[$k]['M'] += $c[$k]['M'];
                $subjectTotals[$k]['F'] += $c[$k]['F'];
            }
        }
    
        $div = max(count($subjectGradeCounts),1);
        foreach (['AB%','ABC%','ABCD%','DEU%','X%'] as $k) {
            $subjectTotals[$k]['M'] = round($subjectTotals[$k]['M'] / $div, 2);
            $subjectTotals[$k]['F'] = round($subjectTotals[$k]['F'] / $div, 2);
        }
    
        $psleTotalM = array_sum(array_column($psleGradeCounts, 'M'));
        $psleTotalF = array_sum(array_column($psleGradeCounts, 'F'));
        $totalPsleStudents = $psleTotalM + $psleTotalF;
    
        $psleA_M = $psleGradeCounts['A']['M']; $psleA_F = $psleGradeCounts['A']['F'];
        $psleB_M = $psleGradeCounts['B']['M']; $psleB_F = $psleGradeCounts['B']['F'];
        $psleC_M = $psleGradeCounts['C']['M']; $psleC_F = $psleGradeCounts['C']['F'];
        $psleD_M = $psleGradeCounts['D']['M']; $psleD_F = $psleGradeCounts['D']['F'];
        $psleE_M = $psleGradeCounts['E']['M']; $psleE_F = $psleGradeCounts['E']['F'];
        $psleU_M = $psleGradeCounts['U']['M']; $psleU_F = $psleGradeCounts['U']['F'];
    
        $psleAB_M = $psleA_M + $psleB_M; $psleAB_F = $psleA_F + $psleB_F;
        $psleAB_T = $psleAB_M + $psleAB_F;
        $psleABC_M = $psleAB_M + $psleC_M; $psleABC_F = $psleAB_F + $psleC_F;
        $psleABC_T = $psleABC_M + $psleABC_F;
        $psleABCD_M = $psleABC_M + $psleD_M; $psleABCD_F = $psleABC_F + $psleD_F;
        $psleABCD_T = $psleABCD_M + $psleABCD_F;
        $psleDEU_M = $psleD_M + $psleE_M + $psleU_M; $psleDEU_F = $psleD_F + $psleE_F + $psleU_F;
        $psleDEU_T = $psleDEU_M + $psleDEU_F;
    
        $psleAB_M_Percentage = $safePercentage($psleAB_M, $psleTotalM);
        $psleAB_F_Percentage = $safePercentage($psleAB_F, $psleTotalF);
        $psleAB_T_percentage = $safePercentage($psleAB_T, $totalPsleStudents);
    
        $psleABC_M_Percentage = $safePercentage($psleABC_M, $psleTotalM);
        $psleABC_F_Percentage = $safePercentage($psleABC_F, $psleTotalF);
        $psleABC_T_percentage = $safePercentage($psleABC_T, $totalPsleStudents);
    
        $psleABCD_M_Percentage = $safePercentage($psleABCD_M, $psleTotalM);
        $psleABCD_F_Percentage = $safePercentage($psleABCD_F, $psleTotalF);
        $psleABCD_T_percentage = $safePercentage($psleABCD_T, $totalPsleStudents);
    
        $psleDEU_M_Percentage = $safePercentage($psleDEU_M, $psleTotalM);
        $psleDEU_F_Percentage = $safePercentage($psleDEU_F, $psleTotalF);
        $psleDEU_T_percentage = $safePercentage($psleDEU_T, $totalPsleStudents);
    
        $data = [
            'reportCards' => $reportCardsData,
            'school_data' => $school_setup,
            'allSubjects' => $allSubjects,
            'currentTerm' => $term,
            'klass' => $klass,
            'gradeCounts' => $gradeCounts,
            'subjectGradeCounts' => $subjectGradeCounts,
            'totalStudents' => $totalStudents,
            'psleGradeCounts' => $psleGradeCounts,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
    
            'mabCount' => $mabCount,
            'mabcCount' => $mabcCount,
            'mabcdCount' => $mabcdCount,
            'deuCount' => $deuCount,
            'mabPercentage' => $mabPercentage,
            'mabcPercentage' => $mabcPercentage,
            'mabcdPercentage' => $mabcdPercentage,
            'deuPercentage' => $deuPercentage,
    
            'x_T_Percentage' => $x_T_Percentange,
            'x_M_Percentage' => $x_M_Percentage,
            'x_F_Percentage' => $x_F_Percentage,
    
            'mab_M' => $mab_M,
            'mab_F' => $mab_F,
            'mabc_M' => $mabc_M,
            'mabc_F' => $mabc_F,
            'mabcd_M' => $mabcd_M,
            'mabcd_F' => $mabcd_F,
            'deu_M' => $deu_M,
            'deu_F' => $deu_F,
            'mab_T' => $mab_T,
            'mabc_T' => $mabc_T,
            'mabcd_T' => $mabcd_T,
            'deu_T' => $deu_T,
            'x_M' => $x_M,
            'x_F' => $x_F,
            'x_T' => $x_T,
    
            'mab_M_Percentage' => $mab_M_Percentage,
            'mab_F_Percentage' => $mab_F_Percentage,
            'mabc_M_Percentage' => $mabc_M_Percentage,
            'mabc_F_Percentage' => $mabc_F_Percentage,
            'mabcd_M_Percentage' => $mabcd_M_Percentage,
            'mabcd_F_Percentage' => $mabcd_F_Percentage,
            'deu_M_Percentage' => $deu_M_Percentage,
            'deu_F_Percentage' => $deu_F_Percentage,
            'mab_T_percentage' => $mab_T_percentage,
            'mabc_T_percentage' => $mabc_T_percentage,
            'mabcd_T_percentage' => $mabcd_T_percentage,
            'deu_T_percentage' => $deu_T_percentage,
    
            'psleTotalM' => $psleTotalM,
            'psleTotalF' => $psleTotalF,
    
            'psleAB_M' => $psleAB_M,
            'psleAB_F' => $psleAB_F,
            'psleABC_M' => $psleABC_M,
            'psleABC_F' => $psleABC_F,
            'psleABCD_M' => $psleABCD_M,
            'psleABCD_F' => $psleABCD_F,
            'psleDEU_M' => $psleDEU_M,
            'psleDEU_F' => $psleDEU_F,
            'psleAB_T' => $psleAB_T,
            'psleABC_T' => $psleABC_T,
            'psleABCD_T' => $psleABCD_T,
            'psleDEU_T' => $psleDEU_T,
    
            'psleAB_M_Percentage' => $psleAB_M_Percentage,
            'psleAB_F_Percentage' => $psleAB_F_Percentage,
            'psleABC_M_Percentage' => $psleABC_M_Percentage,
            'psleABC_F_Percentage' => $psleABC_F_Percentage,
            'psleABCD_M_Percentage' => $psleABCD_M_Percentage,
            'psleABCD_F_Percentage' => $psleABCD_F_Percentage,
            'psleDEU_M_Percentage' => $psleDEU_M_Percentage,
            'psleDEU_F_Percentage' => $psleDEU_F_Percentage,
    
            'psleAB_T_Percentage' => $psleAB_T_percentage,
            'psleABC_T_Percentage' => $psleABC_T_percentage,
            'psleABCD_T_Percentage' => $psleABCD_T_percentage,
            'psleDEU_T_Percentage' => $psleDEU_T_percentage,
    
            'subjectTotals' => $subjectTotals,
        ];
    
        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\CAAnalysisExport($data),
                "Exam_Analysis_{$klass->name}_" . date('Y-m-d') . ".xlsx"
            );
        }
    
        return view('assessment.shared.overall-exam-grade-analysis', $data);
    }

    /**
     * Generate value addition analysis for grade
     */
    public function generateValueAdditionAnalysisForGrade(Request $request, $classId, $type, $sequenceId){
        $klass = Klass::findOrFail($classId);
        $grade = Grade::findOrFail($klass->grade_id);

        $gradeId = $grade->id;
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $term = Term::findOrFail($selectedTermId);

        $school_setup = SchoolSetup::first();
        $test = Test::where('grade_id', $gradeId)->where('term_id', $selectedTermId)->where('type', $type)->where('sequence', $sequenceId)->first();
        $allGradeSubjects = GradeSubject::where('grade_id', $grade->id)->where('term_id', $selectedTermId)->with('subject')->get();

        $subjectMapping = [];
        foreach ($allGradeSubjects as $gradeSubject) {
            if ($gradeSubject->subject) {
                $subjectName = strtoupper(substr($gradeSubject->subject->name, 0, 3));
                $subjectMapping[$subjectName] = $gradeSubject;
            } else {
                Log::warning("GradeSubject ID {$gradeSubject->id} has no associated subject for grade {$grade->id}. It will be skipped.");
            }
        }

        $jcSubjects = array_keys($subjectMapping);
        $psleSubjectKeysInPsleModel = PSLE::getSubjects();

        $gradeCategories = ['M', 'A', 'B', 'C', 'D', 'E', 'U'];
        $psleGradeCategories = ['A', 'B', 'C', 'D', 'E', 'U'];

        $psleOverallGradeCounts = array_fill_keys($psleGradeCategories, 0);
        $jcOverallGradeCounts = array_fill_keys($gradeCategories, 0);
        $gradeShiftMatrix = array_fill_keys($psleGradeCategories, array_fill_keys($gradeCategories, 0));
        
        $highPsleAchievers = [];

        $gradeCounts = [];
        foreach ($jcSubjects as $subject) {
            $gradeCounts[$subject] = [
                'PSLE' => array_fill_keys($psleGradeCategories, 0),
                'JC' => array_fill_keys($gradeCategories, 0),
                'totalPSLE' => 0,
                'totalJC' => 0,
                'qualityPSLE' => 0,
                'quantityPSLE' => 0,
                'qualityJC' => 0,
                'quantityJC' => 0,
                'valueAddition' => 0,
            ];
        }

        $studentsInGrade = Student::whereHas('terms', function ($query) use ($selectedTermId, $gradeId) {
            $query->where('student_term.term_id', $selectedTermId)
                  ->where('student_term.grade_id', $gradeId)
                  ->where('student_term.status', 'Current');
        })->with('psle', 'currentClassRelation')->get();

        foreach ($studentsInGrade as $student) {
            $psleRecord = $student->psle;
            if (!$psleRecord) {
                continue;
            }

            $overallGradePSLE = $psleRecord->overall_grade ?? 'U';
            if (array_key_exists($overallGradePSLE, $psleOverallGradeCounts)) {
                 $psleOverallGradeCounts[$overallGradePSLE]++;
            }

            $hasParticipatedInJC = false;

            foreach ($jcSubjects as $jcSubjectCode) {
                $gradeSubjectInstance = $subjectMapping[$jcSubjectCode] ?? null;

                if (!$gradeSubjectInstance || !$gradeSubjectInstance->subject) {
                    Log::warning("No valid GradeSubject instance or subject for code {$jcSubjectCode} in grade {$grade->id}. Skipping subject for student {$student->id}.");
                    $gradeCounts[$jcSubjectCode]['PSLE']['U']++; 
                    $gradeCounts[$jcSubjectCode]['totalPSLE']++;
                    $gradeCounts[$jcSubjectCode]['totalJC']++; 
                    continue;
                }
                
                $jcSubjectPerformance = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                    $student,
                    $gradeSubjectInstance,
                    $selectedTermId,
                    $type,
                    $sequenceId,
                    $grade->id
                );

                $jcActualGrade = $jcSubjectPerformance['grade'] ?? 'U';
                $jcPercentage = $jcSubjectPerformance['percentage'];

                if ($jcActualGrade === 'Merit') $jcActualGrade = 'M';

                if (array_key_exists($jcActualGrade, $gradeCounts[$jcSubjectCode]['JC'])) {
                    $gradeCounts[$jcSubjectCode]['JC'][$jcActualGrade]++;
                } else {
                    $gradeCounts[$jcSubjectCode]['JC']['U']++;
                }
                $gradeCounts[$jcSubjectCode]['totalJC']++;

                if (!is_null($jcPercentage)) {
                    $hasParticipatedInJC = true;
                }

                $psleSubjectAttribute = null;
                $fullJcSubjectNameLower = strtolower($gradeSubjectInstance->subject->name);

                foreach ($psleSubjectKeysInPsleModel as $psleKey) {
                    if (str_contains($fullJcSubjectNameLower, strtolower($psleKey))) {
                         $psleSubjectAttribute = $psleKey;
                         break;
                    }
                }
                
                $psleSubjectGrade = $overallGradePSLE;
                if ($psleSubjectAttribute && isset($psleRecord->{$psleSubjectAttribute})) {
                    $psleSubjectGrade = $psleRecord->{$psleSubjectAttribute} ?? 'U';
                }

                if (array_key_exists($psleSubjectGrade, $gradeCounts[$jcSubjectCode]['PSLE'])) {
                     $gradeCounts[$jcSubjectCode]['PSLE'][$psleSubjectGrade]++;
                } else {
                    $gradeCounts[$jcSubjectCode]['PSLE']['U']++;
                }
                $gradeCounts[$jcSubjectCode]['totalPSLE']++;
            }

            list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = $this->calculatePoints(
                $student,
                $allGradeSubjects,
                $selectedTermId,
                $student->nationality !== 'Motswana',
                $type,
                $sequenceId
            );
            
            $totalPointsJC = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;
            $overallGradeJC = $hasParticipatedInJC
                ? AssessmentHelper::determineGrade($totalPointsJC, $klass) 
                : 'U';
            if ($overallGradeJC === 'Merit') $overallGradeJC = 'M';
        
            if (array_key_exists($overallGradeJC, $jcOverallGradeCounts)) {
                 $jcOverallGradeCounts[$overallGradeJC]++;
            } else {
                $jcOverallGradeCounts['U']++;
            }

            if (array_key_exists($overallGradePSLE, $gradeShiftMatrix) && array_key_exists($overallGradeJC, $gradeShiftMatrix[$overallGradePSLE])) {
                $gradeShiftMatrix[$overallGradePSLE][$overallGradeJC]++;
            }

            if (in_array($overallGradePSLE, ['A','B','C'])) {
                $highPsleAchievers[] = [
                    'name'       => $student->full_name,
                    'psle_grade' => $overallGradePSLE,
                    'jc_grade'   => $overallGradeJC,
                    'jc_points'  => $totalPointsJC, 
                ];
            }            
        } 

        $valueAdditions = [];
        foreach ($jcSubjects as $subject) {
            $totalPSLEForSubject = max($gradeCounts[$subject]['totalPSLE'], 1);
            $totalJCForSubject = max($gradeCounts[$subject]['totalJC'], 1);

            $qualityPSLE = ($gradeCounts[$subject]['PSLE']['A'] ?? 0) + 
                           ($gradeCounts[$subject]['PSLE']['B'] ?? 0) +
                           ($gradeCounts[$subject]['PSLE']['C'] ?? 0);
            $qualityJC = ($gradeCounts[$subject]['JC']['M'] ?? 0) + 
                         ($gradeCounts[$subject]['JC']['A'] ?? 0) + 
                         ($gradeCounts[$subject]['JC']['B'] ?? 0) +
                         ($gradeCounts[$subject]['JC']['C'] ?? 0);
            
            $quantityPSLE = $qualityPSLE;
            $quantityJC = $qualityJC;

            $gradeCounts[$subject]['qualityPSLE'] = round(($qualityPSLE / $totalPSLEForSubject) * 100, 0);
            $gradeCounts[$subject]['quantityPSLE'] = round(($quantityPSLE / $totalPSLEForSubject) * 100, 0);
            $gradeCounts[$subject]['qualityJC'] = round(($qualityJC / $totalJCForSubject) * 100, 0);
            $gradeCounts[$subject]['quantityJC'] = round(($quantityJC / $totalJCForSubject) * 100, 0);

            $gradeCounts[$subject]['valueAddition'] = $gradeCounts[$subject]['qualityJC'] - $gradeCounts[$subject]['qualityPSLE'];
            $valueAdditions[$subject] = $gradeCounts[$subject]['valueAddition'];
        }

        $totalPsleStudentsOverall = max(array_sum($psleOverallGradeCounts), 1);
        $overallQualityPSLECount = ($psleOverallGradeCounts['A'] ?? 0) + 
                                   ($psleOverallGradeCounts['B'] ?? 0) +
                                   ($psleOverallGradeCounts['C'] ?? 0);
        $overallQualityPSLEPercent = round(($overallQualityPSLECount / $totalPsleStudentsOverall) * 100, 0);

        $totalJcStudentsOverall = max(array_sum($jcOverallGradeCounts), 1);
        $overallQualityJCCount = ($jcOverallGradeCounts['M'] ?? 0) + 
                                 ($jcOverallGradeCounts['A'] ?? 0) + 
                                 ($jcOverallGradeCounts['B'] ?? 0) +
                                 ($jcOverallGradeCounts['C'] ?? 0);
        $overallQualityJCPercent = round(($overallQualityJCCount / $totalJcStudentsOverall) * 100, 0);

        $valueAdditions['overall'] = $overallQualityJCPercent - $overallQualityPSLEPercent;

        arsort($valueAdditions);
        $rankedSubjects = array_keys(array_filter($valueAdditions, function($key) {
            return $key !== 'overall';
        }, ARRAY_FILTER_USE_KEY));

        usort($highPsleAchievers, function($a, $b) {
            $gradeOrder = ['A' => 1, 'B' => 2, 'C' => 3];
            $gradeComparison = $gradeOrder[$a['psle_grade']] - $gradeOrder[$b['psle_grade']];
            
            if ($gradeComparison === 0) {
                return strcmp($a['name'], $b['name']);
            }
            
            return $gradeComparison;
        });

        $data = [
            'grade' => $grade,
            'term' => $term,
            'type' => $type,
            'test' => $test,
            'sequenceId' => $sequenceId,
            'school_data' => $school_setup,
            'jcSubjects' => $jcSubjects,
            'gradeCategories' => $gradeCategories,
            'psleGradeCategories' => $psleGradeCategories,
            'gradeCounts' => $gradeCounts,
            'psleOverallGradeCounts' => $psleOverallGradeCounts,
            'jcOverallGradeCounts' => $jcOverallGradeCounts,
            'valueAdditions' => $valueAdditions,
            'rankedSubjects' => $rankedSubjects,
            'gradeShiftMatrix' => $gradeShiftMatrix,
            'highPsleAchievers' => $highPsleAchievers,
        ];

        $valueAdditionChartLabels = $rankedSubjects;
        $valueAdditionChartData = [];
        foreach($rankedSubjects as $subject) {
            $valueAdditionChartData[] = $valueAdditions[$subject];
        }
        $data['valueAdditionChart'] = [
            'labels' => $valueAdditionChartLabels,
            'data' => $valueAdditionChartData,
        ];

        if ($request->input('export') === 'true') {
            return Excel::download(
                new \App\Exports\GradeValueAnalysisExport($data),
                "Grade_Value_Addition_Analysis_{$grade->name}_" . date('Y-m-d') . ".xlsx"
            );
        }
        return view('assessment.shared.overall-value-addition-analysis', $data);
    }

    /**
     * Generate test comparison analysis for grade
     */
    public function generateTestComparisonAnalysisForGrade(int $classId, string $type, int $sequenceId){
        $currentTerm    = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $schoolSetup    = SchoolSetup::first();

        $klass   = Klass::findOrFail($classId);
        $grade = Grade::findOrFail($klass->grade_id);
        $gradeId = $grade->id;

        $term = Term::find($selectedTermId);
        $test = Test::where('grade_id', $gradeId)->where('term_id', $selectedTermId)->where('type', $type)->where('sequence', $sequenceId)->first();

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;

        // Compare with same type and sequence from previous term
        $prevTerm     = TermHelper::getPreviousTerm($selectedTerm);
        $prevType     = $type;        // Same test type (CA or Exam)
        $prevSequence = $sequenceId;  // Same sequence number

        if (!$prevTerm) {
            return view('assessment.shared.test-comparison-grade-analysis', [
                'error' => 'No previous term data available for comparison.',
                'school_data' => $schoolSetup,
                'grade' => $grade,
            ]);
        }
        
        $prevTermId = $prevTerm->id;
        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)->where('term_id', $selectedTermId)->where('active', 1)->with('subject')->orderByRaw('CASE WHEN sequence IS NULL OR sequence = 0 THEN 1 ELSE 0 END')->orderBy('sequence', 'asc')->get();
        $subjectMapping = [];
        foreach ($allGradeSubjects as $gs) {
            $code = strtoupper(substr($gs->subject->name, 0, 3));
            $subjectMapping[$code] = $gs;
        }
        $subjects        = array_keys($subjectMapping);
        $gradeCategories = ['A','B','C','D','E','U'];
    
        $gradeCounts = [];
        foreach ($subjects as $sub) {
            $gradeCounts[$sub] = [
                'prev'          => array_fill_keys($gradeCategories, 0),
                'curr'          => array_fill_keys($gradeCategories, 0),
                'totalPrev'     => 0,
                'totalCurr'     => 0,
                'qualityPrev'   => 0,
                'quantityPrev'  => 0,
                'qualityCurr'   => 0,
                'quantityCurr'  => 0,
                'valueAddition' => 0,
            ];
        }
    
        $students = Student::join('klass_student', 'students.id', '=', 'klass_student.student_id')
                           ->join('klasses', 'klass_student.klass_id', '=', 'klasses.id')
                           ->where('klasses.grade_id', $gradeId)
                           ->where('klass_student.term_id', $selectedTermId)
                           ->where('klass_student.active', 1)
                           ->whereNull('students.deleted_at')
                           ->whereNull('klasses.deleted_at')
                           ->select('students.*')
                           ->distinct()
                           ->get();
    
        foreach ($students as $student) {
            foreach ($subjects as $sub) {
                $gs = $subjectMapping[$sub];
    
                $prev = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                    $student, $gs, $prevTermId, $prevType, $prevSequence, $gradeId
                );
                $pG   = $prev['grade'] ?? 'U';
                $gradeCounts[$sub]['prev'][$pG]++;
                $gradeCounts[$sub]['totalPrev']++;
    
                $curr = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                    $student, $gs, $selectedTermId, $type, $sequenceId, $gradeId
                );
                $cG   = $curr['grade'] ?? 'U';
                $gradeCounts[$sub]['curr'][$cG]++;
                $gradeCounts[$sub]['totalCurr']++;
            }
        }
    
        $valueAdditions = [];
        $sumPrevAB = $sumCurrAB = 0;
        foreach ($subjects as $sub) {
            $totP  = max($gradeCounts[$sub]['totalPrev'], 1);
            $totC  = max($gradeCounts[$sub]['totalCurr'], 1);
            $qualP = $gradeCounts[$sub]['prev']['A'] + $gradeCounts[$sub]['prev']['B'];
            $qualC = $gradeCounts[$sub]['curr']['A'] + $gradeCounts[$sub]['curr']['B'];
            $quanP = $qualP + $gradeCounts[$sub]['prev']['C'];
            $quanC = $qualC + $gradeCounts[$sub]['curr']['C'];
    
            $gradeCounts[$sub]['qualityPrev']  = round(($qualP / $totP) * 100);
            $gradeCounts[$sub]['quantityPrev'] = round(($quanP / $totP) * 100);
            $gradeCounts[$sub]['qualityCurr']  = round(($qualC / $totC) * 100);
            $gradeCounts[$sub]['quantityCurr'] = round(($quanC / $totC) * 100);
    
            $gradeCounts[$sub]['valueAddition'] = round(
                $gradeCounts[$sub]['qualityCurr'] - $gradeCounts[$sub]['qualityPrev'], 2
            );
    
            $valueAdditions[$sub] = $gradeCounts[$sub]['valueAddition'];
            $sumPrevAB += $qualP;
            $sumCurrAB += $qualC;
        }
    
        $valueAdditions['overall'] = $sumCurrAB - $sumPrevAB;
    
        $prevTotals = array_fill_keys($gradeCategories, 0);
        $currTotals = array_fill_keys($gradeCategories, 0);
        foreach ($gradeCounts as $cnt) {
            foreach ($gradeCategories as $g) {
                $prevTotals[$g] += $cnt['prev'][$g];
                $currTotals[$g] += $cnt['curr'][$g];
            }
        }
    
        arsort($valueAdditions);
        $rankedSubjects = array_keys($valueAdditions);
    
        $data = [
            'school_data'     => $schoolSetup,
            'subjects'        => $subjects,
            'gradeCounts'     => $gradeCounts,
            'prevGradeCounts' => $prevTotals,
            'currGradeCounts' => $currTotals,
            'valueAdditions'  => $valueAdditions,
            'rankedSubjects'  => $rankedSubjects,
            'prevType'        => $prevType,
            'prevSequence'    => $prevSequence,
            'type'            => $type,
            'sequenceId'      => $sequenceId,
            'grade'           => $grade,
            'selectedTerm'    => $selectedTerm,
            'prevTerm'        => $prevTerm,
            'test'            => $test,
            'term'            => $term,
        ];
    
        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\TestComparisonAnalysisExport($data),
                "Subject_Comparison_{$grade->name}_" . date('Y-m-d') . ".xlsx"
            );
        }
    
        return view('assessment.shared.test-comparison-grade-analysis', $data);
    }

    /**
     * Generate grade stream PSLE analysis
     */
    public function generateGradeStreamPSLEAnalysis($classId, $sequence, $type){
        $klass = Klass::findOrFail($classId);
        $gradeD = Grade::findOrFail($klass->grade_id);

        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $term = Term::findOrFail($termId);
        $test = Test::where('grade_id', $gradeD->id)->where('term_id', $term->id)->where('type', $type)->where('sequence', $sequence)->first();
        
        $school_setup = SchoolSetup::first();
        $allGradeSubjects = GradeSubject::where('grade_id', $gradeD->id)
            ->where('term_id', $term->id)
            ->with('subject')
            ->get();
        
        $selectedTermId = $term->id;
        $students = Student::whereHas('classes', function ($query) use ($gradeD) {
            $query->whereHas('grade', function ($query) use ($gradeD) {
                $query->where('id', $gradeD->id);
            });
        })->with([
            'psle',
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'classes' => function ($query) use ($selectedTermId) {
                $query->wherePivot('term_id', $selectedTermId);
            },
            'optionalSubjects',
        ])->get();

        $gradeCounts = [
            'M' => ['M' => 0, 'F' => 0],
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
            'X' => ['M' => 0, 'F' => 0],
        ];
        
        $psleGradeCounts = [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
        ];
        
        // Pre-load the overall_points_matrix for this grade to avoid per-student queries
        $overallPointsMatrix = DB::table('overall_points_matrix')
            ->where('academic_year', $gradeD->name)
            ->get();

        // Pre-load class subject mappings: classId => set of grade_subject_ids
        $classIds = $students->pluck('classes')->flatten()->pluck('id')->unique();
        $classSubjectMap = [];
        if ($classIds->isNotEmpty()) {
            $klassSubjects = DB::table('klass_subject')
                ->whereIn('klass_id', $classIds)
                ->whereNull('deleted_at')
                ->select('klass_id', 'grade_subject_id')
                ->get();
            foreach ($klassSubjects as $ks) {
                $classSubjectMap[$ks->klass_id][$ks->grade_subject_id] = true;
            }
        }

        $reportCardsData = [];

        foreach ($students as $student) {
            $isForeigner = $student->nationality !== 'Motswana';
            $psleGrade = optional($student->psle)->overall_grade;

            if ($psleGrade && isset($psleGradeCounts[$psleGrade])) {
                $gender = $student->gender === 'M' ? 'M' : 'F';
                $psleGradeCounts[$psleGrade][$gender]++;
            }

            $hasParticipated = false;
            $subjectScores = [];
            $subjectPoints = [];

            // Use eager-loaded classes instead of querying per student
            $currentClass = $student->classes->first();
            $className = $currentClass ? $currentClass->name : '';

            // Build sets for in-memory enrollment checks
            $classCurriculumIds = $currentClass
                ? ($classSubjectMap[$currentClass->id] ?? [])
                : [];
            $studentOptionalIds = $student->optionalSubjects
                ->pluck('grade_subject_id')
                ->flip()
                ->all();

            // Use eager-loaded tests collection filtered in-memory
            $studentTests = $student->tests;

            foreach ($allGradeSubjects as $gradeSubject) {
                $subjectName = optional($gradeSubject->subject)->name ?? 'Unknown Subject';

                // In-memory enrollment check (no queries)
                $isEnrolled = isset($classCurriculumIds[$gradeSubject->id])
                    || isset($studentOptionalIds[$gradeSubject->id]);

                // Filter from eager-loaded tests collection instead of querying
                $test = $studentTests
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->where('sequence', $sequence)
                    ->where('type', $type)
                    ->where('grade_id', $gradeD->id)
                    ->first();

                if ($test) {
                    $subjectData = [
                        'subject' => $subjectName,
                        'score' => $test->pivot->score,
                        'percentage' => $test->pivot->percentage,
                        'points' => $test->pivot->points,
                        'grade' => $test->pivot->grade,
                    ];
                } else {
                    // Fallback: check by subject name (handles cross-class subject transfers)
                    $subjectData = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                        $student,
                        $gradeSubject,
                        $term->id,
                        $type,
                        $sequence,
                        $gradeD->id
                    );
                }

                if (!is_null($subjectData['percentage'])) {
                    $hasParticipated = true;
                }

                $subGrade = $subjectData['grade'] ?? 'X';
                $subPercentage = $subjectData['percentage'] ?? null;
                $subPoints = $subjectData['points'] ?? null;

                $subjectScores[$subjectName] = [
                    'percentage' => $subPercentage,
                    'grade' => $subGrade,
                    'enrolled' => $isEnrolled
                ];

                $subjectPoints[$gradeSubject->id] = $subPoints;
            }

            // Use eager-loaded tests for points calculation (in-memory)
            $mandatoryPoints = 0;
            $optionalPoints = [];
            $corePointsList = [];

            foreach ($allGradeSubjects as $subject) {
                $testForPoints = $studentTests
                    ->where('grade_subject_id', $subject->id)
                    ->where('type', $type)
                    ->where('sequence', $sequence)
                    ->first();
                $points = $testForPoints ? $testForPoints->pivot->points : 0;

                if ($subject->subject->name == "Setswana") {
                    if (!$isForeigner) {
                        $mandatoryPoints += $points;
                        continue;
                    }

                    if (!$subject->type) {
                        $optionalPoints[] = $points;
                        continue;
                    }

                    $corePointsList[] = $points;
                    continue;
                }

                if ($subject->mandatory) {
                    $mandatoryPoints += $points;
                } elseif (!$subject->mandatory && !$subject->type) {
                    $optionalPoints[] = $points;
                } elseif (!$subject->mandatory && $subject->type) {
                    $corePointsList[] = $points;
                }
            }

            rsort($optionalPoints);
            rsort($corePointsList);

            if ($isForeigner) {
                $bestOptionalPoints = array_sum(array_slice($optionalPoints, 0, 2));
                $remainingOptionals = array_slice($optionalPoints, 2);
            } else {
                $bestOptionalPoints = count($optionalPoints) ? $optionalPoints[0] : 0;
                $remainingOptionals = array_slice($optionalPoints, 1);
            }

            $combinedRemaining = array_merge($remainingOptionals, $corePointsList);
            rsort($combinedRemaining);
            $bestCorePoints = array_sum(array_slice($combinedRemaining, 0, 2));

            $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;

            // Determine grade from cached matrix instead of querying per student
            $overallGrade = 'X';
            if ($hasParticipated && $currentClass) {
                $matchedGrade = $overallPointsMatrix
                    ->where('min', '<=', $totalPoints)
                    ->where('max', '>=', $totalPoints)
                    ->first();
                $overallGrade = $matchedGrade ? $matchedGrade->grade : 'X';
            }

            if ($overallGrade === 'Merit') {
                $overallGrade = 'M';
            }

            $reportCardsData[] = [
                'student' => $student,
                'scores' => $subjectScores,
                'totalPoints' => $hasParticipated ? $totalPoints : 'X',
                'grade' => $overallGrade,
                'class_name' => $className,
            ];

            $gender = $student->gender === 'M' ? 'M' : 'F';
            if (isset($gradeCounts[$overallGrade][$gender])) {
                $gradeCounts[$overallGrade][$gender]++;
            }
        }
        
        usort($reportCardsData, function ($a, $b) {
            $aVal = is_numeric($a['totalPoints']) ? $a['totalPoints'] : -1;
            $bVal = is_numeric($b['totalPoints']) ? $b['totalPoints'] : -1;
            return $bVal <=> $aVal;
        });
        
        foreach ($reportCardsData as $index => &$data) {
            $data['position'] = $index + 1;
        }
        unset($data);
        
        $maleCount = 0;
        $femaleCount = 0;
        
        foreach ($reportCardsData as $reportCard) {
            $gender = $reportCard['student']->gender === 'M' ? 'M' : 'F';
            
            if ($gender === 'M') {
                $maleCount++;
            } else {
                $femaleCount++;
            }
        }
        
        $totalStudents = count($reportCardsData);
        $safePercentage = function($count, $total) {
            return AssessmentHelper::formatPercentage($count, $total);
        };
        
        $sumM = $gradeCounts['M']['M'] + $gradeCounts['M']['F'];
        $sumA = $gradeCounts['A']['M'] + $gradeCounts['A']['F'];
        $sumB = $gradeCounts['B']['M'] + $gradeCounts['B']['F'];
        $sumC = $gradeCounts['C']['M'] + $gradeCounts['C']['F'];
        $sumD = $gradeCounts['D']['M'] + $gradeCounts['D']['F'];
        $sumE = $gradeCounts['E']['M'] + $gradeCounts['E']['F'];
        $sumU = $gradeCounts['U']['M'] + $gradeCounts['U']['F'];
        $sumX_M = $gradeCounts['X']['M'];
        $sumX_F = $gradeCounts['X']['F'];
        $sumX = $sumX_M + $sumX_F;
        
        $m_M = $gradeCounts['M']['M']; $m_F = $gradeCounts['M']['F'];
        $a_M = $gradeCounts['A']['M']; $a_F = $gradeCounts['A']['F'];
        $b_M = $gradeCounts['B']['M']; $b_F = $gradeCounts['B']['F'];
        $c_M = $gradeCounts['C']['M']; $c_F = $gradeCounts['C']['F'];
        $d_M = $gradeCounts['D']['M']; $d_F = $gradeCounts['D']['F'];
        $e_M = $gradeCounts['E']['M']; $e_F = $gradeCounts['E']['F'];
        $u_M = $gradeCounts['U']['M']; $u_F = $gradeCounts['U']['F'];
        $x_M = $gradeCounts['X']['M']; $x_F = $gradeCounts['X']['F'];
        
        $mab_M = $m_M + $a_M + $b_M;
        $mabc_M = $mab_M + $c_M;
        $mabcd_M = $mabc_M + $d_M;
        $deu_M = $d_M + $e_M + $u_M;
        $eu_M = $e_M + $u_M;

        $mab_F = $m_F + $a_F + $b_F;
        $mabc_F = $mab_F + $c_F;
        $mabcd_F = $mabc_F + $d_F;
        $deu_F = $d_F + $e_F + $u_F;
        $eu_F = $e_F + $u_F;

        $mab_T = $mab_M + $mab_F;
        $mabc_T = $mabc_M + $mabc_F;
        $mabcd_T = $mabcd_M + $mabcd_F;
        $deu_T = $deu_M + $deu_F;
        $eu_T = $eu_M + $eu_F;
        $x_T = $x_M + $x_F;
        
        $mab_M_Percentage = $safePercentage($mab_M, $maleCount);
        $mabc_M_Percentage = $safePercentage($mabc_M, $maleCount);
        $mabcd_M_Percentage = $safePercentage($mabcd_M, $maleCount);
        $deu_M_Percentage = $safePercentage($deu_M, $maleCount);
        $eu_M_Percentage = $safePercentage($eu_M, $maleCount);
        $x_M_Percentage = $safePercentage($x_M, $maleCount);

        $mab_F_Percentage = $safePercentage($mab_F, $femaleCount);
        $mabc_F_Percentage = $safePercentage($mabc_F, $femaleCount);
        $mabcd_F_Percentage = $safePercentage($mabcd_F, $femaleCount);
        $deu_F_Percentage = $safePercentage($deu_F, $femaleCount);
        $eu_F_Percentage = $safePercentage($eu_F, $femaleCount);
        $x_F_Percentage = $safePercentage($x_F, $femaleCount);

        $mab_T_percentage = $safePercentage($mab_T, $totalStudents);
        $mabc_T_percentage = $safePercentage($mabc_T, $totalStudents);
        $mabcd_T_percentage = $safePercentage($mabcd_T, $totalStudents);
        $deu_T_percentage = $safePercentage($deu_T, $totalStudents);
        $eu_T_percentage = $safePercentage($eu_T, $totalStudents);
        $x_T_Percentage = $safePercentage($x_T, $totalStudents);
        
        $psleTotalM = array_sum(array_column($psleGradeCounts, 'M'));
        $psleTotalF = array_sum(array_column($psleGradeCounts, 'F'));
        $totalPsleStudents = $psleTotalM + $psleTotalF;
        
        $psleA_M = $psleGradeCounts['A']['M']; $psleA_F = $psleGradeCounts['A']['F'];
        $psleB_M = $psleGradeCounts['B']['M']; $psleB_F = $psleGradeCounts['B']['F'];
        $psleC_M = $psleGradeCounts['C']['M']; $psleC_F = $psleGradeCounts['C']['F'];
        $psleD_M = $psleGradeCounts['D']['M']; $psleD_F = $psleGradeCounts['D']['F'];
        $psleE_M = $psleGradeCounts['E']['M']; $psleE_F = $psleGradeCounts['E']['F'];
        $psleU_M = $psleGradeCounts['U']['M']; $psleU_F = $psleGradeCounts['U']['F'];
        
        $psleAB_M = $psleA_M + $psleB_M; $psleAB_F = $psleA_F + $psleB_F;
        $psleAB_T = $psleAB_M + $psleAB_F;
        
        $psleABC_M = $psleAB_M + $psleC_M; $psleABC_F = $psleAB_F + $psleC_F;
        $psleABC_T = $psleABC_M + $psleABC_F;
        
        $psleABCD_M = $psleABC_M + $psleD_M; $psleABCD_F = $psleABC_F + $psleD_F;
        $psleABCD_T = $psleABCD_M + $psleABCD_F;
        
        $psleDEU_M = $psleD_M + $psleE_M + $psleU_M; $psleDEU_F = $psleD_F + $psleE_F + $psleU_F;
        $psleDEU_T = $psleDEU_M + $psleDEU_F;

        $psleEU_M = $psleE_M + $psleU_M; $psleEU_F = $psleE_F + $psleU_F;
        $psleEU_T = $psleEU_M + $psleEU_F;

        $psleAB_M_Percentage = $safePercentage($psleAB_M, $psleTotalM);
        $psleAB_F_Percentage = $safePercentage($psleAB_F, $psleTotalF);
        $psleAB_T_percentage = $safePercentage($psleAB_T, $totalPsleStudents);

        $psleABC_M_Percentage = $safePercentage($psleABC_M, $psleTotalM);
        $psleABC_F_Percentage = $safePercentage($psleABC_F, $psleTotalF);
        $psleABC_T_percentage = $safePercentage($psleABC_T, $totalPsleStudents);

        $psleABCD_M_Percentage = $safePercentage($psleABCD_M, $psleTotalM);
        $psleABCD_F_Percentage = $safePercentage($psleABCD_F, $psleTotalF);
        $psleABCD_T_percentage = $safePercentage($psleABCD_T, $totalPsleStudents);

        $psleDEU_M_Percentage = $safePercentage($psleDEU_M, $psleTotalM);
        $psleDEU_F_Percentage = $safePercentage($psleDEU_F, $psleTotalF);
        $psleDEU_T_percentage = $safePercentage($psleDEU_T, $totalPsleStudents);

        $psleEU_M_Percentage = $safePercentage($psleEU_M, $psleTotalM);
        $psleEU_F_Percentage = $safePercentage($psleEU_F, $psleTotalF);
        $psleEU_T_percentage = $safePercentage($psleEU_T, $totalPsleStudents);
        
        $data = [
            'reportCards' => $reportCardsData,
            'school_data' => $school_setup,
            'grade' => $gradeD,
            'term' => $term,
            'test' => $test,
            'type' => $type,
            'totalStudents' => $totalStudents,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
            
            'm_M' => $m_M, 'm_F' => $m_F, 'sumM' => $sumM,
            'a_M' => $a_M, 'a_F' => $a_F, 'sumA' => $sumA,
            'b_M' => $b_M, 'b_F' => $b_F, 'sumB' => $sumB,
            'c_M' => $c_M, 'c_F' => $c_F, 'sumC' => $sumC,
            'd_M' => $d_M, 'd_F' => $d_F, 'sumD' => $sumD,
            'e_M' => $e_M, 'e_F' => $e_F, 'sumE' => $sumE,
            'u_M' => $u_M, 'u_F' => $u_F, 'sumU' => $sumU,
            'x_M' => $x_M, 'x_F' => $x_F, 'sumX' => $sumX,
            
            'mab_M' => $mab_M, 'mab_F' => $mab_F, 'mab_T' => $mab_T,
            'mabc_M' => $mabc_M, 'mabc_F' => $mabc_F, 'mabc_T' => $mabc_T,
            'mabcd_M' => $mabcd_M, 'mabcd_F' => $mabcd_F, 'mabcd_T' => $mabcd_T,
            'deu_M' => $deu_M, 'deu_F' => $deu_F, 'deu_T' => $deu_T,
            'eu_M' => $eu_M, 'eu_F' => $eu_F, 'eu_T' => $eu_T,

            'mab_M_Percentage' => $mab_M_Percentage,
            'mab_F_Percentage' => $mab_F_Percentage,
            'mab_T_percentage' => $mab_T_percentage,
            
            'mabc_M_Percentage' => $mabc_M_Percentage,
            'mabc_F_Percentage' => $mabc_F_Percentage,
            'mabc_T_percentage' => $mabc_T_percentage,
            
            'mabcd_M_Percentage' => $mabcd_M_Percentage,
            'mabcd_F_Percentage' => $mabcd_F_Percentage,
            'mabcd_T_percentage' => $mabcd_T_percentage,
            
            'deu_M_Percentage' => $deu_M_Percentage,
            'deu_F_Percentage' => $deu_F_Percentage,
            'deu_T_percentage' => $deu_T_percentage,

            'eu_M_Percentage' => $eu_M_Percentage,
            'eu_F_Percentage' => $eu_F_Percentage,
            'eu_T_percentage' => $eu_T_percentage,

            'x_M_Percentage' => $x_M_Percentage,
            'x_F_Percentage' => $x_F_Percentage,
            'x_T_Percentage' => $x_T_Percentage,
            
            'psleTotalM' => $psleTotalM,
            'psleTotalF' => $psleTotalF,
            'totalPsleStudents' => $totalPsleStudents,
            
            'psleA_M' => $psleA_M, 'psleA_F' => $psleA_F,
            'psleB_M' => $psleB_M, 'psleB_F' => $psleB_F,
            'psleC_M' => $psleC_M, 'psleC_F' => $psleC_F,
            'psleD_M' => $psleD_M, 'psleD_F' => $psleD_F,
            'psleE_M' => $psleE_M, 'psleE_F' => $psleE_F,
            'psleU_M' => $psleU_M, 'psleU_F' => $psleU_F,
            
            'psleAB_M' => $psleAB_M, 'psleAB_F' => $psleAB_F, 'psleAB_T' => $psleAB_T,
            'psleABC_M' => $psleABC_M, 'psleABC_F' => $psleABC_F, 'psleABC_T' => $psleABC_T,
            'psleABCD_M' => $psleABCD_M, 'psleABCD_F' => $psleABCD_F, 'psleABCD_T' => $psleABCD_T,
            'psleDEU_M' => $psleDEU_M, 'psleDEU_F' => $psleDEU_F, 'psleDEU_T' => $psleDEU_T,
            'psleEU_M' => $psleEU_M, 'psleEU_F' => $psleEU_F, 'psleEU_T' => $psleEU_T,

            'psleAB_M_Percentage' => $psleAB_M_Percentage,
            'psleAB_F_Percentage' => $psleAB_F_Percentage,
            'psleAB_T_Percentage' => $psleAB_T_percentage,
            
            'psleABC_M_Percentage' => $psleABC_M_Percentage,
            'psleABC_F_Percentage' => $psleABC_F_Percentage,
            'psleABC_T_Percentage' => $psleABC_T_percentage,
            
            'psleABCD_M_Percentage' => $psleABCD_M_Percentage,
            'psleABCD_F_Percentage' => $psleABCD_F_Percentage,
            'psleABCD_T_Percentage' => $psleABCD_T_percentage,
            
            'psleDEU_M_Percentage' => $psleDEU_M_Percentage,
            'psleDEU_F_Percentage' => $psleDEU_F_Percentage,
            'psleDEU_T_Percentage' => $psleDEU_T_percentage,

            'psleEU_M_Percentage' => $psleEU_M_Percentage,
            'psleEU_F_Percentage' => $psleEU_F_Percentage,
            'psleEU_T_Percentage' => $psleEU_T_percentage,
        ];

        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\GradePerformanceStreamExport($data),
                "{$gradeD->name}_{$type}_Analysis_" . date('Y-m-d') . ".xlsx"
            );
        }
        
        return view('assessment.junior.stream-psle-analysis', $data);
    }

    /**
     * Generate special needs analysis report
     * Similar to grade stream PSLE analysis but only for students with student_type_id
     */
    public function generateSpecialNeedsAnalysis($classId, $sequence, $type) {
        $klass = Klass::findOrFail($classId);
        $gradeD = Grade::findOrFail($klass->grade_id);

        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $term = Term::findOrFail($termId);
        $test = Test::where('grade_id', $gradeD->id)->where('term_id', $term->id)->where('type', $type)->where('sequence', $sequence)->first();

        $school_setup = SchoolSetup::first();
        $allGradeSubjects = GradeSubject::where('grade_id', $gradeD->id)
            ->where('term_id', $term->id)
            ->with('subject')
            ->get();

        // Only get students with special needs (those with a student_type_id)
        $students = Student::whereNotNull('student_type_id')
            ->with('type')
            ->whereHas('classes', function ($query) use ($gradeD) {
                $query->whereHas('grade', function ($query) use ($gradeD) {
                    $query->where('id', $gradeD->id);
                });
            })->get();

        $gradeCounts = [
            'M' => ['M' => 0, 'F' => 0],
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
            'X' => ['M' => 0, 'F' => 0],
        ];

        $psleGradeCounts = [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
        ];

        // Group counts by student type
        $studentTypes = \App\Models\StudentType::all();
        $typeGradeCounts = [];
        foreach ($studentTypes as $studentType) {
            $typeGradeCounts[$studentType->id] = [
                'name' => $studentType->type,
                'color' => $studentType->color,
                'grades' => [
                    'M' => ['M' => 0, 'F' => 0],
                    'A' => ['M' => 0, 'F' => 0],
                    'B' => ['M' => 0, 'F' => 0],
                    'C' => ['M' => 0, 'F' => 0],
                    'D' => ['M' => 0, 'F' => 0],
                    'E' => ['M' => 0, 'F' => 0],
                    'U' => ['M' => 0, 'F' => 0],
                    'X' => ['M' => 0, 'F' => 0],
                ],
                'male_count' => 0,
                'female_count' => 0,
                'total' => 0,
            ];
        }

        $reportCardsData = [];

        foreach ($students as $student) {
            $isForeigner = $student->nationality !== 'Motswana';
            $psleGrade = optional($student->psle)->overall_grade;

            if ($psleGrade && isset($psleGradeCounts[$psleGrade])) {
                $gender = $student->gender === 'M' ? 'M' : 'F';
                $psleGradeCounts[$psleGrade][$gender]++;
            }

            $hasParticipated = false;
            $subjectScores = [];
            $subjectPoints = [];

            foreach ($allGradeSubjects as $gradeSubject) {
                $subjectName = optional($gradeSubject->subject)->name ?? 'Unknown Subject';

                $isEnrolled = false;
                $currentClass = $student->currentClass();

                if ($currentClass) {
                    $isInClassCurriculum = $currentClass->subjectClasses()
                        ->whereHas('subject', function($q) use ($gradeSubject) {
                            $q->where('grade_subject_id', $gradeSubject->id);
                        })->exists();

                    $isInOptionalSubjects = $student->optionalSubjects()
                        ->where('grade_subject_id', $gradeSubject->id)
                        ->exists();

                    $isEnrolled = $isInClassCurriculum || $isInOptionalSubjects;
                }

                $subjectData = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                    $student,
                    $gradeSubject,
                    $term->id,
                    $type,
                    $sequence,
                    $gradeD->id
                );

                if (!is_null($subjectData['percentage'])) {
                    $hasParticipated = true;
                }

                $subGrade = $subjectData['grade'] ?? 'X';
                $subPercentage = $subjectData['percentage'] ?? null;
                $subPoints = $subjectData['points'] ?? null;

                $subjectScores[$subjectName] = [
                    'percentage' => $subPercentage,
                    'grade' => $subGrade,
                    'enrolled' => $isEnrolled
                ];

                $subjectPoints[$gradeSubject->id] = $subPoints;
            }

            list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = AssessmentHelper::calculatePointsGeneral(
                $student,
                $isForeigner,
                $allGradeSubjects,
                $term->id,
                $type,
                $sequence
            );

            $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;
            $currentClass = $student->currentClass();
            $className = $currentClass ? $currentClass->name : '';

            $overallGrade = $hasParticipated
                ? AssessmentHelper::determineGrade($totalPoints, $currentClass)
                : 'X';

            if ($overallGrade === 'Merit') {
                $overallGrade = 'M';
            }

            $reportCardsData[] = [
                'student' => $student,
                'scores' => $subjectScores,
                'totalPoints' => $hasParticipated ? $totalPoints : 'X',
                'grade' => $overallGrade,
                'class_name' => $className,
                'student_type' => $student->type ? $student->type->type : 'Unknown',
                'student_type_color' => $student->type ? $student->type->color : '#6c757d',
            ];

            $gender = $student->gender === 'M' ? 'M' : 'F';
            if (isset($gradeCounts[$overallGrade][$gender])) {
                $gradeCounts[$overallGrade][$gender]++;
            }

            // Count by student type
            if ($student->student_type_id && isset($typeGradeCounts[$student->student_type_id])) {
                $typeGradeCounts[$student->student_type_id]['grades'][$overallGrade][$gender]++;
                if ($gender === 'M') {
                    $typeGradeCounts[$student->student_type_id]['male_count']++;
                } else {
                    $typeGradeCounts[$student->student_type_id]['female_count']++;
                }
                $typeGradeCounts[$student->student_type_id]['total']++;
            }
        }

        usort($reportCardsData, function ($a, $b) {
            $aVal = is_numeric($a['totalPoints']) ? $a['totalPoints'] : -1;
            $bVal = is_numeric($b['totalPoints']) ? $b['totalPoints'] : -1;
            return $bVal <=> $aVal;
        });

        foreach ($reportCardsData as $index => &$data) {
            $data['position'] = $index + 1;
        }
        unset($data);

        $maleCount = 0;
        $femaleCount = 0;

        foreach ($reportCardsData as $reportCard) {
            $gender = $reportCard['student']->gender === 'M' ? 'M' : 'F';

            if ($gender === 'M') {
                $maleCount++;
            } else {
                $femaleCount++;
            }
        }

        $totalStudents = count($reportCardsData);
        $safePercentage = function($count, $total) {
            return AssessmentHelper::formatPercentage($count, $total);
        };

        $sumM = $gradeCounts['M']['M'] + $gradeCounts['M']['F'];
        $sumA = $gradeCounts['A']['M'] + $gradeCounts['A']['F'];
        $sumB = $gradeCounts['B']['M'] + $gradeCounts['B']['F'];
        $sumC = $gradeCounts['C']['M'] + $gradeCounts['C']['F'];
        $sumD = $gradeCounts['D']['M'] + $gradeCounts['D']['F'];
        $sumE = $gradeCounts['E']['M'] + $gradeCounts['E']['F'];
        $sumU = $gradeCounts['U']['M'] + $gradeCounts['U']['F'];
        $sumX_M = $gradeCounts['X']['M'];
        $sumX_F = $gradeCounts['X']['F'];
        $sumX = $sumX_M + $sumX_F;

        $m_M = $gradeCounts['M']['M']; $m_F = $gradeCounts['M']['F'];
        $a_M = $gradeCounts['A']['M']; $a_F = $gradeCounts['A']['F'];
        $b_M = $gradeCounts['B']['M']; $b_F = $gradeCounts['B']['F'];
        $c_M = $gradeCounts['C']['M']; $c_F = $gradeCounts['C']['F'];
        $d_M = $gradeCounts['D']['M']; $d_F = $gradeCounts['D']['F'];
        $e_M = $gradeCounts['E']['M']; $e_F = $gradeCounts['E']['F'];
        $u_M = $gradeCounts['U']['M']; $u_F = $gradeCounts['U']['F'];
        $x_M = $gradeCounts['X']['M']; $x_F = $gradeCounts['X']['F'];

        $mab_M = $m_M + $a_M + $b_M;
        $mabc_M = $mab_M + $c_M;
        $mabcd_M = $mabc_M + $d_M;
        $deu_M = $d_M + $e_M + $u_M;

        $mab_F = $m_F + $a_F + $b_F;
        $mabc_F = $mab_F + $c_F;
        $mabcd_F = $mabc_F + $d_F;
        $deu_F = $d_F + $e_F + $u_F;

        $mab_T = $mab_M + $mab_F;
        $mabc_T = $mabc_M + $mabc_F;
        $mabcd_T = $mabcd_M + $mabcd_F;
        $deu_T = $deu_M + $deu_F;
        $x_T = $x_M + $x_F;

        $mab_M_Percentage = $safePercentage($mab_M, $maleCount);
        $mabc_M_Percentage = $safePercentage($mabc_M, $maleCount);
        $mabcd_M_Percentage = $safePercentage($mabcd_M, $maleCount);
        $deu_M_Percentage = $safePercentage($deu_M, $maleCount);
        $x_M_Percentage = $safePercentage($x_M, $maleCount);

        $mab_F_Percentage = $safePercentage($mab_F, $femaleCount);
        $mabc_F_Percentage = $safePercentage($mabc_F, $femaleCount);
        $mabcd_F_Percentage = $safePercentage($mabcd_F, $femaleCount);
        $deu_F_Percentage = $safePercentage($deu_F, $femaleCount);
        $x_F_Percentage = $safePercentage($x_F, $femaleCount);

        $mab_T_percentage = $safePercentage($mab_T, $totalStudents);
        $mabc_T_percentage = $safePercentage($mabc_T, $totalStudents);
        $mabcd_T_percentage = $safePercentage($mabcd_T, $totalStudents);
        $deu_T_percentage = $safePercentage($deu_T, $totalStudents);
        $x_T_Percentage = $safePercentage($x_T, $totalStudents);

        // PSLE calculations
        $psleTotalM = array_sum(array_column($psleGradeCounts, 'M'));
        $psleTotalF = array_sum(array_column($psleGradeCounts, 'F'));
        $totalPsleStudents = $psleTotalM + $psleTotalF;

        $psleA_M = $psleGradeCounts['A']['M']; $psleA_F = $psleGradeCounts['A']['F'];
        $psleB_M = $psleGradeCounts['B']['M']; $psleB_F = $psleGradeCounts['B']['F'];
        $psleC_M = $psleGradeCounts['C']['M']; $psleC_F = $psleGradeCounts['C']['F'];
        $psleD_M = $psleGradeCounts['D']['M']; $psleD_F = $psleGradeCounts['D']['F'];
        $psleE_M = $psleGradeCounts['E']['M']; $psleE_F = $psleGradeCounts['E']['F'];
        $psleU_M = $psleGradeCounts['U']['M']; $psleU_F = $psleGradeCounts['U']['F'];

        $psleAB_M = $psleA_M + $psleB_M; $psleAB_F = $psleA_F + $psleB_F;
        $psleAB_T = $psleAB_M + $psleAB_F;

        $psleABC_M = $psleAB_M + $psleC_M; $psleABC_F = $psleAB_F + $psleC_F;
        $psleABC_T = $psleABC_M + $psleABC_F;

        $psleABCD_M = $psleABC_M + $psleD_M; $psleABCD_F = $psleABC_F + $psleD_F;
        $psleABCD_T = $psleABCD_M + $psleABCD_F;

        $psleDEU_M = $psleD_M + $psleE_M + $psleU_M; $psleDEU_F = $psleD_F + $psleE_F + $psleU_F;
        $psleDEU_T = $psleDEU_M + $psleDEU_F;

        $psleAB_M_Percentage = $safePercentage($psleAB_M, $psleTotalM);
        $psleAB_F_Percentage = $safePercentage($psleAB_F, $psleTotalF);
        $psleAB_T_percentage = $safePercentage($psleAB_T, $totalPsleStudents);

        $psleABC_M_Percentage = $safePercentage($psleABC_M, $psleTotalM);
        $psleABC_F_Percentage = $safePercentage($psleABC_F, $psleTotalF);
        $psleABC_T_percentage = $safePercentage($psleABC_T, $totalPsleStudents);

        $psleABCD_M_Percentage = $safePercentage($psleABCD_M, $psleTotalM);
        $psleABCD_F_Percentage = $safePercentage($psleABCD_F, $psleTotalF);
        $psleABCD_T_percentage = $safePercentage($psleABCD_T, $totalPsleStudents);

        $psleDEU_M_Percentage = $safePercentage($psleDEU_M, $psleTotalM);
        $psleDEU_F_Percentage = $safePercentage($psleDEU_F, $psleTotalF);
        $psleDEU_T_percentage = $safePercentage($psleDEU_T, $totalPsleStudents);

        // Filter out student types with no students
        $typeGradeCounts = array_filter($typeGradeCounts, function($typeData) {
            return $typeData['total'] > 0;
        });

        $data = [
            'reportCards' => $reportCardsData,
            'school_data' => $school_setup,
            'grade' => $gradeD,
            'term' => $term,
            'test' => $test,
            'type' => $type,
            'totalStudents' => $totalStudents,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
            'typeGradeCounts' => $typeGradeCounts,

            'm_M' => $m_M, 'm_F' => $m_F, 'sumM' => $sumM,
            'a_M' => $a_M, 'a_F' => $a_F, 'sumA' => $sumA,
            'b_M' => $b_M, 'b_F' => $b_F, 'sumB' => $sumB,
            'c_M' => $c_M, 'c_F' => $c_F, 'sumC' => $sumC,
            'd_M' => $d_M, 'd_F' => $d_F, 'sumD' => $sumD,
            'e_M' => $e_M, 'e_F' => $e_F, 'sumE' => $sumE,
            'u_M' => $u_M, 'u_F' => $u_F, 'sumU' => $sumU,
            'x_M' => $x_M, 'x_F' => $x_F, 'sumX' => $sumX,

            'mab_M' => $mab_M, 'mab_F' => $mab_F, 'mab_T' => $mab_T,
            'mabc_M' => $mabc_M, 'mabc_F' => $mabc_F, 'mabc_T' => $mabc_T,
            'mabcd_M' => $mabcd_M, 'mabcd_F' => $mabcd_F, 'mabcd_T' => $mabcd_T,
            'deu_M' => $deu_M, 'deu_F' => $deu_F, 'deu_T' => $deu_T,

            'mab_M_Percentage' => $mab_M_Percentage,
            'mab_F_Percentage' => $mab_F_Percentage,
            'mab_T_percentage' => $mab_T_percentage,

            'mabc_M_Percentage' => $mabc_M_Percentage,
            'mabc_F_Percentage' => $mabc_F_Percentage,
            'mabc_T_percentage' => $mabc_T_percentage,

            'mabcd_M_Percentage' => $mabcd_M_Percentage,
            'mabcd_F_Percentage' => $mabcd_F_Percentage,
            'mabcd_T_percentage' => $mabcd_T_percentage,

            'deu_M_Percentage' => $deu_M_Percentage,
            'deu_F_Percentage' => $deu_F_Percentage,
            'deu_T_percentage' => $deu_T_percentage,

            'x_M_Percentage' => $x_M_Percentage,
            'x_F_Percentage' => $x_F_Percentage,
            'x_T_Percentage' => $x_T_Percentage,

            'psleTotalM' => $psleTotalM,
            'psleTotalF' => $psleTotalF,
            'totalPsleStudents' => $totalPsleStudents,

            'psleA_M' => $psleA_M, 'psleA_F' => $psleA_F,
            'psleB_M' => $psleB_M, 'psleB_F' => $psleB_F,
            'psleC_M' => $psleC_M, 'psleC_F' => $psleC_F,
            'psleD_M' => $psleD_M, 'psleD_F' => $psleD_F,
            'psleE_M' => $psleE_M, 'psleE_F' => $psleE_F,
            'psleU_M' => $psleU_M, 'psleU_F' => $psleU_F,

            'psleAB_M' => $psleAB_M, 'psleAB_F' => $psleAB_F, 'psleAB_T' => $psleAB_T,
            'psleABC_M' => $psleABC_M, 'psleABC_F' => $psleABC_F, 'psleABC_T' => $psleABC_T,
            'psleABCD_M' => $psleABCD_M, 'psleABCD_F' => $psleABCD_F, 'psleABCD_T' => $psleABCD_T,
            'psleDEU_M' => $psleDEU_M, 'psleDEU_F' => $psleDEU_F, 'psleDEU_T' => $psleDEU_T,

            'psleAB_M_Percentage' => $psleAB_M_Percentage,
            'psleAB_F_Percentage' => $psleAB_F_Percentage,
            'psleAB_T_Percentage' => $psleAB_T_percentage,

            'psleABC_M_Percentage' => $psleABC_M_Percentage,
            'psleABC_F_Percentage' => $psleABC_F_Percentage,
            'psleABC_T_Percentage' => $psleABC_T_percentage,

            'psleABCD_M_Percentage' => $psleABCD_M_Percentage,
            'psleABCD_F_Percentage' => $psleABCD_F_Percentage,
            'psleABCD_T_Percentage' => $psleABCD_T_percentage,

            'psleDEU_M_Percentage' => $psleDEU_M_Percentage,
            'psleDEU_F_Percentage' => $psleDEU_F_Percentage,
            'psleDEU_T_Percentage' => $psleDEU_T_percentage,
        ];

        return view('assessment.junior.special-needs-analysis', $data);
    }

    /**
     * Generate house analysis report
     */
    public function generateHouseAnalysisReport($classId, $sequence, $type){
        $klass          = Klass::findOrFail($classId);
        $currentTerm    = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_data    = SchoolSetup::first();
        $year           = $currentTerm->year;
        $grade          = Grade::findOrFail($klass->grade_id);

        $houses = House::all();
        $classes = Klass::with('teacher')
            ->where('term_id', $selectedTermId)
            ->where('year', $year)
            ->where('grade_id', $klass->grade_id)
            ->orderBy('name')
            ->get();

        $houseAnalysis = [];
        $totalGrades   = array_fill_keys(['A','B','C','D','E','U'], ['M'=>0,'F'=>0]);
        foreach ($houses as $h) {
            $houseAnalysis[$h->id] = [
                'name'    => $h->name,
                'classes' => [],
                'totals'  => array_merge(
                    array_fill_keys(['A','B','C','D','E','U'], ['M'=>0,'F'=>0]),
                    ['total'=>0,'male_count'=>0,'female_count'=>0]
                ),
            ];
        }

        $tests = Test::where('term_id', $selectedTermId)
            ->where('grade_id', $klass->grade_id)
            ->where('sequence', $sequence)
            ->where('type', $type)
            ->pluck('id');

        foreach ($classes as $class) {
            $students = $class->currentStudents($selectedTermId, $year)->get();
            $byHouse  = [];
            foreach ($students as $stu) {
                if ($house = $stu->houses()->wherePivot('term_id', $selectedTermId)->first()) {
                    $byHouse[$house->id][] = $stu;
                }
            }

            foreach ($byHouse as $houseId => $stuList) {
                $gradesByGender   = array_fill_keys(['A','B','C','D','E','U'], ['M'=>0,'F'=>0]);
                $maleRecordCount   = 0;
                $femaleRecordCount = 0;

                $ids = collect($stuList)->pluck('id');
                $records = StudentTest::with('student')
                    ->whereIn('student_id', $ids)
                    ->whereIn('test_id', $tests)
                    ->get();

                foreach ($records as $r) {
                    $g = $r->grade ?? 'U';
                    $gen = $r->student->gender ?? 'M';

                    if (isset($gradesByGender[$g][$gen])) {
                        $gradesByGender[$g][$gen]++;
                        $houseAnalysis[$houseId]['totals'][$g][$gen]++;
                        $totalGrades[$g][$gen]++;
                    }

                    if ($gen === 'M') {
                        $maleRecordCount++;
                    } else {
                        $femaleRecordCount++;
                    }
                }

                $totalRecordCount = $maleRecordCount + $femaleRecordCount;

                if ($totalRecordCount) {
                    $abcM   = $gradesByGender['A']['M'] + $gradesByGender['B']['M'] + $gradesByGender['C']['M'];
                    $abcF   = $gradesByGender['A']['F'] + $gradesByGender['B']['F'] + $gradesByGender['C']['F'];
                    $abcT   = $abcM + $abcF;
                    $abcdM  = $abcM + $gradesByGender['D']['M'];
                    $abcdF  = $abcF + $gradesByGender['D']['F'];
                    $abcdT  = $abcdM + $abcdF;

                    $maleAbcPct   = round($abcM   / $maleRecordCount   * 100, 1);
                    $femaleAbcPct = round($abcF   / $femaleRecordCount * 100, 1);
                    $abcPct       = round($abcT   / $totalRecordCount  * 100, 1);

                    $maleAbcdPct   = round($abcdM  / $maleRecordCount   * 100, 1);
                    $femaleAbcdPct = round($abcdF  / $femaleRecordCount * 100, 1);
                    $abcdPct       = round($abcdT  / $totalRecordCount  * 100, 1);

                    $houseAnalysis[$houseId]['classes'][$class->id] = [
                        'class_name'            => $class->name,
                        'grades'                => $gradesByGender,
                        'male_count'            => $maleRecordCount,
                        'female_count'          => $femaleRecordCount,
                        'total'                 => $totalRecordCount,
                        'abc_percentage'        => $abcPct,
                        'abcd_percentage'       => $abcdPct,
                        'male_abc_percentage'   => $maleAbcPct,
                        'female_abc_percentage' => $femaleAbcPct,
                        'male_abcd_percentage'  => $maleAbcdPct,
                        'female_abcd_percentage'=> $femaleAbcdPct,
                    ];

                    $houseAnalysis[$houseId]['totals']['male_count']   += $maleRecordCount;
                    $houseAnalysis[$houseId]['totals']['female_count'] += $femaleRecordCount;
                    $houseAnalysis[$houseId]['totals']['total']        += $totalRecordCount;
                }
            }
        }

        foreach ($houseAnalysis as $hId => &$h) {
            $m = $h['totals']['male_count'];
            $f = $h['totals']['female_count'];
            $t = $h['totals']['total'];

            if ($t) {
                $abcM = $h['totals']['A']['M'] + $h['totals']['B']['M'] + $h['totals']['C']['M'];
                $abcF = $h['totals']['A']['F'] + $h['totals']['B']['F'] + $h['totals']['C']['F'];
                $abcT = $abcM + $abcF;
                $abcdM = $abcM + $h['totals']['D']['M'];
                $abcdF = $abcF + $h['totals']['D']['F'];
                $abcdT = $abcdM + $abcdF;

                $h['totals']['abc_percentage']        = round($abcT   / $t * 100, 1);
                $h['totals']['abcd_percentage']       = round($abcdT  / $t * 100, 1);
                $h['totals']['male_abc_percentage']   = $m ? round($abcM / $m * 100, 1) : 0;
                $h['totals']['female_abc_percentage'] = $f ? round($abcF / $f * 100, 1) : 0;
                $h['totals']['male_abcd_percentage']  = $m ? round($abcdM/ $m * 100, 1) : 0;
                $h['totals']['female_abcd_percentage']= $f ? round($abcdF/ $f * 100, 1) : 0;
            } else {
                foreach (['abc_percentage','abcd_percentage','male_abc_percentage','female_abc_percentage','male_abcd_percentage','female_abcd_percentage'] as $key) {
                    $h['totals'][$key] = 0;
                }
            }
        }
        unset($h);

        $houseAnalysis = array_filter($houseAnalysis, fn($h) => ! empty($h['classes']));

        $totalMaleCount   = array_sum(array_column($houseAnalysis, 'totals.male_count'));
        $totalFemaleCount = array_sum(array_column($houseAnalysis, 'totals.female_count'));
        $grandTotalCount  = array_sum(array_column($houseAnalysis, 'totals.total'));

        $grandAbcM  = $totalGrades['A']['M'] + $totalGrades['B']['M'] + $totalGrades['C']['M'];
        $grandAbcF  = $totalGrades['A']['F'] + $totalGrades['B']['F'] + $totalGrades['C']['F'];
        $grandAbc   = $grandAbcM + $grandAbcF;
        $grandAbcdM = $grandAbcM + $totalGrades['D']['M'];
        $grandAbcdF = $grandAbcF + $totalGrades['D']['F'];
        $grandAbcd  = $grandAbcdM + $grandAbcdF;

        $overallABCPercentage  = $grandTotalCount  ? round($grandAbc  / $grandTotalCount  * 100, 1) : 0;
        $overallABCDPercentage = $grandTotalCount  ? round($grandAbcd / $grandTotalCount  * 100, 1) : 0;

        $overallTotals = [
            'A'     => $totalGrades['A']['M'] + $totalGrades['A']['F'],
            'B'     => $totalGrades['B']['M'] + $totalGrades['B']['F'],
            'C'     => $totalGrades['C']['M'] + $totalGrades['C']['F'],
            'D'     => $totalGrades['D']['M'] + $totalGrades['D']['F'],
            'E'     => $totalGrades['E']['M'] + $totalGrades['E']['F'],
            'U'     => $totalGrades['U']['M'] + $totalGrades['U']['F'],
            'T'     => $grandTotalCount,
            'ABC'   => $overallABCPercentage,
            'ABCD'  => $overallABCDPercentage,
            'ABC_M' => $totalGrades['A']['M'] + $totalGrades['B']['M'] + $totalGrades['C']['M'],
            'ABC_F' => $totalGrades['A']['F'] + $totalGrades['B']['F'] + $totalGrades['C']['F'],
            'ABCD_M'=> $totalGrades['A']['M'] + $totalGrades['B']['M'] + $totalGrades['C']['M'] + $totalGrades['D']['M'],
            'ABCD_F'=> $totalGrades['A']['F'] + $totalGrades['B']['F'] + $totalGrades['C']['F'] + $totalGrades['D']['F'],
        ];

        $data = [
            'houseAnalysis'        => $houseAnalysis,
            'school_data'          => $school_data,
            'currentTerm'          => $currentTerm,
            'grade'                => $grade,
            'sequence'             => $sequence,
            'type'                 => $type,
            'totalGrades'          => $totalGrades,
            'grandTotal'           => $grandTotalCount,
            'totalMaleCount'       => $totalMaleCount,
            'totalFemaleCount'     => $totalFemaleCount,
            'overallABCPercentage' => $overallABCPercentage,
            'overallABCDPercentage'=> $overallABCDPercentage,
            'overallTotals'        => $overallTotals,
        ];

        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\HouseAnalysisExport($data),
                "House_Analysis_Report_{$type}_" . date('Y-m-d') . ".xlsx"
            );
        }

        return view('assessment.junior.house-analysis-by-class', $data);
    }

    /**
     * Generate exam house performance report
     */
    public function generateExamHousePerformanceReport(){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $test = Test::where('term_id', $selectedTermId)->where('type', 'Exam')->first();
        $term = Term::findOrFail($selectedTermId);

        // Get all house students with their class/grade info for this term
        $houseStudentData = DB::table('houses')
            ->join('student_house', 'houses.id', '=', 'student_house.house_id')
            ->join('students', 'student_house.student_id', '=', 'students.id')
            ->leftJoin('klass_student', function($join) use ($selectedTermId) {
                $join->on('students.id', '=', 'klass_student.student_id')
                     ->where('klass_student.term_id', '=', $selectedTermId);
            })
            ->leftJoin('klasses', 'klass_student.klass_id', '=', 'klasses.id')
            ->leftJoin('grades', 'klasses.grade_id', '=', 'grades.id')
            ->where('student_house.term_id', $selectedTermId)
            ->whereNull('students.deleted_at')
            ->whereNotNull('klasses.id')
            ->whereNotNull('klasses.grade_id')
            ->select(
                'houses.name as house_name',
                'students.id as student_id',
                'students.gender',
                'students.nationality',
                'grades.name as grade_name'
            )->get();

        $studentIds = $houseStudentData->pluck('student_id')->unique()->toArray();

        // Load points matrix into memory
        $pointsMatrix = DB::table('overall_points_matrix')->get()->groupBy('academic_year');

        // Get ALL test results for all students in one query
        $allTestResults = DB::table('student_tests as st')
            ->join('tests as t', 'st.test_id', '=', 't.id')
            ->join('grade_subject as gs', 't.grade_subject_id', '=', 'gs.id')
            ->join('subjects as s', 'gs.subject_id', '=', 's.id')
            ->whereIn('st.student_id', $studentIds)
            ->where('t.term_id', $selectedTermId)
            ->where('t.type', 'Exam')
            ->whereNull('st.deleted_at')
            ->whereNull('t.deleted_at')
            ->select(
                'st.student_id',
                'st.points',
                's.name as subject_name',
                'gs.mandatory',
                'gs.type as subject_type'
            )->get()->groupBy('student_id');

        // Helper to determine overall grade from points
        $determineGrade = function($totalPoints, $gradeName) use ($pointsMatrix) {
            $matrix = $pointsMatrix->get($gradeName, collect());
            foreach ($matrix as $row) {
                if ($totalPoints >= $row->min && $totalPoints <= $row->max) {
                    return $row->grade;
                }
            }
            return 'U';
        };

        // Helper to calculate total points for a student
        $calculateTotalPoints = function($studentTests, $isForeigner) {
            $mandatoryPoints = 0;
            $optionalPoints = [];
            $corePoints = [];

            foreach ($studentTests as $test) {
                $points = $test->points ?? 0;
                $subjectName = $test->subject_name;

                if ($subjectName === "Setswana") {
                    if (!$isForeigner) {
                        $mandatoryPoints += $points;
                        continue;
                    }

                    if (!$test->subject_type) {
                        $optionalPoints[] = $points;
                        continue;
                    }

                    $corePoints[] = $points;
                    continue;
                }

                if ($test->mandatory) {
                    $mandatoryPoints += $points;
                } elseif (!$test->mandatory && !$test->subject_type) {
                    $optionalPoints[] = $points;
                } elseif (!$test->mandatory && $test->subject_type) {
                    $corePoints[] = $points;
                }
            }

            rsort($optionalPoints);
            rsort($corePoints);

            if ($isForeigner) {
                $bestOptionalPoints = array_sum(array_slice($optionalPoints, 0, 2));
                $remainingOptionals = array_slice($optionalPoints, 2);
            } else {
                $bestOptionalPoints = count($optionalPoints) ? $optionalPoints[0] : 0;
                $remainingOptionals = array_slice($optionalPoints, 1);
            }

            $combinedRemaining = array_merge($remainingOptionals, $corePoints);
            rsort($combinedRemaining);
            $bestFromCombined = array_sum(array_slice($combinedRemaining, 0, 2));

            return $mandatoryPoints + $bestOptionalPoints + $bestFromCombined;
        };

        // Initialize overall totals
        $overall = [
            'grades' => [
                'A'=>['M'=>0,'F'=>0], 'B'=>['M'=>0,'F'=>0], 'C'=>['M'=>0,'F'=>0],
                'D'=>['M'=>0,'F'=>0], 'E'=>['M'=>0,'F'=>0], 'U'=>['M'=>0,'F'=>0],
                'total'=>['M'=>0,'F'=>0],
            ],
            'AB%'=>0, 'ABC%'=>0, 'ABCD%'=>0, 'DEU%'=>0,
        ];

        $housePerformance = [];
        $groupedByHouse = $houseStudentData->groupBy('house_name');

        foreach ($groupedByHouse as $houseName => $houseStudents) {
            $gc = [
                'A'=>['M'=>0,'F'=>0], 'B'=>['M'=>0,'F'=>0], 'C'=>['M'=>0,'F'=>0],
                'D'=>['M'=>0,'F'=>0], 'E'=>['M'=>0,'F'=>0], 'U'=>['M'=>0,'F'=>0],
                'total'=>['M'=>0,'F'=>0],
            ];

            foreach ($houseStudents as $studentData) {
                $sex = $studentData->gender === 'M' ? 'M' : 'F';
                $isForeigner = $studentData->nationality !== 'Motswana';
                $studentTests = $allTestResults->get($studentData->student_id, collect());

                if ($studentTests->isEmpty()) {
                    continue;
                }

                // Calculate overall grade for this student
                $totalPoints = $calculateTotalPoints($studentTests, $isForeigner);
                $overallG = $determineGrade($totalPoints, $studentData->grade_name);

                // Map Merit to A for this report (or handle separately)
                if ($overallG === 'Merit') {
                    $overallG = 'A'; // Merit counts as A for AB%, ABC% calculations
                }

                if ($overallG && isset($gc[$overallG][$sex])) {
                    $gc[$overallG][$sex]++;
                    $gc['total'][$sex]++;
                }
            }

            $tot = $gc['total']['M'] + $gc['total']['F'];
            $ab = $gc['A']['M'] + $gc['A']['F'] + $gc['B']['M'] + $gc['B']['F'];
            $abc = $ab + $gc['C']['M'] + $gc['C']['F'];
            $abcd = $abc + $gc['D']['M'] + $gc['D']['F'];
            $deu = $gc['D']['M'] + $gc['D']['F'] + $gc['E']['M'] + $gc['E']['F'] + $gc['U']['M'] + $gc['U']['F'];

            $housePerformance[$houseName] = [
                'grades' => $gc,
                'AB%' => $tot ? round($ab / $tot * 100, 2) : 0,
                'ABC%' => $tot ? round($abc / $tot * 100, 2) : 0,
                'ABCD%' => $tot ? round($abcd / $tot * 100, 2) : 0,
                'DEU%' => $tot ? round($deu / $tot * 100, 2) : 0,
            ];

            // Update overall totals
            foreach (['A','B','C','D','E','U'] as $g) {
                $overall['grades'][$g]['M'] += $gc[$g]['M'];
                $overall['grades'][$g]['F'] += $gc[$g]['F'];
            }
            $overall['grades']['total']['M'] += $gc['total']['M'];
            $overall['grades']['total']['F'] += $gc['total']['F'];
        }

        $totFull = $overall['grades']['total']['M'] + $overall['grades']['total']['F'];
        $abFull = $overall['grades']['A']['M'] + $overall['grades']['A']['F']
                + $overall['grades']['B']['M'] + $overall['grades']['B']['F'];
        $abcFull = $abFull + $overall['grades']['C']['M'] + $overall['grades']['C']['F'];
        $abcdFull = $abcFull + $overall['grades']['D']['M'] + $overall['grades']['D']['F'];
        $deuFull = $overall['grades']['D']['M'] + $overall['grades']['D']['F']
                 + $overall['grades']['E']['M'] + $overall['grades']['E']['F']
                 + $overall['grades']['U']['M'] + $overall['grades']['U']['F'];

        $overall['AB%'] = $totFull ? round($abFull / $totFull * 100, 2) : 0;
        $overall['ABC%'] = $totFull ? round($abcFull / $totFull * 100, 2) : 0;
        $overall['ABCD%'] = $totFull ? round($abcdFull / $totFull * 100, 2) : 0;
        $overall['DEU%'] = $totFull ? round($deuFull / $totFull * 100, 2) : 0;

        $viewData = [
            'housePerformance' => $housePerformance,
            'overallTotals' => $overall,
            'school_data' => SchoolSetup::first(),
            'type' => 'Exam',
            'test' => $test,
            'term' => $term,
        ];

        if (request()->query('export') === 'excel') {
            return Excel::download(
                new JuniorSubjectsHouseStatisticsExport($viewData),
                'subjects-by-house-exam-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('houses.subjects-houses-statistics', $viewData);
    }

    /**
     * Generate overall CA house performance report
     */
    public function generateOverallCAHousePerformanceReport(int $sequence){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm    = TermHelper::getCurrentTerm();
        $school_data    = SchoolSetup::first();

        $test = Test::where('term_id', $selectedTermId)->where('type', 'CA')->where('sequence', $sequence)->first();
        $term = Term::findOrFail($selectedTermId);

        $houses = House::with(['students' => function ($q) use ($selectedTermId) {
            $q->where('student_house.term_id', $selectedTermId);
        }])->where('term_id', $selectedTermId) ->get();

        $gradeIds = [];
        foreach ($houses as $h) {
            foreach ($h->students as $stu) {
                if ($cls = $stu->currentClass()) $gradeIds[] = $cls->grade_id;
            }
        }
        $gradeIds = array_unique($gradeIds);

        $allGradeSubjects = GradeSubject::whereIn('grade_id', $gradeIds)
                            ->where('term_id', $selectedTermId)
                            ->with('subject')
                            ->get();
        $allSubjects = $allGradeSubjects->pluck('subject.name')
                                        ->unique()->sort()->values()->toArray();

        $overall = [
            'grades' => [
                'Merit'=>['M'=>0,'F'=>0],'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],
                'C'=>['M'=>0,'F'=>0],'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],
                'U'=>['M'=>0,'F'=>0],'total'=>['M'=>0,'F'=>0],
            ],
            'MAB%'=>['M'=>0,'F'=>0],'MABC%'=>['M'=>0,'F'=>0],
            'MABCD%'=>['M'=>0,'F'=>0],'DEU%'=>['M'=>0,'F'=>0],
            'totalMale'=>0,'totalFemale'=>0,
        ];

        $housePerformance = [];

        foreach ($houses as $house) {
            $houseName = $house->name;
            $students  = $house->students;

            $gradeCounts = [
                'Merit'=>['M'=>0,'F'=>0],'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],
                'C'=>['M'=>0,'F'=>0],'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],
                'U'=>['M'=>0,'F'=>0],'total'=>['M'=>0,'F'=>0],
            ];

            $subjectGradeCounts = [];
            foreach ($allSubjects as $sub) {
                $subjectGradeCounts[$sub] = [
                    'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],'C'=>['M'=>0,'F'=>0],
                    'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],'U'=>['M'=>0,'F'=>0],
                    'total'=>['M'=>0,'F'=>0],
                ];
            }
            $reportCardsData = [];

            foreach ($students as $student) {
                $cls = $student->currentClass(); if (!$cls) continue;

                $stuGSubs = $allGradeSubjects->where('grade_id',$cls->grade_id);
                foreach ($stuGSubs as $gSub) {
                    $sData = AssessmentHelper::calculateSubjectCAScoresAnalysis(
                                $student,$gSub,$selectedTermId,$sequence,$cls->grade_id);

                    $subName = $gSub->subject->name;
                    $g       = $sData['grade'];
                    $sex     = $student->gender==='M'?'M':'F';

                    $subjectGradeCounts[$subName]['total'][$sex]++;
                    if (isset($subjectGradeCounts[$subName][$g][$sex])) {
                        $subjectGradeCounts[$subName][$g][$sex]++;
                    }

                    $gradeCounts['total'][$sex]++;
                    if (isset($gradeCounts[$g][$sex])) {
                        $gradeCounts[$g][$sex]++;
                    }
                }
            }

            $totM = $gradeCounts['total']['M'];
            $totF = $gradeCounts['total']['F'];
            $totT = $totM + $totF;

            $sum = fn($g)=>$gradeCounts[$g]['M'] + $gradeCounts[$g]['F'];
            $mab   = $sum('Merit') + $sum('A') + $sum('B');
            $mabc  = $mab + $sum('C');
            $mabcd = $mabc + $sum('D');
            $deu   =        $sum('D') + $sum('E') + $sum('U');

            $mabM   = $gradeCounts['Merit']['M']+$gradeCounts['A']['M']+$gradeCounts['B']['M'];
            $mabF   = $gradeCounts['Merit']['F']+$gradeCounts['A']['F']+$gradeCounts['B']['F'];
            $mabcM  = $mabM  + $gradeCounts['C']['M'];
            $mabcF  = $mabF  + $gradeCounts['C']['F'];
            $mabcdM = $mabcM + $gradeCounts['D']['M'];
            $mabcdF = $mabcF + $gradeCounts['D']['F'];
            $deuM   = $gradeCounts['D']['M']+$gradeCounts['E']['M']+$gradeCounts['U']['M'];
            $deuF   = $gradeCounts['D']['F']+$gradeCounts['E']['F']+$gradeCounts['U']['F'];

            $housePerformance[$houseName] = [
                'gradeCounts'       => $gradeCounts,
                'mabPercentageM'    => $totM? round($mabM   /$totM*100,2):0,
                'mabPercentageF'    => $totF? round($mabF   /$totF*100,2):0,
                'mabcPercentageM'   => $totM? round($mabcM  /$totM*100,2):0,
                'mabcPercentageF'   => $totF? round($mabcF  /$totF*100,2):0,
                'mabcdPercentageM'  => $totM? round($mabcdM /$totM*100,2):0,
                'mabcdPercentageF'  => $totF? round($mabcdF /$totF*100,2):0,
                'deuPercentageM'    => $totM? round($deuM   /$totM*100,2):0,
                'deuPercentageF'    => $totF? round($deuF   /$totF*100,2):0,
                'totalMale'         => $totM,
                'totalFemale'       => $totF,
            ];

            foreach (['Merit','A','B','C','D','E','U'] as $g){
                $overall['grades'][$g]['M'] += $gradeCounts[$g]['M'];
                $overall['grades'][$g]['F'] += $gradeCounts[$g]['F'];
            }
            $overall['grades']['total']['M'] += $totM;
            $overall['grades']['total']['F'] += $totF;
            $overall['totalMale']            += $totM;
            $overall['totalFemale']          += $totF;
        } 

        $totM = $overall['totalMale']; $totF = $overall['totalFemale'];
        $sum  = fn($g)=>$overall['grades'][$g]['M']+$overall['grades'][$g]['F'];

        $overall['MAB%']['M']   = $totM? round(($overall['grades']['Merit']['M']+$overall['grades']['A']['M']+$overall['grades']['B']['M'])/$totM*100,2):0;
        $overall['MAB%']['F']   = $totF? round(($overall['grades']['Merit']['F']+$overall['grades']['A']['F']+$overall['grades']['B']['F'])/$totF*100,2):0;

        $overall['MABC%']['M']  = $totM? round(($overall['grades']['Merit']['M']+$overall['grades']['A']['M']+$overall['grades']['B']['M']+$overall['grades']['C']['M'])/$totM*100,2):0;
        $overall['MABC%']['F']  = $totF? round(($overall['grades']['Merit']['F']+$overall['grades']['A']['F']+$overall['grades']['B']['F']+$overall['grades']['C']['F'])/$totF*100,2):0;

        $overall['MABCD%']['M'] = $totM? round(($overall['grades']['Merit']['M']+$overall['grades']['A']['M']+$overall['grades']['B']['M']+$overall['grades']['C']['M']+$overall['grades']['D']['M'])/$totM*100,2):0;
        $overall['MABCD%']['F'] = $totF? round(($overall['grades']['Merit']['F']+$overall['grades']['A']['F']+$overall['grades']['B']['F']+$overall['grades']['C']['F']+$overall['grades']['D']['F'])/$totF*100,2):0;

        $overall['DEU%']['M']   = $totM? round(($overall['grades']['D']['M']+$overall['grades']['E']['M']+$overall['grades']['U']['M'])/$totM*100,2):0;
        $overall['DEU%']['F']   = $totF? round(($overall['grades']['D']['F']+$overall['grades']['E']['F']+$overall['grades']['U']['F'])/$totF*100,2):0;

        $viewData = [
            'housePerformance' => $housePerformance,
            'overallTotals'    => $overall,
            'school_data'      => $school_data,
            'currentTerm'      => $currentTerm,
            'type'             => 'CA',
            'allSubjects'      => $allSubjects,
            'test'             => $test,
            'term'             => $term,
        ];

        if (request()->query('export') === 'excel') {
            return Excel::download(
                new JuniorHouseOverallPerformanceExport($viewData),
                'overall-house-performance-by-gender-ca-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('houses.overall-ca-houses-junior', $viewData);
    }

    /**
     * Generate overall exam house performance report
     */

     public function generateOverallExamHousePerformanceReport(){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm    = Term::findOrFail($selectedTermId);
        $school_data    = SchoolSetup::first();
        $test = Test::where('term_id', $selectedTermId)->where('sequence', 1)->where('type', 'Exam')->first();
        $term = Term::findOrFail($selectedTermId);

        // OPTIMIZED: Single query to get all house students with class/grade info
        $houseStudentData = DB::table('houses')
            ->join('student_house', 'houses.id', '=', 'student_house.house_id')
            ->join('students', 'student_house.student_id', '=', 'students.id')
            ->leftJoin('klass_student', function($join) use ($selectedTermId) {
                $join->on('students.id', '=', 'klass_student.student_id')
                     ->where('klass_student.term_id', '=', $selectedTermId);
            })
            ->leftJoin('klasses', 'klass_student.klass_id', '=', 'klasses.id')
            ->leftJoin('grades', 'klasses.grade_id', '=', 'grades.id')
            ->where('student_house.term_id', $selectedTermId)
            ->whereNull('students.deleted_at')
            ->whereNotNull('klasses.id')
            ->whereNotNull('klasses.grade_id')
            ->select(
                'houses.id as house_id',
                'houses.name as house_name',
                'students.id as student_id',
                'students.gender',
                'students.nationality',
                'klasses.id as class_id',
                'klasses.grade_id',
                'grades.name as grade_name'
            )->get();

        $studentIds = $houseStudentData->pluck('student_id')->unique()->toArray();
        $gradeIds = $houseStudentData->pluck('grade_id')->unique()->toArray();

        // OPTIMIZED: Load grade subjects with subject info in one query
        $gradeSubjects = GradeSubject::whereIn('grade_id', $gradeIds)
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get()
            ->groupBy('grade_id');

        $allSubjects = collect($gradeSubjects)->flatten()->pluck('subject.name')
            ->unique()->sort()->values()->toArray();

        // OPTIMIZED: Load points matrix into memory (small table)
        $pointsMatrix = DB::table('overall_points_matrix')->get()
            ->groupBy('academic_year');

        // OPTIMIZED: Single query to get ALL test results for all students
        $allTestResults = DB::table('student_tests as st')
            ->join('tests as t', 'st.test_id', '=', 't.id')
            ->join('grade_subject as gs', 't.grade_subject_id', '=', 'gs.id')
            ->join('subjects as s', 'gs.subject_id', '=', 's.id')
            ->whereIn('st.student_id', $studentIds)
            ->where('t.term_id', $selectedTermId)
            ->where('t.type', 'Exam')
            ->whereNull('st.deleted_at')
            ->whereNull('t.deleted_at')
            ->select(
                'st.student_id',
                'st.grade as subject_grade',
                'st.points',
                's.name as subject_name',
                'gs.mandatory',
                'gs.type as subject_type',
                'gs.grade_id'
            )->get()->groupBy('student_id');

        // Helper to determine overall grade from points
        $determineGrade = function($totalPoints, $gradeName) use ($pointsMatrix) {
            $matrix = $pointsMatrix->get($gradeName, collect());
            foreach ($matrix as $row) {
                if ($totalPoints >= $row->min && $totalPoints <= $row->max) {
                    return $row->grade;
                }
            }
            return 'U';
        };

        // Helper to calculate total points for a student
        $calculateTotalPoints = function($studentTests, $isForeigner) {
            $mandatoryPoints = 0;
            $optionalPoints = [];
            $corePoints = [];

            foreach ($studentTests as $test) {
                $points = $test->points ?? 0;
                $subjectName = $test->subject_name;

                if ($subjectName === "Setswana") {
                    if (!$isForeigner) {
                        $mandatoryPoints += $points;
                        continue;
                    }

                    if (!$test->subject_type) {
                        $optionalPoints[] = $points;
                        continue;
                    }

                    $corePoints[] = $points;
                    continue;
                }

                if ($test->mandatory) {
                    $mandatoryPoints += $points;
                } elseif (!$test->mandatory && !$test->subject_type) {
                    $optionalPoints[] = $points;
                } elseif (!$test->mandatory && $test->subject_type) {
                    $corePoints[] = $points;
                }
            }

            rsort($optionalPoints);
            rsort($corePoints);

            if ($isForeigner) {
                $bestOptionalPoints = array_sum(array_slice($optionalPoints, 0, 2));
                $remainingOptionals = array_slice($optionalPoints, 2);
            } else {
                $bestOptionalPoints = count($optionalPoints) ? $optionalPoints[0] : 0;
                $remainingOptionals = array_slice($optionalPoints, 1);
            }

            $combinedRemaining = array_merge($remainingOptionals, $corePoints);
            rsort($combinedRemaining);
            $bestFromCombined = array_sum(array_slice($combinedRemaining, 0, 2));

            return $mandatoryPoints + $bestOptionalPoints + $bestFromCombined;
        };

        // Initialize totals
        $overallTotals = [
            'grades' => [
                'Merit'=>['M'=>0,'F'=>0],'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],
                'C'=>['M'=>0,'F'=>0],'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],
                'U'=>['M'=>0,'F'=>0],'total'=>['M'=>0,'F'=>0],
            ],
            'MAB%'=>['M'=>0,'F'=>0],'MABC%'=>['M'=>0,'F'=>0],
            'MABCD%'=>['M'=>0,'F'=>0],'DEU%'=>['M'=>0,'F'=>0],
            'totalMale'=>0,'totalFemale'=>0,
        ];

        $housePerformance = [];
        $groupedByHouse = $houseStudentData->groupBy('house_name');

        foreach ($groupedByHouse as $houseName => $houseStudents) {
            $gradeCounts = [
                'Merit'=>['M'=>0,'F'=>0],'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],
                'C'=>['M'=>0,'F'=>0],'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],
                'U'=>['M'=>0,'F'=>0],'total'=>['M'=>0,'F'=>0],
            ];

            $subjectGradeCounts = [];
            foreach ($allSubjects as $sub) {
                $subjectGradeCounts[$sub] = [
                    'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],'C'=>['M'=>0,'F'=>0],
                    'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],'U'=>['M'=>0,'F'=>0],
                    'total'=>['M'=>0,'F'=>0],
                ];
            }

            foreach ($houseStudents as $studentData) {
                $sex = $studentData->gender === 'M' ? 'M' : 'F';
                $isForeigner = $studentData->nationality !== 'Motswana';
                $studentTests = $allTestResults->get($studentData->student_id, collect());

                if ($studentTests->isEmpty()) {
                    continue;
                }

                // Count subject grades
                foreach ($studentTests as $testResult) {
                    $subName = $testResult->subject_name;
                    $g = $testResult->subject_grade;

                    if (!empty($g) && isset($subjectGradeCounts[$subName][$g][$sex])) {
                        $subjectGradeCounts[$subName][$g][$sex]++;
                        $subjectGradeCounts[$subName]['total'][$sex]++;
                    }
                }

                // Calculate overall grade
                $totalPoints = $calculateTotalPoints($studentTests, $isForeigner);
                $overallG = $determineGrade($totalPoints, $studentData->grade_name);

                if ($overallG && isset($gradeCounts[$overallG][$sex])) {
                    $gradeCounts[$overallG][$sex]++;
                    $gradeCounts['total'][$sex]++;
                }
            }

            $totM = $gradeCounts['total']['M'];
            $totF = $gradeCounts['total']['F'];

            $pct = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

            $housePerformance[$houseName] = [
                'gradeCounts' => $gradeCounts,
                'mabPercentageM' => $pct($gradeCounts['Merit']['M']+$gradeCounts['A']['M']+$gradeCounts['B']['M'], $totM),
                'mabPercentageF' => $pct($gradeCounts['Merit']['F']+$gradeCounts['A']['F']+$gradeCounts['B']['F'], $totF),
                'mabcPercentageM' => $pct($gradeCounts['Merit']['M']+$gradeCounts['A']['M']+$gradeCounts['B']['M']+$gradeCounts['C']['M'], $totM),
                'mabcPercentageF' => $pct($gradeCounts['Merit']['F']+$gradeCounts['A']['F']+$gradeCounts['B']['F']+$gradeCounts['C']['F'], $totF),
                'mabcdPercentageM' => $pct($gradeCounts['Merit']['M']+$gradeCounts['A']['M']+$gradeCounts['B']['M']+$gradeCounts['C']['M']+$gradeCounts['D']['M'], $totM),
                'mabcdPercentageF' => $pct($gradeCounts['Merit']['F']+$gradeCounts['A']['F']+$gradeCounts['B']['F']+$gradeCounts['C']['F']+$gradeCounts['D']['F'], $totF),
                'deuPercentageM' => $pct($gradeCounts['D']['M']+$gradeCounts['E']['M']+$gradeCounts['U']['M'], $totM),
                'deuPercentageF' => $pct($gradeCounts['D']['F']+$gradeCounts['E']['F']+$gradeCounts['U']['F'], $totF),
                'totalMale' => $totM,
                'totalFemale' => $totF,
                'subjectGradeCounts' => $subjectGradeCounts,
            ];

            foreach (['Merit','A','B','C','D','E','U'] as $g) {
                $overallTotals['grades'][$g]['M'] += $gradeCounts[$g]['M'];
                $overallTotals['grades'][$g]['F'] += $gradeCounts[$g]['F'];
            }
            $overallTotals['grades']['total']['M'] += $totM;
            $overallTotals['grades']['total']['F'] += $totF;
            $overallTotals['totalMale'] += $totM;
            $overallTotals['totalFemale'] += $totF;
        }

        $totM = $overallTotals['totalMale'];
        $totF = $overallTotals['totalFemale'];

        $pctG = fn(array $gArr, $den) => $den ? round(array_sum($gArr) / $den * 100, 2) : 0;

        $overallTotals['MAB%']['M'] = $pctG([$overallTotals['grades']['Merit']['M'],$overallTotals['grades']['A']['M'],$overallTotals['grades']['B']['M']], $totM);
        $overallTotals['MAB%']['F'] = $pctG([$overallTotals['grades']['Merit']['F'],$overallTotals['grades']['A']['F'],$overallTotals['grades']['B']['F']], $totF);
        $overallTotals['MABC%']['M'] = $pctG([$overallTotals['grades']['Merit']['M'],$overallTotals['grades']['A']['M'],$overallTotals['grades']['B']['M'],$overallTotals['grades']['C']['M']], $totM);
        $overallTotals['MABC%']['F'] = $pctG([$overallTotals['grades']['Merit']['F'],$overallTotals['grades']['A']['F'],$overallTotals['grades']['B']['F'],$overallTotals['grades']['C']['F']], $totF);
        $overallTotals['MABCD%']['M'] = $pctG([$overallTotals['grades']['Merit']['M'],$overallTotals['grades']['A']['M'],$overallTotals['grades']['B']['M'],$overallTotals['grades']['C']['M'],$overallTotals['grades']['D']['M']], $totM);
        $overallTotals['MABCD%']['F'] = $pctG([$overallTotals['grades']['Merit']['F'],$overallTotals['grades']['A']['F'],$overallTotals['grades']['B']['F'],$overallTotals['grades']['C']['F'],$overallTotals['grades']['D']['F']], $totF);
        $overallTotals['DEU%']['M'] = $pctG([$overallTotals['grades']['D']['M'],$overallTotals['grades']['E']['M'],$overallTotals['grades']['U']['M']], $totM);
        $overallTotals['DEU%']['F'] = $pctG([$overallTotals['grades']['D']['F'],$overallTotals['grades']['E']['F'],$overallTotals['grades']['U']['F']], $totF);

        $viewData = [
            'housePerformance' => $housePerformance,
            'overallTotals' => $overallTotals,
            'school_data' => $school_data,
            'currentTerm' => $currentTerm,
            'type' => 'Exam',
            'allSubjects' => $allSubjects,
            'test' => $test,
            'term' => $term
        ];

        if (request()->query('export') === 'excel') {
            return Excel::download(
                new JuniorHouseOverallPerformanceExport($viewData),
                'overall-house-performance-by-gender-exam-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('houses.overall-houses-junior', $viewData);
    }

    /**
     * Simplified version without gender distinctions - just totals
     */
    public function generateOverallExamHousePerformanceReportSimple(string $type, int $sequenceId){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm    = Term::findOrFail($selectedTermId);
        $school_data    = SchoolSetup::first();
        $test = Test::where('term_id', $selectedTermId)->where('sequence', $sequenceId)->where('type', $type)->first();
        $term = Term::findOrFail($selectedTermId);

        // Get all house students with their class/grade info for this term
        $houseStudentData = DB::table('houses')
            ->join('student_house', 'houses.id', '=', 'student_house.house_id')
            ->join('students', 'student_house.student_id', '=', 'students.id')
            ->leftJoin('klass_student', function($join) use ($selectedTermId) {
                $join->on('students.id', '=', 'klass_student.student_id')
                     ->where('klass_student.term_id', '=', $selectedTermId);
            })
            ->leftJoin('klasses', 'klass_student.klass_id', '=', 'klasses.id')
            ->leftJoin('grades', 'klasses.grade_id', '=', 'grades.id')
            ->where('student_house.term_id', $selectedTermId)
            ->whereNull('students.deleted_at')
            ->whereNotNull('klasses.id')
            ->whereNotNull('klasses.grade_id')
            ->select(
                'houses.name as house_name',
                'students.id as student_id',
                'students.nationality',
                'grades.id as grade_id',
                'grades.name as grade_name'
            )->get();

        $studentIds = $houseStudentData->pluck('student_id')->unique()->toArray();
        $gradeIds = $houseStudentData->pluck('grade_id')->unique()->toArray();

        // Initialize totals (simplified - no gender)
        $overallTotals = [
            'grades' => ['Merit'=>0, 'A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0, 'U'=>0],
            'total' => 0,
            'MAB%' => 0, 'MABC%' => 0, 'MABCD%' => 0, 'DEU%' => 0,
        ];

        $housePerformance = [];
        $groupedByHouse = $houseStudentData->groupBy('house_name');

        if ($type === 'Exam') {
            // EXAM: Overall grade per student (points-based calculation)
            $pointsMatrix = DB::table('overall_points_matrix')->get()->groupBy('academic_year');

            $allTestResults = DB::table('student_tests as st')
                ->join('tests as t', 'st.test_id', '=', 't.id')
                ->join('grade_subject as gs', 't.grade_subject_id', '=', 'gs.id')
                ->join('subjects as s', 'gs.subject_id', '=', 's.id')
                ->whereIn('st.student_id', $studentIds)
                ->where('t.term_id', $selectedTermId)
                ->where('t.type', 'Exam')
                ->where('t.sequence', $sequenceId)
                ->whereNull('st.deleted_at')
                ->whereNull('t.deleted_at')
                ->select(
                    'st.student_id',
                    'st.points',
                    's.name as subject_name',
                    'gs.mandatory',
                    'gs.type as subject_type'
                )->get()->groupBy('student_id');

            $determineGrade = function($totalPoints, $gradeName) use ($pointsMatrix) {
                $matrix = $pointsMatrix->get($gradeName, collect());
                foreach ($matrix as $row) {
                    if ($totalPoints >= $row->min && $totalPoints <= $row->max) {
                        return $row->grade;
                    }
                }
                return 'U';
            };

            $calculateTotalPoints = function($studentTests, $isForeigner) {
                $mandatoryPoints = 0;
                $optionalPoints = [];
                $corePoints = [];

                foreach ($studentTests as $test) {
                    $points = $test->points ?? 0;
                    $subjectName = $test->subject_name;

                    if ($subjectName === "Setswana") {
                        if (!$isForeigner) {
                            $mandatoryPoints += $points;
                            continue;
                        }

                        if (!$test->subject_type) {
                            $optionalPoints[] = $points;
                            continue;
                        }

                        $corePoints[] = $points;
                        continue;
                    }

                    if ($test->mandatory) {
                        $mandatoryPoints += $points;
                    } elseif (!$test->mandatory && !$test->subject_type) {
                        $optionalPoints[] = $points;
                    } elseif (!$test->mandatory && $test->subject_type) {
                        $corePoints[] = $points;
                    }
                }

                rsort($optionalPoints);
                rsort($corePoints);

                if ($isForeigner) {
                    $bestOptionalPoints = array_sum(array_slice($optionalPoints, 0, 2));
                    $remainingOptionals = array_slice($optionalPoints, 2);
                } else {
                    $bestOptionalPoints = count($optionalPoints) ? $optionalPoints[0] : 0;
                    $remainingOptionals = array_slice($optionalPoints, 1);
                }

                $combinedRemaining = array_merge($remainingOptionals, $corePoints);
                rsort($combinedRemaining);
                $bestFromCombined = array_sum(array_slice($combinedRemaining, 0, 2));

                return $mandatoryPoints + $bestOptionalPoints + $bestFromCombined;
            };

            foreach ($groupedByHouse as $houseName => $houseStudents) {
                $gradeCounts = ['Merit'=>0, 'A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0, 'U'=>0];
                $total = 0;

                foreach ($houseStudents as $studentData) {
                    $isForeigner = $studentData->nationality !== 'Motswana';
                    $studentTests = $allTestResults->get($studentData->student_id, collect());

                    if ($studentTests->isEmpty()) {
                        continue;
                    }

                    $totalPoints = $calculateTotalPoints($studentTests, $isForeigner);
                    $overallG = $determineGrade($totalPoints, $studentData->grade_name);

                    if ($overallG && isset($gradeCounts[$overallG])) {
                        $gradeCounts[$overallG]++;
                        $total++;
                    }
                }

                $pct = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

                $mab = $gradeCounts['Merit'] + $gradeCounts['A'] + $gradeCounts['B'];
                $mabc = $mab + $gradeCounts['C'];
                $mabcd = $mabc + $gradeCounts['D'];
                $deu = $gradeCounts['D'] + $gradeCounts['E'] + $gradeCounts['U'];

                $housePerformance[$houseName] = [
                    'gradeCounts' => $gradeCounts,
                    'total' => $total,
                    'mabPercentage' => $pct($mab, $total),
                    'mabcPercentage' => $pct($mabc, $total),
                    'mabcdPercentage' => $pct($mabcd, $total),
                    'deuPercentage' => $pct($deu, $total),
                ];

                foreach (['Merit','A','B','C','D','E','U'] as $g) {
                    $overallTotals['grades'][$g] += $gradeCounts[$g];
                }
                $overallTotals['total'] += $total;
            }
        } else {
            // CA: Per-subject grade counting (each subject grade counts)
            $allGradeSubjects = GradeSubject::whereIn('grade_id', $gradeIds)
                ->where('term_id', $selectedTermId)
                ->with('subject')
                ->get()
                ->groupBy('grade_id');

            $houses = House::with(['students' => function ($q) use ($selectedTermId) {
                $q->where('student_house.term_id', $selectedTermId);
            }])->where('term_id', $selectedTermId)->get();

            foreach ($houses as $house) {
                $houseName = $house->name;
                $students = $house->students;

                $gradeCounts = ['Merit'=>0, 'A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0, 'U'=>0];
                $total = 0;

                foreach ($students as $student) {
                    $cls = $student->currentClass();
                    if (!$cls) continue;

                    $stuGSubs = $allGradeSubjects->get($cls->grade_id, collect());

                    foreach ($stuGSubs as $gSub) {
                        $sData = AssessmentHelper::calculateSubjectCAScoresAnalysis(
                            $student, $gSub, $selectedTermId, $sequenceId, $cls->grade_id
                        );

                        $g = $sData['grade'];

                        if (isset($gradeCounts[$g])) {
                            $gradeCounts[$g]++;
                            $total++;
                        }
                    }
                }

                $pct = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

                $mab = $gradeCounts['Merit'] + $gradeCounts['A'] + $gradeCounts['B'];
                $mabc = $mab + $gradeCounts['C'];
                $mabcd = $mabc + $gradeCounts['D'];
                $deu = $gradeCounts['D'] + $gradeCounts['E'] + $gradeCounts['U'];

                $housePerformance[$houseName] = [
                    'gradeCounts' => $gradeCounts,
                    'total' => $total,
                    'mabPercentage' => $pct($mab, $total),
                    'mabcPercentage' => $pct($mabc, $total),
                    'mabcdPercentage' => $pct($mabcd, $total),
                    'deuPercentage' => $pct($deu, $total),
                ];

                foreach (['Merit','A','B','C','D','E','U'] as $g) {
                    $overallTotals['grades'][$g] += $gradeCounts[$g];
                }
                $overallTotals['total'] += $total;
            }
        }

        // Calculate overall percentages
        $tot = $overallTotals['total'];
        $pctG = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

        $mabTotal = $overallTotals['grades']['Merit'] + $overallTotals['grades']['A'] + $overallTotals['grades']['B'];
        $mabcTotal = $mabTotal + $overallTotals['grades']['C'];
        $mabcdTotal = $mabcTotal + $overallTotals['grades']['D'];
        $deuTotal = $overallTotals['grades']['D'] + $overallTotals['grades']['E'] + $overallTotals['grades']['U'];

        $overallTotals['MAB%'] = $pctG($mabTotal, $tot);
        $overallTotals['MABC%'] = $pctG($mabcTotal, $tot);
        $overallTotals['MABCD%'] = $pctG($mabcdTotal, $tot);
        $overallTotals['DEU%'] = $pctG($deuTotal, $tot);

        $viewData = [
            'housePerformance' => $housePerformance,
            'overallTotals' => $overallTotals,
            'school_data' => $school_data,
            'currentTerm' => $currentTerm,
            'type' => $type,
            'test' => $test,
            'term' => $term
        ];

        if (request()->query('export') === 'excel') {
            return Excel::download(
                new JuniorHouseOverallPerformanceSimpleExport($viewData),
                'overall-house-performance-no-gender-' . Str::slug($type) . '-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('houses.overall-houses-junior-simple', $viewData);
    }

    /**
     * Generate overall grade house exam performance report
     */
    // generateOverallGradeHouseExamPerformanceReport
    public function generateOverallGradeHouseExamPerformanceReport($classId, $type = 'Exam', $sequence = 1){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm    = Term::findOrFail($selectedTermId);
        $school_data    = SchoolSetup::first();

        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;
        $grade          = Grade::findOrFail($gradeId);

        $test = Test::where('term_id', $selectedTermId)->where('sequence', $sequence)->where('type', $type)->first();
        $gradeStudentData = DB::table('houses')
            ->join('student_house', 'houses.id', '=', 'student_house.house_id')
            ->join('students', 'student_house.student_id', '=', 'students.id')
            ->join('student_term', function($join) use ($selectedTermId) {
                $join->on('students.id', '=', 'student_term.student_id')
                     ->where('student_term.term_id', '=', $selectedTermId)
                     ->where('student_term.status', '=', 'Current');
            })
            ->leftJoin('klass_student', function($join) use ($selectedTermId) {
                $join->on('students.id', '=', 'klass_student.student_id')
                     ->where('klass_student.term_id', '=', $selectedTermId);
            })
            ->leftJoin('klasses', 'klass_student.klass_id', '=', 'klasses.id')
            ->where('houses.term_id', $selectedTermId)
            ->where('klasses.grade_id', $gradeId)
            ->whereNull('students.deleted_at')
            ->distinct()
            ->select(
                'houses.id as house_id',
                'houses.name as house_name',
                'students.id as student_id',
                'students.first_name',
                'students.last_name',
                'students.gender',
                'students.nationality',
                'klasses.id as class_id',
                'klasses.name as class_name',
                'klasses.grade_id'
            )->get();

        $gradeSubjects = GradeSubject::where('grade_id', $gradeId)
                                    ->where('term_id', $selectedTermId)
                                    ->with('subject')
                                    ->get();

        $allSubjects = $gradeSubjects->pluck('subject.name')
                                    ->unique()
                                    ->sort()
                                    ->values()
                                    ->toArray();

        $overallTotals = [
            'grades' => [
                'Merit'=>['M'=>0,'F'=>0],'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],
                'C'   =>['M'=>0,'F'=>0],'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],
                'U'   =>['M'=>0,'F'=>0],'total'=>['M'=>0,'F'=>0],
            ],
            'MAB%'=>['M'=>0,'F'=>0],'MABC%'=>['M'=>0,'F'=>0],
            'MABCD%'=>['M'=>0,'F'=>0],'DEU%'=>['M'=>0,'F'=>0],
            'totalMale'=>0,'totalFemale'=>0,
        ];

        $housePerformance = [];
        $groupedByHouse = $gradeStudentData->groupBy('house_name');

        foreach ($groupedByHouse as $houseName => $houseStudents) {
            $gradeCounts = [
                'Merit'=>['M'=>0,'F'=>0],'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],
                'C'=>['M'=>0,'F'=>0],'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],
                'U'=>['M'=>0,'F'=>0],'total'=>['M'=>0,'F'=>0],
            ];

            $subjectGradeCounts = [];
            foreach ($allSubjects as $sub) {
                $subjectGradeCounts[$sub] = [
                    'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],'C'=>['M'=>0,'F'=>0],
                    'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],'U'=>['M'=>0,'F'=>0],
                    'total'=>['M'=>0,'F'=>0],
                ];
            }

            $reportCardsData = [];

            foreach ($houseStudents as $studentData) {
                if (!$studentData->class_id || !$studentData->grade_id) {
                    Log::warning("Student {$studentData->first_name} {$studentData->last_name} has no class/grade - SKIPPING");
                    continue;
                }

                $student = Student::find($studentData->student_id);
                if (!$student) {
                    Log::error("Could not find Student model for ID: {$studentData->student_id}");
                    continue;
                }

                $studentClass = Klass::with('grade')->find($studentData->class_id);
                if (!$studentClass || !$studentClass->grade) {
                    Log::error("Could not find Klass model or Grade for Klass ID: {$studentData->class_id}");
                    continue;
                }

                $isForeigner = $student->nationality !== 'Motswana';
                $subjectScores = [];
                foreach ($gradeSubjects as $gs) {
                    try {
                        $subRes = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                                    $student, $gs, $selectedTermId, $type, $sequence, $studentData->grade_id);

                        $subName = $gs->subject->name;
                        $subjectScores[$subName] = [
                            'percentage'=>$subRes['percentage'], 'grade'=>$subRes['grade']
                        ];

                        $g = $subRes['grade'];
                        $sex = $student->gender === 'M' ? 'M' : 'F';

                        if (isset($subjectGradeCounts[$subName][$g][$sex])) {
                            $subjectGradeCounts[$subName][$g][$sex]++;
                            $subjectGradeCounts[$subName]['total'][$sex]++;
                        }
                    } catch (Exception $e) {
                        Log::error("Error calculating subject scores for {$gs->subject->name}: " . $e->getMessage());
                    }
                }

                try {
                    [$mand, $bestOpt, $bestCore] = AssessmentHelper::calculatePointsGeneral(
                                                    $student, $isForeigner, $gradeSubjects,
                                                    $selectedTermId, $type, $sequence);

                    $totalPts = $mand + $bestOpt + $bestCore;
                    $overallG = AssessmentHelper::determineGrade($totalPts, $studentClass);

                    $sex = $student->gender === 'M' ? 'M' : 'F';
                    if (isset($gradeCounts[$overallG][$sex])) {
                        $gradeCounts[$overallG][$sex]++;
                        $gradeCounts['total'][$sex]++;
                    }

                    $reportCardsData[] = [
                        'student'=>$student, 'scores'=>$subjectScores,
                        'totalPoints'=>$totalPts, 'grade'=>$overallG,
                        'class_name'=>$studentClass->name,
                    ];
                } catch (Exception $e) {
                    Log::error("Error calculating points/grade for student {$student->full_name}: " . $e->getMessage());
                }
            }

            $totM = $gradeCounts['total']['M'];
            $totF = $gradeCounts['total']['F'];

            $pct = fn($num,$den) => $den ? round($num/$den*100,2) : 0;
            $housePerformance[$houseName] = [
                'gradeCounts'      => $gradeCounts,
                'mabPercentageM'   => $pct($gradeCounts['Merit']['M']+$gradeCounts['A']['M']+$gradeCounts['B']['M'], $totM),
                'mabPercentageF'   => $pct($gradeCounts['Merit']['F']+$gradeCounts['A']['F']+$gradeCounts['B']['F'], $totF),
                'mabcPercentageM'  => $pct($gradeCounts['Merit']['M']+$gradeCounts['A']['M']+$gradeCounts['B']['M']+$gradeCounts['C']['M'], $totM),
                'mabcPercentageF'  => $pct($gradeCounts['Merit']['F']+$gradeCounts['A']['F']+$gradeCounts['B']['F']+$gradeCounts['C']['F'], $totF),
                'mabcdPercentageM' => $pct($gradeCounts['Merit']['M']+$gradeCounts['A']['M']+$gradeCounts['B']['M']+$gradeCounts['C']['M']+$gradeCounts['D']['M'], $totM),
                'mabcdPercentageF' => $pct($gradeCounts['Merit']['F']+$gradeCounts['A']['F']+$gradeCounts['B']['F']+$gradeCounts['C']['F']+$gradeCounts['D']['F'], $totF),
                'deuPercentageM'   => $pct($gradeCounts['D']['M']+$gradeCounts['E']['M']+$gradeCounts['U']['M'], $totM),
                'deuPercentageF'   => $pct($gradeCounts['D']['F']+$gradeCounts['E']['F']+$gradeCounts['U']['F'], $totF),
                'totalMale'        => $totM,
                'totalFemale'      => $totF,
                'subjectGradeCounts'=> $subjectGradeCounts,
                'students'         => $reportCardsData,
            ];

            foreach (['Merit','A','B','C','D','E','U'] as $g){
                $overallTotals['grades'][$g]['M'] += $gradeCounts[$g]['M'];
                $overallTotals['grades'][$g]['F'] += $gradeCounts[$g]['F'];
            }
            $overallTotals['grades']['total']['M'] += $totM;
            $overallTotals['grades']['total']['F'] += $totF;
            $overallTotals['totalMale']            += $totM;
            $overallTotals['totalFemale']          += $totF;
        }

        $totM = $overallTotals['totalMale'];
        $totF = $overallTotals['totalFemale'];

        $pctG = function(array $gArr, $den){ return $den? round(array_sum($gArr)/$den*100,2):0; };

        $overallTotals['MAB%']['M']   = $pctG([$overallTotals['grades']['Merit']['M'],$overallTotals['grades']['A']['M'],$overallTotals['grades']['B']['M']], $totM);
        $overallTotals['MAB%']['F']   = $pctG([$overallTotals['grades']['Merit']['F'],$overallTotals['grades']['A']['F'],$overallTotals['grades']['B']['F']], $totF);

        $overallTotals['MABC%']['M']  = $pctG([$overallTotals['grades']['Merit']['M'],$overallTotals['grades']['A']['M'],$overallTotals['grades']['B']['M'],$overallTotals['grades']['C']['M']], $totM);
        $overallTotals['MABC%']['F']  = $pctG([$overallTotals['grades']['Merit']['F'],$overallTotals['grades']['A']['F'],$overallTotals['grades']['B']['F'],$overallTotals['grades']['C']['F']], $totF);

        $overallTotals['MABCD%']['M'] = $pctG([$overallTotals['grades']['Merit']['M'],$overallTotals['grades']['A']['M'],$overallTotals['grades']['B']['M'],$overallTotals['grades']['C']['M'],$overallTotals['grades']['D']['M']], $totM);
        $overallTotals['MABCD%']['F'] = $pctG([$overallTotals['grades']['Merit']['F'],$overallTotals['grades']['A']['F'],$overallTotals['grades']['B']['F'],$overallTotals['grades']['C']['F'],$overallTotals['grades']['D']['F']], $totF);

        $overallTotals['DEU%']['M']   = $pctG([$overallTotals['grades']['D']['M'],$overallTotals['grades']['E']['M'],$overallTotals['grades']['U']['M']], $totM);
        $overallTotals['DEU%']['F']   = $pctG([$overallTotals['grades']['D']['F'],$overallTotals['grades']['E']['F'],$overallTotals['grades']['U']['F']], $totF);

        $viewData = [
            'housePerformance' => $housePerformance,
            'overallTotals'    => $overallTotals,
            'grade'           => $grade,
            'school_data'     => $school_data,
            'currentTerm'     => $currentTerm,
            'type'            => $type,
            'allSubjects'     => $allSubjects,
            'test'            => $test,
            'term'            => $currentTerm,
        ];

        if (request()->query('export') === 'excel') {
            return Excel::download(
                new JuniorGradeHousePerformanceExport($viewData),
                'overall-by-grade-by-gender-' . Str::slug($grade->name ?? 'grade') . '-' . Str::slug($type) . '-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('houses.overall-grade-houses-junior', $viewData);
    }

    /**
     * Generate overall grade house exam performance report (simple)
     */
    // generateOverallGradeHouseExamPerformanceReportSimple - No gender distinctions
    public function generateOverallGradeHouseExamPerformanceReportSimple($classId, $type = 'Exam', $sequence = 1){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm    = Term::findOrFail($selectedTermId);
        $school_data    = SchoolSetup::first();

        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;
        $grade = Grade::findOrFail($gradeId);

        $test = Test::where('term_id', $selectedTermId)->where('sequence', $sequence)->where('type', $type)->first();
        $gradeStudentData = DB::table('houses')
            ->join('student_house', 'houses.id', '=', 'student_house.house_id')
            ->join('students', 'student_house.student_id', '=', 'students.id')
            ->join('student_term', function($join) use ($selectedTermId) {
                $join->on('students.id', '=', 'student_term.student_id')
                     ->where('student_term.term_id', '=', $selectedTermId)
                     ->where('student_term.status', '=', 'Current');
            })->leftJoin('klass_student', function($join) use ($selectedTermId) {
                $join->on('students.id', '=', 'klass_student.student_id')
                     ->where('klass_student.term_id', '=', $selectedTermId);
            })
            ->leftJoin('klasses', 'klass_student.klass_id', '=', 'klasses.id')
            ->where('houses.term_id', $selectedTermId)
            ->where('klasses.grade_id', $gradeId)
            ->whereNull('students.deleted_at')
            ->distinct()
            ->select(
                'houses.name as house_name',
                'students.id as student_id',
                'students.nationality',
                'klasses.id as class_id',
                'klasses.grade_id'
            )->get();

        $gradeSubjects = GradeSubject::where('grade_id', $gradeId)
                                    ->where('term_id', $selectedTermId)
                                    ->with('subject')
                                    ->get();

        $allSubjects = $gradeSubjects->pluck('subject.name')
                                    ->unique()
                                    ->sort()
                                    ->values()
                                    ->toArray();

        // Initialize totals (simplified - no gender)
        $overallTotals = [
            'grades' => ['Merit'=>0, 'A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0, 'U'=>0],
            'total' => 0,
            'MAB%' => 0, 'MABC%' => 0, 'MABCD%' => 0, 'DEU%' => 0,
        ];

        $housePerformance = [];
        $groupedByHouse = $gradeStudentData->groupBy('house_name');

        foreach ($groupedByHouse as $houseName => $houseStudents) {
            $gradeCounts = ['Merit'=>0, 'A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0, 'U'=>0];
            $total = 0;

            $subjectGradeCounts = [];
            foreach ($allSubjects as $sub) {
                $subjectGradeCounts[$sub] = ['A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0, 'U'=>0, 'total'=>0];
            }

            foreach ($houseStudents as $studentData) {
                if (!$studentData->class_id || !$studentData->grade_id) {
                    continue;
                }

                $student = Student::find($studentData->student_id);
                if (!$student) {
                    continue;
                }

                $studentClass = Klass::with('grade')->find($studentData->class_id);
                if (!$studentClass || !$studentClass->grade) {
                    continue;
                }

                $isForeigner = $student->nationality !== 'Motswana';

                // Process subject grades
                foreach ($gradeSubjects as $gs) {
                    try {
                        $subRes = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                                    $student, $gs, $selectedTermId, $type, $sequence, $studentData->grade_id);

                        $subName = $gs->subject->name;
                        $g = $subRes['grade'];

                        if (isset($subjectGradeCounts[$subName][$g])) {
                            $subjectGradeCounts[$subName][$g]++;
                            $subjectGradeCounts[$subName]['total']++;
                        }
                    } catch (Exception $e) {
                        Log::error("Error calculating subject scores: " . $e->getMessage());
                    }
                }

                // Calculate overall grade
                try {
                    [$mand, $bestOpt, $bestCore] = AssessmentHelper::calculatePointsGeneral(
                                                    $student, $isForeigner, $gradeSubjects,
                                                    $selectedTermId, $type, $sequence);

                    $totalPts = $mand + $bestOpt + $bestCore;
                    $overallG = AssessmentHelper::determineGrade($totalPts, $studentClass);

                    if (isset($gradeCounts[$overallG])) {
                        $gradeCounts[$overallG]++;
                        $total++;
                    }
                } catch (Exception $e) {
                    Log::error("Error calculating points/grade: " . $e->getMessage());
                }
            }

            $pct = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

            $mab = $gradeCounts['Merit'] + $gradeCounts['A'] + $gradeCounts['B'];
            $mabc = $mab + $gradeCounts['C'];
            $mabcd = $mabc + $gradeCounts['D'];
            $deu = $gradeCounts['D'] + $gradeCounts['E'] + $gradeCounts['U'];

            $housePerformance[$houseName] = [
                'gradeCounts' => $gradeCounts,
                'total' => $total,
                'mabPercentage' => $pct($mab, $total),
                'mabcPercentage' => $pct($mabc, $total),
                'mabcdPercentage' => $pct($mabcd, $total),
                'deuPercentage' => $pct($deu, $total),
                'subjectGradeCounts' => $subjectGradeCounts,
            ];

            // Update overall totals
            foreach (['Merit','A','B','C','D','E','U'] as $g) {
                $overallTotals['grades'][$g] += $gradeCounts[$g];
            }
            $overallTotals['total'] += $total;
        }

        // Calculate overall percentages
        $tot = $overallTotals['total'];
        $pctG = fn($num, $den) => $den ? round($num / $den * 100, 2) : 0;

        $mabTotal = $overallTotals['grades']['Merit'] + $overallTotals['grades']['A'] + $overallTotals['grades']['B'];
        $mabcTotal = $mabTotal + $overallTotals['grades']['C'];
        $mabcdTotal = $mabcTotal + $overallTotals['grades']['D'];
        $deuTotal = $overallTotals['grades']['D'] + $overallTotals['grades']['E'] + $overallTotals['grades']['U'];

        $overallTotals['MAB%'] = $pctG($mabTotal, $tot);
        $overallTotals['MABC%'] = $pctG($mabcTotal, $tot);
        $overallTotals['MABCD%'] = $pctG($mabcdTotal, $tot);
        $overallTotals['DEU%'] = $pctG($deuTotal, $tot);

        $viewData = [
            'housePerformance' => $housePerformance,
            'overallTotals' => $overallTotals,
            'grade' => $grade,
            'school_data' => $school_data,
            'currentTerm' => $currentTerm,
            'type' => $type,
            'allSubjects' => $allSubjects,
            'test' => $test,
            'term' => $currentTerm,
        ];

        if (request()->query('export') === 'excel') {
            return Excel::download(
                new JuniorGradeHousePerformanceSimpleExport($viewData),
                'overall-by-grade-no-gender-' . Str::slug($grade->name ?? 'grade') . '-' . Str::slug($type) . '-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('houses.overall-grade-houses-junior-simple', $viewData);
    }

    /**
     * Generate grade distribution report
     */
    public function generateGradeDistributionReport($classId, $sequence, $type){
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $term = Term::find($selectedTermId);

        $test = Test::where('grade_id', $gradeId)
            ->where('sequence', $sequence)
            ->where('type', $type)
            ->where('term_id', $selectedTermId)
            ->first();
            
            

        $school_data = SchoolSetup::first();
        $students = Student::whereHas('classes', function ($query) use ($gradeId) {
            $query->where('klasses.grade_id', $gradeId);
        })->get();
        
        $gradeCounts = [
            'M' => ['M' => 0, 'F' => 0],
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
        ];
        
        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get();
        
        $subjectPerformance = [];
        foreach ($allGradeSubjects as $gradeSubject) {
            $subjectName = optional($gradeSubject->subject)->name ?? 'Unknown Subject';
            $subjectPerformance[$subjectName] = [
                'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'U' => 0, 'X' => 0,
                'total' => 0, 'pass' => 0, 'fail' => 0, 'passRate' => 0,
                'malePass' => 0, 'femalePass' => 0, 'maleFail' => 0, 'femaleFail' => 0,
                'maleCount' => 0, 'femaleCount' => 0,
                'studentsWithThisSubject' => 0,
                'enrolledStudents' => 0,
                'grade_subject_id' => $gradeSubject->id
            ];
        }
        
        $klassSubjectEnrollments = DB::table('klass_subject')
            ->where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->pluck('grade_subject_id')
            ->toArray();
        
        $studentOptionalSubjects = [];
        foreach ($students as $student) {
            $optionalSubjectIds = $student->optionalSubjects()
                ->where('optional_subjects.term_id', $selectedTermId)
                ->pluck('grade_subject_id')
                ->toArray();
            
            $studentOptionalSubjects[$student->id] = $optionalSubjectIds;
        }
        
        $reportCardsData = [];
        foreach ($students as $student) {
            $isForeigner = $student->nationality !== 'Motswana';
            $hasParticipated = false;
            $subjectScores = [];
            $subjectPoints = [];
            
            foreach ($allGradeSubjects as $gradeSubject) {
                $subjectName = optional($gradeSubject->subject)->name ?? 'Unknown Subject';
                $isEnrolled = false;
                
                if (in_array($gradeSubject->id, $klassSubjectEnrollments)) {
                    $isEnrolled = true;
                }
                
                if (!$isEnrolled && isset($studentOptionalSubjects[$student->id]) && 
                    in_array($gradeSubject->id, $studentOptionalSubjects[$student->id])) {
                    $isEnrolled = true;
                }
                
                if ($isEnrolled) {
                    $subjectPerformance[$subjectName]['enrolledStudents']++;
                }
            }

            foreach ($allGradeSubjects as $gradeSubject) {
                $subjectData = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                    $student,
                    $gradeSubject,
                    $selectedTermId,
                    $type,
                    $sequence,
                    $gradeId
                );
                
                $subjectName = optional($gradeSubject->subject)->name ?? 'Unknown Subject';
                $subGrade = $subjectData['grade'] ?? 'X';
                $subPercentage = $subjectData['percentage'] ?? null;
                $subPoints = $subjectData['points'] ?? null;
                
                if (!is_null($subjectData['percentage'])) {
                    $hasParticipated = true;
                }
                
                $subjectScores[$subjectName] = [
                    'percentage' => $subPercentage,
                    'grade' => $subGrade,
                ];
                $subjectPoints[$gradeSubject->id] = $subPoints;
                if (!is_null($subPercentage)) {
                    $gender = $student->gender === 'M' ? 'male' : 'female';
                    $subjectPerformance[$subjectName]['studentsWithThisSubject']++;
                    
                    if ($subGrade !== 'X') {
                        if ($subGrade === 'M') {
                            $subGrade = 'A';
                        }
                        
                        $subjectPerformance[$subjectName][$subGrade]++;
                        $subjectPerformance[$subjectName]['total']++;
                        $subjectPerformance[$subjectName][$gender.'Count']++;

                        if (in_array($subGrade, ['A', 'B', 'C'])) {
                            $subjectPerformance[$subjectName]['pass']++;
                            $subjectPerformance[$subjectName][$gender.'Pass']++;
                        } elseif (in_array($subGrade, ['D', 'E', 'U'])) {
                            $subjectPerformance[$subjectName]['fail']++;
                            $subjectPerformance[$subjectName][$gender.'Fail']++;
                        }
                    } else {
                        $subjectPerformance[$subjectName]['X']++;
                    }
                }
            }
            
            list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = AssessmentHelper::calculatePointsGeneral(
                $student,
                $isForeigner,
                $allGradeSubjects,
                $selectedTermId,
                $type,
                $sequence
            );

            $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;
            $currentClass = $student->currentClass();
            $className = $currentClass ? $currentClass->name : '';
            
            $overallGrade = $hasParticipated
                ? AssessmentHelper::determineGrade($totalPoints, $currentClass)
                : 'X';
            
            if ($overallGrade === 'Merit') {
                $overallGrade = 'M';
            }
            
            $reportCardsData[] = [
                'student' => $student,
                'scores' => $subjectScores,
                'totalPoints' => $hasParticipated ? $totalPoints : 'X',
                'grade' => $overallGrade,
                'class_name' => $className,
                'hasValidGrade' => $overallGrade !== 'X',
            ];
        }
        
        $studentsWithGrades = array_filter($reportCardsData, function($student) {
            return $student['hasValidGrade'];
        });
        
        $maleCount = 0;
        $femaleCount = 0;
        $validMaleCount = 0;
        $validFemaleCount = 0;
        
        foreach ($reportCardsData as $reportCard) {
            $grade = $reportCard['grade'] ?? '';
            $gender = $reportCard['student']->gender === 'M' ? 'M' : 'F';
            
            if ($gender === 'M') {
                $maleCount++;
                if ($grade !== 'X') {
                    $validMaleCount++;
                    if (isset($gradeCounts[$grade][$gender])) {
                        $gradeCounts[$grade][$gender]++;
                    }
                }
            } else {
                $femaleCount++;
                if ($grade !== 'X') {
                    $validFemaleCount++;
                    if (isset($gradeCounts[$grade][$gender])) {
                        $gradeCounts[$grade][$gender]++;
                    }
                }
            }
        }
        
        $totalStudents = count($studentsWithGrades);
        $validTotalStudents = $validMaleCount + $validFemaleCount;
        
        $safePercentage = function($count, $total) {
            return $total > 0 ? round(($count / $total) * 100, 1) : 0;
        };
        
        $sumM = $gradeCounts['M']['M'] + $gradeCounts['M']['F'];
        $sumA = $gradeCounts['A']['M'] + $gradeCounts['A']['F'];
        $sumB = $gradeCounts['B']['M'] + $gradeCounts['B']['F'];
        $sumC = $gradeCounts['C']['M'] + $gradeCounts['C']['F'];
        $sumD = $gradeCounts['D']['M'] + $gradeCounts['D']['F'];
        $sumE = $gradeCounts['E']['M'] + $gradeCounts['E']['F'];
        $sumU = $gradeCounts['U']['M'] + $gradeCounts['U']['F'];
        
        $m_M = $gradeCounts['M']['M']; $m_F = $gradeCounts['M']['F'];
        $a_M = $gradeCounts['A']['M']; $a_F = $gradeCounts['A']['F'];
        $b_M = $gradeCounts['B']['M']; $b_F = $gradeCounts['B']['F'];
        $c_M = $gradeCounts['C']['M']; $c_F = $gradeCounts['C']['F'];
        $d_M = $gradeCounts['D']['M']; $d_F = $gradeCounts['D']['F'];
        $e_M = $gradeCounts['E']['M']; $e_F = $gradeCounts['E']['F'];
        $u_M = $gradeCounts['U']['M']; $u_F = $gradeCounts['U']['F'];
        
        $mab_M = $m_M + $a_M + $b_M;
        $mabc_M = $mab_M + $c_M;
        $mabcd_M = $mabc_M + $d_M;
        
        $mab_F = $m_F + $a_F + $b_F;
        $mabc_F = $mab_F + $c_F;
        $mabcd_F = $mabc_F + $d_F;
        
        $mab_T = $mab_M + $mab_F;
        $mabc_T = $mabc_M + $mabc_F;
        $mabcd_T = $mabcd_M + $mabcd_F;
        
        $mab_M_Percentage = $safePercentage($mab_M, $validMaleCount);
        $mab_F_Percentage = $safePercentage($mab_F, $validFemaleCount);
        
        $mabc_M_Percentage = $safePercentage($mabc_M, $validMaleCount);
        $mabc_F_Percentage = $safePercentage($mabc_F, $validFemaleCount);
        
        $mabcd_M_Percentage = $safePercentage($mabcd_M, $validMaleCount);
        $mabcd_F_Percentage = $safePercentage($mabcd_F, $validFemaleCount);
        
        $mab_T_percentage = $safePercentage($mab_T, $validTotalStudents);
        $mabc_T_percentage = $safePercentage($mabc_T, $validTotalStudents);
        $mabcd_T_percentage = $safePercentage($mabcd_T, $validTotalStudents);
        
        foreach ($subjectPerformance as $subjectName => &$subject) {
            $total = $subject['total'];
            $pass = $subject['pass'];
            $subject['passRate'] = $total > 0 ? round(($pass / $total) * 100, 1) : 0;
            
            $maleTotal = $subject['maleCount'];
            $malePass = $subject['malePass'];
            $subject['malePassRate'] = $maleTotal > 0 ? round(($malePass / $maleTotal) * 100, 1) : 0;
            
            $femaleTotal = $subject['femaleCount'];
            $femalePass = $subject['femalePass'];
            $subject['femalePassRate'] = $femaleTotal > 0 ? round(($femalePass / $femaleTotal) * 100, 1) : 0;
        }
        
        $subjectPerformance = array_filter($subjectPerformance, function($subject) {
            return $subject['enrolledStudents'] > 0;
        });
        
        $subjectNames = array_keys($subjectPerformance);
        $data = [
            'grade' => Grade::find($gradeId),
            'school_data' => $school_data,
            'currentTerm' => $term,
            'test' => $test,
            'gradeCounts' => $gradeCounts,
            'totalStudents' => $totalStudents,
            'validTotalStudents' => $validTotalStudents,
            'maleCount' => $validMaleCount,
            'femaleCount' => $validFemaleCount,
            'validMaleCount' => $validMaleCount,
            'validFemaleCount' => $validFemaleCount,
            
            'sumM' => $sumM,
            'sumA' => $sumA,
            'sumB' => $sumB,
            'sumC' => $sumC,
            'sumD' => $sumD,
            'sumE' => $sumE,
            'sumU' => $sumU,
            
            'm_M' => $m_M, 'm_F' => $m_F,
            'a_M' => $a_M, 'a_F' => $a_F,
            'b_M' => $b_M, 'b_F' => $b_F,
            'c_M' => $c_M, 'c_F' => $c_F,
            'd_M' => $d_M, 'd_F' => $d_F,
            'e_M' => $e_M, 'e_F' => $e_F,
            'u_M' => $u_M, 'u_F' => $u_F,
            
            'mab_M' => $mab_M,
            'mab_F' => $mab_F,
            'mabc_M' => $mabc_M,
            'mabc_F' => $mabc_F,
            'mabcd_M' => $mabcd_M,
            'mabcd_F' => $mabcd_F,
            'mab_T' => $mab_T,
            'mabc_T' => $mabc_T,
            'mabcd_T' => $mabcd_T,
            
            'mab_M_Percentage' => $mab_M_Percentage,
            'mab_F_Percentage' => $mab_F_Percentage,
            'mabc_M_Percentage' => $mabc_M_Percentage,
            'mabc_F_Percentage' => $mabc_F_Percentage,
            'mabcd_M_Percentage' => $mabcd_M_Percentage,
            'mabcd_F_Percentage' => $mabcd_F_Percentage,
            'mab_T_percentage' => $mab_T_percentage,
            'mabc_T_percentage' => $mabc_T_percentage,
            'mabcd_T_percentage' => $mabcd_T_percentage,
            
            'subjectPerformance' => $subjectPerformance,
            'subjectNames' => $subjectNames,
            'klassName' => $klass->name,
            'sequenceInfo' => $sequence,
            'typeInfo' => $type
        ];
        
        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\GradeDistributionExport($data),
                "Grade_Distribution-" . date('Y-m-d') . ".xlsx"
            );
        }
        
        return view('assessment.junior.grade-distribution-by-gender', $data);
    }

    /**
     * Generate analysis by department
     */
    public function generateAnalysisByDepartment(Request $request, $classId, $sequenceId, $type){
        $klass      = Klass::findOrFail($classId);
        $gradeId    = $klass->grade_id;
        $termId     = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        $test = Test::where('term_id', $termId)->where('grade_id', $gradeId)->where('sequence', $sequenceId)->where('type', $type)->first();
        $term = Term::findOrFail($termId);
    
        $gradeSubjects = GradeSubject::with(['subject:id,name,is_double', 'department:id,name'])->where('grade_id', $gradeId)->where('term_id',  $termId)->get();
        $performanceData = [];
        $overallTotals   = array_fill_keys(['A','B','C','D','E','U'], 0) + [
            'total'         => 0,
            'ab_percent'    => 0,
            'abc_percent'   => 0,
            'abcd_percent'  => 0,
            'gender'        => array_fill_keys(
                ['A','B','C','D','E','U'],
                ['M' => 0, 'F' => 0]
            )
        ];
    
        foreach ($gradeSubjects as $gradeSubject) {
            if (!$gradeSubject->subject) {
                continue;
            }
    
            $testIds = Test::where([
                ['grade_subject_id', $gradeSubject->id],
                ['sequence',         $sequenceId],
                ['term_id',          $termId],
                ['type',             $type],
            ])->pluck('id');
    
            if ($testIds->isEmpty()) {
                continue;
            }
    
            $isDouble = (bool) ($gradeSubject->subject->is_double ?? false);

            $rows = StudentTest::whereIn('test_id', $testIds)
                ->join('students', 'students.id', '=', 'student_tests.student_id')
                ->selectRaw('upper(grade) as grade, upper(substr(students.gender,1,1)) as sex, count(*) as cnt')
                ->groupBy('grade', 'sex')
                ->get();

            $gradeGender = array_fill_keys(
                ['A','B','C','D','E','U'],
                ['M' => 0, 'F' => 0]
            );

            foreach ($rows as $r) {
                $g = $r->grade;
                $s = ($r->sex === 'F') ? 'F' : 'M';
                $cnt = (int) $r->cnt;

                if ($isDouble && is_string($g) && strlen($g) === 2) {
                    foreach (str_split($g) as $char) {
                        if (isset($gradeGender[$char])) {
                            $gradeGender[$char][$s] += $cnt;
                        }
                    }
                } elseif (isset($gradeGender[$g])) {
                    $gradeGender[$g][$s] += $cnt;
                }
            }

            $gradeTotals = [];
            foreach (['A','B','C','D','E','U'] as $g) {
                $gradeTotals[$g] = $gradeGender[$g]['M'] + $gradeGender[$g]['F'];
            }

            $totalStudents = array_sum($gradeTotals);
            if ($totalStudents === 0) {
                continue;
            }
    
            $ab   = $gradeTotals['A'] + $gradeTotals['B'];
            $abc  = $ab + $gradeTotals['C'];
            $abcd = $abc + $gradeTotals['D'];
    
            $deptName = optional($gradeSubject->department)->name ?? 'Uncategorised';
            $subjName = $gradeSubject->subject->name;
    
            $performanceData[$deptName]['slug'] = Str::slug($deptName);
            $performanceData[$deptName]['subjects'][$subjName] = [
                'grades'       => $gradeGender,
                'totals'       => $gradeTotals,
                'ab_percent'   => round($ab / $totalStudents * 100),
                'abc_percent'  => round($abc / $totalStudents * 100),
                'abcd_percent' => round($abcd / $totalStudents * 100),
            ];
    
            foreach (['A','B','C','D','E','U'] as $g) {
                $overallTotals[$g]                += $gradeTotals[$g];
                $overallTotals['gender'][$g]['M'] += $gradeGender[$g]['M'];
                $overallTotals['gender'][$g]['F'] += $gradeGender[$g]['F'];
            }
            $overallTotals['total'] += $totalStudents;
        }
    
        foreach ($performanceData as $deptName => &$deptData) {
            if (isset($deptData['subjects'])) {
                uasort($deptData['subjects'], function($a, $b) {
                    if (abs($a['abc_percent'] - $b['abc_percent']) >= 0.01) {
                        return $b['abc_percent'] <=> $a['abc_percent'];
                    }
                    return $b['ab_percent'] <=> $a['ab_percent'];
                });
            }
        }
        unset($deptData);
    
        if ($overallTotals['total'] > 0) {
            $overallTotals['ab_percent'] = round(
                ($overallTotals['A'] + $overallTotals['B'])
                / $overallTotals['total'] * 100
            );
            $overallTotals['abc_percent'] = round(
                ($overallTotals['A'] + $overallTotals['B'] + $overallTotals['C'])
                / $overallTotals['total'] * 100
            );
            $overallTotals['abcd_percent'] = round(
                ($overallTotals['A'] + $overallTotals['B'] + $overallTotals['C'] + $overallTotals['D'])
                / $overallTotals['total'] * 100
            );
        }
    
        ksort($performanceData);
        $viewData = [
            'klass'           => $klass,
            'performanceData' => $performanceData,
            'totals'          => $overallTotals,
            'term'            => $term,
            'test'            => $test,
            'type'            => ucfirst($type),
            'school_data'     => SchoolSetup::first(),
        ];
    
        if ($request->boolean('export')) {
            return Excel::download(
                new \App\Exports\DepartmentAnalysisExport($viewData),
                "Performance_{$viewData['term']->term}_{$viewData['term']->year}_{$type}_" . now()->toDateString() . ".xlsx"
            );
        }
        return view('assessment.shared.department-analysis', $viewData);
    }

        # Junior schools analyis reports
        public function generateExamAnalysis($classId,$type,$sequence){
            $klass = Klass::findOrFail($classId);
            $currentTerm = TermHelper::getCurrentTerm();
            $selectedTermId = session('selected_term_id', $currentTerm->id);
        
            $school_setup = SchoolSetup::first();
            $hasSequenceOrder = GradeSubject::where('grade_id', $klass->grade_id)
                ->where('term_id', $selectedTermId)
                ->where('active', 1)
                ->where('sequence', '>', 0)
                ->exists();
    
            $allGradeSubjectsQuery = GradeSubject::where('grade_id', $klass->grade_id)
                ->where('term_id', $selectedTermId)
                ->with('subject')
                ->where('active', 1);
    
            if ($hasSequenceOrder) {
                $allGradeSubjectsQuery = $allGradeSubjectsQuery
                    ->orderByRaw('CASE WHEN sequence IS NULL OR sequence = 0 THEN 1 ELSE 0 END')
                    ->orderBy('sequence', 'asc');
            }
    
            $allGradeSubjects = $allGradeSubjectsQuery->get();
    
            $test = Test::where('type',$type)->where('term_id',$selectedTermId)->where('sequence',$sequence)->first();
            $allSubjects = $allGradeSubjects->pluck('subject.name')->filter()->unique()->values()->toArray();
            $gradeCounts = [
                'M' => ['M' => 0, 'F' => 0],
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'U' => ['M' => 0, 'F' => 0],
                'X' => ['M' => 0, 'F' => 0],
            ];
        
            $subjectGradeCounts = [];
            foreach ($allSubjects as $subject) {
                $subjectGradeCounts[$subject] = [
                    'A' => ['M' => 0, 'F' => 0],
                    'B' => ['M' => 0, 'F' => 0],
                    'C' => ['M' => 0, 'F' => 0],
                    'D' => ['M' => 0, 'F' => 0],
                    'E' => ['M' => 0, 'F' => 0],
                    'U' => ['M' => 0, 'F' => 0],
                    'total_students' => ['M' => 0, 'F' => 0],
                    'no_scores' => ['M' => 0, 'F' => 0],
                    'enrolled' => ['M' => 0, 'F' => 0],
                ];
            }
        
            $psleGradeCounts = [
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'U' => ['M' => 0, 'F' => 0],
            ];
        
            $reportCardsData = [];
            foreach ($klass->students as $student) {
                if (empty($student->pivot) || !$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                    continue;
                }
        
                $isForeigner = $student->nationality !== 'Motswana';
                $psleGrade = optional($student->psle)->overall_grade;
        
                if ($psleGrade && isset($psleGradeCounts[$psleGrade])) {
                    $gender = $student->gender === 'M' ? 'M' : 'F';
                    $psleGradeCounts[$psleGrade][$gender]++;
                }
        
                $hasParticipated = false;
                $subjectScores = [];
                $enrolledSubjects = [];
    
                // First, determine which subjects this student is actually enrolled in
                foreach ($allGradeSubjects as $gradeSubject) {
                    $subjectData = AssessmentHelper::calculateSubjectGeneralScoresAnalysis(
                        $student,
                        $gradeSubject,
                        $selectedTermId,
                        $type,
                        $sequence,
                        $klass->grade_id
                    );
    
                    $subPercentage = $subjectData['percentage'] ?? null;
                    $subjectName = optional($gradeSubject->subject)->name ?? 'Unknown Subject';
    
                    if (!is_null($subPercentage)) {
                        $hasParticipated = true;
                        $enrolledSubjects[] = $subjectName;
                        
                        $subGrade = $subjectData['grade'] ?? 'X';
                        $subjectScores[$subjectName] = [
                            'percentage' => $subPercentage,
                            'grade' => $subGrade,
                        ];
                    }
                }
    
                $optionalSubjects = DB::table('student_optional_subjects')
                    ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                    ->join('grade_subject', 'optional_subjects.grade_subject_id', '=', 'grade_subject.id')
                    ->join('subjects', 'grade_subject.subject_id', '=', 'subjects.id')
                    ->leftJoin('tests', function($join) use ($selectedTermId, $type, $sequence) {
                        $join->on('tests.grade_subject_id', '=', 'grade_subject.id')
                             ->where('tests.term_id', '=', $selectedTermId)
                             ->where('tests.type', '=', $type)
                             ->where('tests.sequence', '=', $sequence);
                    })
                    ->leftJoin('student_tests', function($join) use ($student) {
                        $join->on('student_tests.test_id', '=', 'tests.id')
                             ->where('student_tests.student_id', '=', $student->id);
                    })
                    ->where('student_optional_subjects.student_id', $student->id)
                    ->where('student_optional_subjects.term_id', $selectedTermId)
                    ->where('student_optional_subjects.klass_id', $klass->id)
                    ->where('optional_subjects.active', true)
                    ->whereNotNull('student_tests.score')
                    ->select('subjects.name as subject_name', 'student_tests.score')
                    ->distinct()
                    ->get();
    
                foreach ($optionalSubjects as $optionalSubject) {
                    if (!in_array($optionalSubject->subject_name, $enrolledSubjects)) {
                        $enrolledSubjects[] = $optionalSubject->subject_name;
                    }
                }
    
                $enrolledGradeSubjects = $allGradeSubjects->filter(function($gradeSubject) use ($enrolledSubjects) {
                    $subjectName = optional($gradeSubject->subject)->name ?? 'Unknown Subject';
                    return in_array($subjectName, $enrolledSubjects);
                });
    
                list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = AssessmentHelper::calculatePointsGeneral (
                    $student,
                    $isForeigner,
                    $enrolledGradeSubjects,
                    $selectedTermId,
                    $type,
                    $sequence
                );
                $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;
        
                $currentClass = $student->currentClass();
                $className = $currentClass ? $currentClass->name : '';
        
                $overallGrade = $hasParticipated
                    ? AssessmentHelper::determineGrade($totalPoints, $currentClass)
                    : 'X';
        
                if ($overallGrade === 'Merit') {
                    $overallGrade = 'M';
                }
        
                $reportCardsData[] = [
                    'student' => $student,
                    'scores' => $subjectScores,
                    'totalPoints' => $hasParticipated ? $totalPoints : 'X',
                    'grade' => $overallGrade,
                    'class_name' => $className,
                ];
            }
        
            usort($reportCardsData, function($a, $b) {
                $aPts = is_numeric($a['totalPoints']) ? $a['totalPoints'] : -1;
                $bPts = is_numeric($b['totalPoints']) ? $b['totalPoints'] : -1;
                if ($aPts !== $bPts) {
                    return $bPts <=> $aPts;
                }
            
                $sumA = array_sum(array_map(fn($s) => is_numeric($s['percentage']) ? $s['percentage'] : 0, $a['scores']));
                $sumB = array_sum(array_map(fn($s) => is_numeric($s['percentage']) ? $s['percentage'] : 0, $b['scores']));
            
                return $sumB <=> $sumA;
            });
        
            $studentSubjectEnrollment = [];
            foreach ($reportCardsData as $reportCard) {
                if (!isset($reportCard['student'])) {
                    continue;
                }
                
                $student = $reportCard['student'];
                $studentId = $student->id;
                
                $studentClass = $student->currentClass();
                $studentClassSubjects = $studentClass && $studentClass->subjectClasses
                    ? $studentClass->subjectClasses->pluck('subject.subject.name')->toArray()
                    : [];
        
                $studentOptionalSubjects = $student->optionalSubjects
                    ? $student->optionalSubjects->pluck('gradeSubject.subject.name')->toArray()
                    : [];
        
                $studentSubjects = array_merge($studentClassSubjects, $studentOptionalSubjects);
                $studentSubjectEnrollment[$studentId] = $studentSubjects;
            }
        
            foreach ($reportCardsData as $reportCard) {
                if (!isset($reportCard['student'])) {
                    continue;
                }
            
                $student = $reportCard['student'];
                $gender = $student->gender === 'M' ? 'M' : 'F';
                $studentId = $student->id;
                $studentEnrolledSubjects = $studentSubjectEnrollment[$studentId] ?? [];
                
                foreach ($reportCard['scores'] as $subject => $data) {
                    if (!isset($subjectGradeCounts[$subject])) {
                        continue;
                    }
                    
                    $isEnrolled = in_array($subject, $studentEnrolledSubjects);
                    
                    if ($isEnrolled) {
                        $subjectGradeCounts[$subject]['enrolled'][$gender]++;
                        $subjectGradeCounts[$subject]['total_students'][$gender]++;
                        
                        $grade = $data['grade'] ?? 'X';
                        $percentage = $data['percentage'] ?? null;
                        
                        if ($grade === 'X' || is_null($percentage)) {
                            $subjectGradeCounts[$subject]['no_scores'][$gender]++;
                        } else {
                            if (isset($subjectGradeCounts[$subject][$grade][$gender])) {
                                $subjectGradeCounts[$subject][$grade][$gender]++;
                            }
                        }
                    }
                }
            }
        
            foreach ($reportCardsData as $i => &$row) {
                $row['position'] = $i + 1;
            }
            unset($row);
        
            $maleCount = 0;
            $femaleCount = 0;
        
            foreach ($reportCardsData as $reportCard) {
                $grade = $reportCard['grade'] ?? 'X';
                $gender = $reportCard['student']->gender === 'M' ? 'M' : 'F';
        
                if (isset($gradeCounts[$grade][$gender])) {
                    $gradeCounts[$grade][$gender]++;
                }
        
                if ($gender === 'M') {
                    $maleCount++;
                } else {
                    $femaleCount++;
                }
            }
        
            $totalStudents = count($reportCardsData);
            $safePercentage = function($count, $total) {
                return AssessmentHelper::formatPercentage($count, $total);
            };
        
            $sumM = $gradeCounts['M']['M'] + $gradeCounts['M']['F'];
            $sumA = $gradeCounts['A']['M'] + $gradeCounts['A']['F'];
            $sumB = $gradeCounts['B']['M'] + $gradeCounts['B']['F'];
            $sumC = $gradeCounts['C']['M'] + $gradeCounts['C']['F'];
            $sumD = $gradeCounts['D']['M'] + $gradeCounts['D']['F'];
            $sumE = $gradeCounts['E']['M'] + $gradeCounts['E']['F'];
            $sumU = $gradeCounts['U']['M'] + $gradeCounts['U']['F'];
            $sumX_M = $gradeCounts['X']['M'];
            $sumX_F = $gradeCounts['X']['F'];
            $sumX = $sumX_M + $sumX_F;
        
            $m_M = $gradeCounts['M']['M']; $m_F = $gradeCounts['M']['F'];
            $a_M = $gradeCounts['A']['M']; $a_F = $gradeCounts['A']['F'];
            $b_M = $gradeCounts['B']['M']; $b_F = $gradeCounts['B']['F'];
            $c_M = $gradeCounts['C']['M']; $c_F = $gradeCounts['C']['F'];
            $d_M = $gradeCounts['D']['M']; $d_F = $gradeCounts['D']['F'];
            $e_M = $gradeCounts['E']['M']; $e_F = $gradeCounts['E']['F'];
            $u_M = $gradeCounts['U']['M']; $u_F = $gradeCounts['U']['F'];
            $x_M = $gradeCounts['X']['M']; $x_F = $gradeCounts['X']['F'];
        
            $mb_M = $m_M + $b_M;
            $mb_F = $m_F + $b_F;
            $mb_T = $mb_M + $mb_F;
            $mb_M_Percentage = $safePercentage($mb_M, $maleCount);
            $mb_F_Percentage = $safePercentage($mb_F, $femaleCount);
            $mb_T_percentage = $safePercentage($mb_T, $totalStudents);
        
            $mabCount = $sumM + $sumA + $sumB; 
            $mabcCount = $mabCount + $sumC;
            $mabcdCount = $mabcCount + $sumD;
            $deuCount = $sumD + $sumE + $sumU;
        
            $mab_M = $m_M + $a_M + $b_M;
            $mabc_M = $mab_M + $c_M;
            $mabcd_M = $mabc_M + $d_M;
            $deu_M = $d_M + $e_M + $u_M;
        
            $mab_F = $m_F + $a_F + $b_F;
            $mabc_F = $mab_F + $c_F;
            $mabcd_F = $mabc_F + $d_F;
            $deu_F = $d_F + $e_F + $u_F;
        
            $mab_T = $mab_M + $mab_F;
            $mabc_T = $mabc_M + $mabc_F;
            $mabcd_T = $mabcd_M + $mabcd_F;
            $deu_T = $deu_M + $deu_F;
            $x_T = $x_M + $x_F;
        
            $mabPercentage = $safePercentage($mabCount, $totalStudents);
            $mabcPercentage = $safePercentage($mabcCount, $totalStudents);
            $mabcdPercentage = $safePercentage($mabcdCount, $totalStudents);
            $deuPercentage = $safePercentage($deuCount, $totalStudents);
        
            $mab_M_Percentage = $safePercentage($mab_M, $maleCount);
            $mab_F_Percentage = $safePercentage($mab_F, $femaleCount);
            $mab_T_percentage = $safePercentage($mab_T, $totalStudents);
            $mabc_T_percentage = $safePercentage($mabc_T, $totalStudents);
            $mabcd_T_percentage = $safePercentage($mabcd_T, $totalStudents);
            $deu_T_percentage = $safePercentage($deu_T, $totalStudents);
            $x_T_Percentange = $safePercentage($x_T, $totalStudents);
        
            $mabc_M_Percentage = $safePercentage($mabc_M, $maleCount);
            $mabc_F_Percentage = $safePercentage($mabc_F, $femaleCount);
        
            $mabcd_M_Percentage = $safePercentage($mabcd_M, $maleCount);
            $mabcd_F_Percentage = $safePercentage($mabcd_F, $femaleCount);
        
            $deu_M_Percentage = $safePercentage($deu_M, $maleCount);
            $deu_F_Percentage = $safePercentage($deu_F, $femaleCount);
        
            $x_M_Percentage = $safePercentage($x_M, $maleCount);
            $x_F_Percentage = $safePercentage($x_F, $femaleCount);
        
            foreach ($subjectGradeCounts as $subject => &$counts) {
                foreach (['M', 'F'] as $gender) {
                    $totalWithScores = $counts['A'][$gender] + $counts['B'][$gender] + 
                                     $counts['C'][$gender] + $counts['D'][$gender] + 
                                     $counts['E'][$gender] + $counts['U'][$gender];
        
                    $abCount = $counts['A'][$gender] + $counts['B'][$gender];
                    $abcCount = $abCount + $counts['C'][$gender];
                    $abcdCount = $abcCount + $counts['D'][$gender];
                    $deuCountSub = $counts['D'][$gender] + $counts['E'][$gender] + $counts['U'][$gender];
        
                    $counts['AB%'][$gender] = $totalWithScores > 0 ? round(($abCount / $totalWithScores) * 100, 2) : 0;
                    $counts['ABC%'][$gender] = $totalWithScores > 0 ? round(($abcCount / $totalWithScores) * 100, 2) : 0;
                    $counts['ABCD%'][$gender] = $totalWithScores > 0 ? round(($abcdCount / $totalWithScores) * 100, 2) : 0;
                    $counts['DEU%'][$gender] = $totalWithScores > 0 ? round(($deuCountSub / $totalWithScores) * 100, 2) : 0;
                }
            }
            unset($counts);
            $subjectTotals = [
                'A' => ['M' => 0, 'F' => 0], 'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0], 'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0], 'U' => ['M' => 0, 'F' => 0],
                'total_students' => ['M' => 0, 'F' => 0],
                'no_scores' => ['M' => 0, 'F' => 0],
                'enrolled' => ['M' => 0, 'F' => 0],
                'AB%'   => ['M' => 0, 'F' => 0],
                'ABC%'  => ['M' => 0, 'F' => 0],
                'ABCD%' => ['M' => 0, 'F' => 0],
                'DEU%'  => ['M' => 0, 'F' => 0],
            ];
            
            foreach ($subjectGradeCounts as $counts) {
                foreach (['A','B','C','D','E','U','total_students','no_scores','enrolled'] as $g) {
                    $subjectTotals[$g]['M'] += $counts[$g]['M'];
                    $subjectTotals[$g]['F'] += $counts[$g]['F'];
                }
                foreach (['AB%','ABC%','ABCD%','DEU%'] as $k) {
                    $subjectTotals[$k]['M'] += $counts[$k]['M'];
                    $subjectTotals[$k]['F'] += $counts[$k]['F'];
                }
            }
            
            $subjectCount = max(count($subjectGradeCounts), 1);
            foreach (['AB%','ABC%','ABCD%','DEU%'] as $k) {
                $subjectTotals[$k]['M'] = round($subjectTotals[$k]['M'] / $subjectCount, 2);
                $subjectTotals[$k]['F'] = round($subjectTotals[$k]['F'] / $subjectCount, 2);
            }
            
            $data['subjectTotals'] = $subjectTotals;
        
            $psleTotalM = array_sum(array_column($psleGradeCounts, 'M'));
            $psleTotalF = array_sum(array_column($psleGradeCounts, 'F'));
            $totalPsleStudents = $psleTotalM + $psleTotalF;
        
            $psleA_M = $psleGradeCounts['A']['M']; $psleA_F = $psleGradeCounts['A']['F'];
            $psleB_M = $psleGradeCounts['B']['M']; $psleB_F = $psleGradeCounts['B']['F'];
            $psleC_M = $psleGradeCounts['C']['M']; $psleC_F = $psleGradeCounts['C']['F'];
            $psleD_M = $psleGradeCounts['D']['M']; $psleD_F = $psleGradeCounts['D']['F'];
            $psleE_M = $psleGradeCounts['E']['M']; $psleE_F = $psleGradeCounts['E']['F'];
            $psleU_M = $psleGradeCounts['U']['M']; $psleU_F = $psleGradeCounts['U']['F'];
        
            $psleAB_M = $psleA_M + $psleB_M; $psleAB_F = $psleA_F + $psleB_F;
            $psleAB_T = $psleAB_M + $psleAB_F;
            $psleABC_M = $psleAB_M + $psleC_M; $psleABC_F = $psleAB_F + $psleC_F;
            $psleABC_T = $psleABC_M + $psleABC_F;
            $psleABCD_M = $psleABC_M + $psleD_M; $psleABCD_F = $psleABC_F + $psleD_F;
            $psleABCD_T = $psleABCD_M + $psleABCD_F;
            $psleDEU_M = $psleD_M + $psleE_M + $psleU_M; $psleDEU_F = $psleD_F + $psleE_F + $psleU_F;
            $psleDEU_T = $psleDEU_M + $psleDEU_F;
        
            $psleAB_M_Percentage = $safePercentage($psleAB_M, $psleTotalM);
            $psleAB_F_Percentage = $safePercentage($psleAB_F, $psleTotalF);
            $psleAB_T_percentage = $safePercentage($psleAB_T, $totalPsleStudents);
        
            $psleABC_M_Percentage = $safePercentage($psleABC_M, $psleTotalM);
            $psleABC_F_Percentage = $safePercentage($psleABC_F, $psleTotalF);
            $psleABC_T_percentage = $safePercentage($psleABC_T, $totalPsleStudents);
        
            $psleABCD_M_Percentage = $safePercentage($psleABCD_M, $psleTotalM);
            $psleABCD_F_Percentage = $safePercentage($psleABCD_F, $psleTotalF);
            $psleABCD_T_percentage = $safePercentage($psleABCD_T, $totalPsleStudents);
        
            $psleDEU_M_Percentage = $safePercentage($psleDEU_M, $psleTotalM);
            $psleDEU_F_Percentage = $safePercentage($psleDEU_F, $psleTotalF);
            $psleDEU_T_percentage = $safePercentage($psleDEU_T, $totalPsleStudents);
        
            $data = [
                'reportCards' => $reportCardsData,
                'school_data' => $school_setup,
                'allSubjects' => $allSubjects,
                'currentTerm' => $currentTerm,
                'klass' => $klass,
                'test' => $test,
                'gradeCounts' => $gradeCounts,
                'subjectGradeCounts' => $subjectGradeCounts,
                'totalStudents' => $totalStudents,
                'psleGradeCounts' => $psleGradeCounts,
                'maleCount' => $maleCount,
                'femaleCount' => $femaleCount,
                'type' => $type,
        
                'mb_M' => $mb_M,
                'mb_F' => $mb_F,
                'mb_T' => $mb_T,
                'mb_M_Percentage' => $mb_M_Percentage,
                'mb_F_Percentage' => $mb_F_Percentage,
                'mb_T_percentage' => $mb_T_percentage,
        
                'mabCount' => $mabCount,
                'mabcCount' => $mabcCount,
                'mabcdCount' => $mabcdCount,
                'deuCount' => $deuCount,
                'mabPercentage' => $mabPercentage,
                'mabcPercentage' => $mabcPercentage,
                'mabcdPercentage' => $mabcdPercentage,
                'deuPercentage' => $deuPercentage,
        
                'x_T_Percentage' => $x_T_Percentange,
                'x_M_Percentage' => $x_M_Percentage,
                'x_F_Percentage' => $x_F_Percentage,
        
                'mab_M' => $mab_M,
                'mab_F' => $mab_F,
                'mabc_M' => $mabc_M,
                'mabc_F' => $mabc_F,
                'mabcd_M' => $mabcd_M,
                'mabcd_F' => $mabcd_F,
                'deu_M' => $deu_M,
                'deu_F' => $deu_F,
                'mab_T' => $mab_T,
                'mabc_T' => $mabc_T,
                'mabcd_T' => $mabcd_T,
                'deu_T' => $deu_T,
                'x_M' => $x_M,
                'x_F' => $x_F,
                'x_T' => $x_T,
        
                'mab_M_Percentage' => $mab_M_Percentage,
                'mab_F_Percentage' => $mab_F_Percentage,
                'mabc_M_Percentage' => $mabc_M_Percentage,
                'mabc_F_Percentage' => $mabc_F_Percentage,
                'mabcd_M_Percentage' => $mabcd_M_Percentage,
                'mabcd_F_Percentage' => $mabcd_F_Percentage,
                'deu_M_Percentage' => $deu_M_Percentage,
                'deu_F_Percentage' => $deu_F_Percentage,
                'mab_T_percentage' => $mab_T_percentage,
                'mabc_T_percentage' => $mabc_T_percentage,
                'mabcd_T_percentage' => $mabcd_T_percentage,
                'deu_T_percentage' => $deu_T_percentage,
        
                'psleTotalM' => $psleTotalM,
                'psleTotalF' => $psleTotalF,
        
                'psleAB_M' => $psleAB_M,
                'psleAB_F' => $psleAB_F,
                'psleABC_M' => $psleABC_M,
                'psleABC_F' => $psleABC_F,
                'psleABCD_M' => $psleABCD_M,
                'psleABCD_F' => $psleABCD_F,
                'psleDEU_M' => $psleDEU_M,
                'psleDEU_F' => $psleDEU_F,
                'psleAB_T' => $psleAB_T,
                'psleABC_T' => $psleABC_T,
                'psleABCD_T' => $psleABCD_T,
                'psleDEU_T' => $psleDEU_T,
        
                'psleAB_M_Percentage' => $psleAB_M_Percentage,
                'psleAB_F_Percentage' => $psleAB_F_Percentage,
                'psleABC_M_Percentage' => $psleABC_M_Percentage,
                'psleABC_F_Percentage' => $psleABC_F_Percentage,
                'psleABCD_M_Percentage' => $psleABCD_M_Percentage,
                'psleABCD_F_Percentage' => $psleABCD_F_Percentage,
                'psleDEU_M_Percentage' => $psleDEU_M_Percentage,
                'psleDEU_F_Percentage' => $psleDEU_F_Percentage,
        
                'psleAB_T_Percentage' => $psleAB_T_percentage,
                'psleABC_T_Percentage' => $psleABC_T_percentage,
                'psleABCD_T_Percentage' => $psleABCD_T_percentage,
                'psleDEU_T_Percentage' => $psleDEU_T_percentage,
        
                'subjectTotals' => $subjectTotals,
            ];
        
            if (request()->has('export')) {
                return Excel::download(
                    new \App\Exports\CAAnalysisExport($data),
                    "Exam_Analysis_{$klass->name}_" . date('Y-m-d') . ".xlsx"
                );
            }
        
            return view('assessment.shared.exam-class-analysis', $data);
    }


    private function calculateGradeDistribution($test, $students, $subjectName, $teacher){
        $gradeDistribution = [
            'subject' => $subjectName,
            'teacher' => $teacher ? $teacher->full_name : 'N/A',
            'grades' => [
                'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                'U' => ['M' => 0, 'F' => 0, 'T' => 0],
            ],
            'total_enrolled' => ['M' => 0, 'F' => 0, 'T' => 0],
            'no_scores' => ['M' => 0, 'F' => 0, 'T' => 0],
            'percentages' => [
                'AB' => ['M' => 0, 'F' => 0, 'T' => 0],
                'ABC' => ['M' => 0, 'F' => 0, 'T' => 0],
                'DEU' => ['M' => 0, 'F' => 0, 'T' => 0],
            ]
        ];
        
        foreach ($students as $student) {
            $gender = $student->gender;
            $genderKey = ($gender == 'M') ? 'M' : 'F';
            
            $gradeDistribution['total_enrolled'][$genderKey]++;
            $gradeDistribution['total_enrolled']['T']++;
            
            $studentTest = StudentTest::where('student_id', $student->id)
                ->where('test_id', $test->id)
                ->first();
            
            if ($studentTest && !empty($studentTest->grade)) {
                $grade = $studentTest->grade;
                
                if (isset($gradeDistribution['grades'][$grade])) {
                    $gradeDistribution['grades'][$grade][$genderKey]++;
                    $gradeDistribution['grades'][$grade]['T']++;
                }
            } else {
                $gradeDistribution['no_scores'][$genderKey]++;
                $gradeDistribution['no_scores']['T']++;
            }
        }
        
        foreach (['M', 'F', 'T'] as $gender) {
            $totalWithScores = $gradeDistribution['total_enrolled'][$gender] - $gradeDistribution['no_scores'][$gender];
            
            if ($totalWithScores > 0) {
                $gradeDistribution['percentages']['AB'][$gender] = round(
                    (($gradeDistribution['grades']['A'][$gender] + $gradeDistribution['grades']['B'][$gender]) / $totalWithScores) * 100,
                    1
                );
                
                $gradeDistribution['percentages']['ABC'][$gender] = round(
                    (($gradeDistribution['grades']['A'][$gender] + $gradeDistribution['grades']['B'][$gender] + $gradeDistribution['grades']['C'][$gender]) / $totalWithScores) * 100,
                    1
                );

                $gradeDistribution['percentages']['DEU'][$gender] = round(
                    (($gradeDistribution['grades']['D'][$gender] + $gradeDistribution['grades']['E'][$gender] + $gradeDistribution['grades']['U'][$gender]) / $totalWithScores) * 100,
                    1
                );
            }
        }
        return $gradeDistribution;
    }

    private function prepareChartData($subjectsData){
        $subjects = [];
        $series = [
            ['name' => 'AB% Male', 'type' => 'line', 'data' => []],
            ['name' => 'AB% Female', 'type' => 'line', 'data' => []],
            ['name' => 'AB% Total', 'type' => 'line', 'data' => []],
            ['name' => 'ABC% Total', 'type' => 'bar', 'data' => []],
            ['name' => 'DEU% Total', 'type' => 'bar', 'data' => []]
        ];
        
        foreach ($subjectsData as $data) {
            $subjects[] = $data['subject'];
            
            $series[0]['data'][] = $data['percentages']['AB']['M'];
            $series[1]['data'][] = $data['percentages']['AB']['F'];
            $series[2]['data'][] = $data['percentages']['AB']['T'];
            
            $series[3]['data'][] = $data['percentages']['ABC']['T'];
            $series[4]['data'][] = $data['percentages']['DEU']['T'];
        }
        
        return [
            'subjects' => $subjects,
            'series' => $series
        ];
    }


    protected function calculateTeacherPerformanceDataWithNS($teacher, $class, $subjectName, $tests, $studentIds, $gradeSubjectId) {
        $gradeStructure = [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
            'NS' => ['M' => 0, 'F' => 0],
        ];
        
        $totalMale = 0;
        $totalFemale = 0;
        $totalEnrolledMale = 0;
        $totalEnrolledFemale = 0;
        
        // Get all enrolled students for this subject
        $enrolledStudents = Student::whereIn('id', $studentIds)->get();
        
        foreach ($enrolledStudents as $student) {
            $isMale = in_array(strtolower($student->gender), ['male', 'm']);
            
            if ($isMale) {
                $totalEnrolledMale++;
            } else {
                $totalEnrolledFemale++;
            }
            
            // Check if student has test results
            $hasScore = false;
            foreach ($tests as $test) {
                $result = StudentTest::where('test_id', $test->id)
                    ->where('student_id', $student->id)
                    ->first();
                    
                if ($result && !empty($result->grade)) {
                    $grade = $result->grade;
                    
                    // Skip if grade is not in our structure
                    if (!isset($gradeStructure[$grade])) {
                        continue;
                    }
                    
                    if ($isMale) {
                        $totalMale++;
                        $gradeStructure[$grade]['M']++;
                    } else {
                        $totalFemale++;
                        $gradeStructure[$grade]['F']++;
                    }
                    $hasScore = true;
                    break; // Only count once per student
                }
            }
            
            // If no score found, add to NS
            if (!$hasScore) {
                if ($isMale) {
                    $gradeStructure['NS']['M']++;
                } else {
                    $gradeStructure['NS']['F']++;
                }
            }
        }
        
        if ($totalEnrolledMale + $totalEnrolledFemale == 0) {
            return null; // No data to report
        }
        
        // Calculate percentages based on students with scores only
        $percentRanges = [
            'AB%'   => ['A','B'],
            'ABC%'  => ['A','B','C'],
            'ABCD%' => ['A','B','C','D'],
            'DEU%'  => ['D','E','U'],
        ];
        
        $percentages = [];
        foreach ($percentRanges as $col => $grades) {
            $mSum = array_sum(array_map(fn($g) => $gradeStructure[$g]['M'], $grades));
            $fSum = array_sum(array_map(fn($g) => $gradeStructure[$g]['F'], $grades));
            
            $percentages[$col] = [
                'M' => $totalMale ? round($mSum / $totalMale * 100, 2) : 0,
                'F' => $totalFemale ? round($fSum / $totalFemale * 100, 2) : 0
            ];
        }
        
        // Handle teacher name and class name
        $teacherName = '';
        if (is_object($teacher)) {
            if (isset($teacher->name)) {
                $teacherName = $teacher->name;
            } elseif (isset($teacher->full_name)) {
                $teacherName = $teacher->full_name;
            } elseif (method_exists($teacher, 'getFullNameAttribute')) {
                $teacherName = $teacher->getFullNameAttribute();
            } else {
                $teacherName = (string)$teacher;
            }
        }
        
        $className = '';
        if (is_object($class)) {
            $className = $class->name ?? '';
        } else {
            $className = (string)$class;
        }
        
        return [
            'teacher_name' => $teacherName,
            'class_name' => $className,
            'subject_name' => $subjectName,
            'totalMale' => $totalMale,
            'totalFemale' => $totalFemale,
            'totalEnrolled' => ['M' => $totalEnrolledMale, 'F' => $totalEnrolledFemale],
            'grades' => $gradeStructure,
            'AB%' => $percentages['AB%'],
            'ABC%' => $percentages['ABC%'],
            'ABCD%' => $percentages['ABCD%'],
            'DEU%' => $percentages['DEU%']
        ];
    }

    /**
     * Generate CA house performance report for junior school
     */
    public function generateCAJuniorHousePerformanceReport(int $sequence){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $houses = House::with([
            'students' => function ($q) use ($selectedTermId, $sequence) {
                $q->where('student_house.term_id', $selectedTermId)
                ->with(['tests' => function ($q) use ($selectedTermId, $sequence) {
                    $q->where('tests.term_id', $selectedTermId)
                    ->where('sequence', $sequence)
                    ->where('type', 'CA')
                    ->whereNull('student_tests.deleted_at');
                }]);
            }
        ])->where('term_id', $selectedTermId)
        ->get();

        $test = Test::where('term_id', $selectedTermId)->where('type', 'CA')->where('sequence', $sequence)->first();
        $term = Term::findOrFail($selectedTermId);
        $overall = [
            'grades' => [
                'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],'C'=>['M'=>0,'F'=>0],
                'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],'U'=>['M'=>0,'F'=>0],
                'total'=>['M'=>0,'F'=>0],
            ],
            'AB%'=>['M'=>0,'F'=>0],'ABC%'=>['M'=>0,'F'=>0],
            'ABCD%'=>['M'=>0,'F'=>0],'DEU%'=>['M'=>0,'F'=>0],
            'totalMale'=>0,'totalFemale'=>0,
        ];

        $housePerformance = [];
        foreach ($houses as $house) {
            $houseName = $house->name;
            $gradesCount = [
                'A'=>['M'=>0,'F'=>0],'B'=>['M'=>0,'F'=>0],'C'=>['M'=>0,'F'=>0],
                'D'=>['M'=>0,'F'=>0],'E'=>['M'=>0,'F'=>0],'U'=>['M'=>0,'F'=>0],
                'total'=>['M'=>0,'F'=>0],
            ];

            foreach ($house->students as $student) {
                $genderKey = $student->gender === 'M' ? 'M' : 'F';

                foreach ($student->tests as $test) {
                    $grade = $test->pivot->grade ?? 'U';

                    if (isset($gradesCount[$grade][$genderKey])) {
                        $gradesCount[$grade][$genderKey]++;
                        $gradesCount['total'][$genderKey]++;
                    }
                }
            }

            $totalMale   = $gradesCount['total']['M'];
            $totalFemale = $gradesCount['total']['F'];
            $safePct = fn($num,$den)=> $den ? round($num/$den*100,2) : 0;

            $AB_M   = $gradesCount['A']['M']+$gradesCount['B']['M'];
            $AB_F   = $gradesCount['A']['F']+$gradesCount['B']['F'];
            $ABC_M  = $AB_M + $gradesCount['C']['M'];
            $ABC_F  = $AB_F + $gradesCount['C']['F'];
            $ABCD_M = $ABC_M+ $gradesCount['D']['M'];
            $ABCD_F = $ABC_F+ $gradesCount['D']['F'];
            $DEU_M  = $gradesCount['D']['M']+$gradesCount['E']['M']+$gradesCount['U']['M'];
            $DEU_F  = $gradesCount['D']['F']+$gradesCount['E']['F']+$gradesCount['U']['F'];

            $housePerformance[$houseName] = [
                'grades'      => $gradesCount,
                'AB%'         => ['M'=> $safePct($AB_M  ,$totalMale)  ,'F'=> $safePct($AB_F  ,$totalFemale)],
                'ABC%'        => ['M'=> $safePct($ABC_M ,$totalMale)  ,'F'=> $safePct($ABC_F ,$totalFemale)],
                'ABCD%'       => ['M'=> $safePct($ABCD_M,$totalMale)  ,'F'=> $safePct($ABCD_F,$totalFemale)],
                'DEU%'        => ['M'=> $safePct($DEU_M ,$totalMale)  ,'F'=> $safePct($DEU_F ,$totalFemale)],
                'totalMale'   => $totalMale,
                'totalFemale' => $totalFemale,
            ];

            foreach (['A','B','C','D','E','U'] as $g) {
                $overall['grades'][$g]['M'] += $gradesCount[$g]['M'];
                $overall['grades'][$g]['F'] += $gradesCount[$g]['F'];
            }
            $overall['grades']['total']['M']   += $totalMale;
            $overall['grades']['total']['F']   += $totalFemale;
            $overall['totalMale']              += $totalMale;
            $overall['totalFemale']            += $totalFemale;
        }

        $totM = $overall['totalMale'];
        $totF = $overall['totalFemale'];
        $sum  = fn($g)=> $overall['grades'][$g]['M'] + $overall['grades'][$g]['F'];

        $pct  = fn($num,$den)=> $den ? round($num/$den*100,2) : 0;

        $overall['AB%']['M']   = $pct($overall['grades']['A']['M']+$overall['grades']['B']['M'], $totM);
        $overall['AB%']['F']   = $pct($overall['grades']['A']['F']+$overall['grades']['B']['F'], $totF);

        $overall['ABC%']['M']  = $pct($overall['grades']['A']['M']+$overall['grades']['B']['M']+$overall['grades']['C']['M'], $totM);
        $overall['ABC%']['F']  = $pct($overall['grades']['A']['F']+$overall['grades']['B']['F']+$overall['grades']['C']['F'], $totF);

        $overall['ABCD%']['M'] = $pct($overall['grades']['A']['M']+$overall['grades']['B']['M']+$overall['grades']['C']['M']+$overall['grades']['D']['M'], $totM);
        $overall['ABCD%']['F'] = $pct($overall['grades']['A']['F']+$overall['grades']['B']['F']+$overall['grades']['C']['F']+$overall['grades']['D']['F'], $totF);

        $overall['DEU%']['M']  = $pct($overall['grades']['D']['M']+$overall['grades']['E']['M']+$overall['grades']['U']['M'], $totM);
        $overall['DEU%']['F']  = $pct($overall['grades']['D']['F']+$overall['grades']['E']['F']+$overall['grades']['U']['F'], $totF);

        $viewData = [
            'housePerformance' => $housePerformance,
            'overallTotals'    => $overall, 
            'school_data'      => SchoolSetup::first(),
            'type'             => 'CA',
            'test'             => $test,
            'term'             => $term,
        ];

        if (request()->query('export') === 'excel') {
            return Excel::download(
                new JuniorSubjectsHouseStatisticsSimpleExport($viewData),
                'subjects-by-house-ca-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        return view('houses.subjects-houses-statistics-junior', $viewData);
    }

    /**
     * Generate overall CA teacher performance report
     */
    public function generateOverallCATeacherPerformanceReport($classId, $type, $sequence) {
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm = Term::findOrFail($selectedTermId);
        $school_data = SchoolSetup::first();
        $year = $currentTerm->year;
        
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;
        
        $grade = Grade::with(['klasses' => function($query) use ($selectedTermId, $year) {
            $query->where('term_id', $selectedTermId);
        }])->findOrFail($gradeId);

        $test = Test::where('term_id',$selectedTermId)->where('type',$type)->where('sequence',$sequence)->first();
        
        $teacherPerformanceBySubject = [];
        $subjectList = [];
        
        foreach ($grade->klasses as $class) {
            $students = $class->currentStudents($selectedTermId, $year)->get();
            $studentIds = $students->pluck('id')->toArray();
            
            $klassSubjects = KlassSubject::where('klass_id', $class->id)
                ->where('term_id', $selectedTermId)
                ->where('grade_id', $gradeId)
                ->with(['teacher', 'subject.subject', 'klass'])
                ->get();
            
            foreach ($klassSubjects as $klassSubject) {
                $teacher = $klassSubject->teacher;
                $gradeSubject = $klassSubject->subject;
                
                $subjectName = $gradeSubject->subject->name;
                if (!in_array($subjectName, $subjectList)) {
                    $subjectList[] = $subjectName;
                }
                
                $tests = Test::where('grade_subject_id', $gradeSubject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('sequence', $sequence)
                    ->where('type', $type)
                    ->get();
                
                $performanceData = $this->calculateTeacherPerformanceDataWithNS($teacher, $class, $subjectName, $tests, $studentIds, $gradeSubject->id);
                
                if ($performanceData) {
                    if (!isset($teacherPerformanceBySubject[$subjectName])) {
                        $teacherPerformanceBySubject[$subjectName] = [];
                    }
                    $teacherPerformanceBySubject[$subjectName][] = $performanceData;
                }
            }
        }
        
        $optionalSubjects = OptionalSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->with(['teacher', 'gradeSubject.subject'])
            ->get();
        
        foreach ($optionalSubjects as $optionalSubject) {
            $optionalStudents = $optionalSubject->students()
                ->wherePivot('term_id', $selectedTermId)
                ->get();
            
            if ($optionalStudents->isEmpty()) {
                continue;
            }
            
            $studentIds = $optionalStudents->pluck('id')->toArray();
            $teacher = $optionalSubject->teacher;
            $gradeSubject = $optionalSubject->gradeSubject;
            
            if (!$gradeSubject || !$gradeSubject->subject) {
                continue;
            }
            
            $subjectName = $gradeSubject->subject->name;
            if (!in_array($subjectName, $subjectList)) {
                $subjectList[] = $subjectName;
            }
            
            $displayName = $optionalSubject->name . ' (' . $subjectName . ')';
            
            $tests = Test::where('grade_subject_id', $gradeSubject->id)
                ->where('term_id', $selectedTermId)
                ->where('sequence', $sequence)
                ->where('type', $type)
                ->get();
            
            $performanceData = $this->calculateTeacherPerformanceDataWithNS($teacher, $optionalSubject, $displayName, $tests, $studentIds, $gradeSubject->id);
            
            if ($performanceData) {
                if (!isset($teacherPerformanceBySubject[$subjectName])) {
                    $teacherPerformanceBySubject[$subjectName] = [];
                }
                $teacherPerformanceBySubject[$subjectName][] = $performanceData;
            }
        }
        
        foreach ($teacherPerformanceBySubject as &$performances) {
            usort($performances, function($a, $b) {
                $aTotal = ($a['totalMale'] * $a['ABC%']['M'] + $a['totalFemale'] * $a['ABC%']['F']) / 
                          max(1, $a['totalMale'] + $a['totalFemale']);
                $bTotal = ($b['totalMale'] * $b['ABC%']['M'] + $b['totalFemale'] * $b['ABC%']['F']) / 
                          max(1, $b['totalMale'] + $b['totalFemale']);
                return $bTotal <=> $aTotal;
            });
        }
        
        $orderedSubjectNames = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->where('active', 1)
            ->with('subject')
            ->orderByRaw('CASE WHEN sequence IS NULL OR sequence = 0 THEN 1 ELSE 0 END')
            ->orderBy('sequence', 'asc')
            ->get()
            ->pluck('subject.name')
            ->filter()
            ->unique()
            ->toArray();

        $subjectSet = array_flip($subjectList);
        $finalSubjectList = [];
        foreach ($orderedSubjectNames as $name) {
            if (isset($subjectSet[$name])) {
                $finalSubjectList[] = $name;
            }
        }

        $leftovers = array_values(array_diff($subjectList, $finalSubjectList));
        sort($leftovers);
        $finalSubjectList = array_merge($finalSubjectList, $leftovers);

        $teacherPerformance = [];
        foreach ($finalSubjectList as $subject) {
            if (isset($teacherPerformanceBySubject[$subject]) && !empty($teacherPerformanceBySubject[$subject])) {
                $teacherPerformance[$subject] = $teacherPerformanceBySubject[$subject];
            }
        }
    
        $teacherTotals = [];
        $isGrouped = true;
    
        $subjectsToIterate = $isGrouped ? $finalSubjectList : ['__overall__'];
    
        foreach ($subjectsToIterate as $subjKey) {
            $rows = $subjKey === '__overall__'
                ? $teacherPerformance
                : ($teacherPerformance[$subjKey] ?? []);
        
            $tot = [
                'grades' => [
                    'A'=>['M'=>0,'F'=>0], 'B'=>['M'=>0,'F'=>0],
                    'C'=>['M'=>0,'F'=>0], 'D'=>['M'=>0,'F'=>0],
                    'E'=>['M'=>0,'F'=>0], 'U'=>['M'=>0,'F'=>0],
                    'NS'=>['M'=>0,'F'=>0],
                ],
                'AB%'   => ['M'=>0,'F'=>0,'T'=>0],
                'ABC%'  => ['M'=>0,'F'=>0,'T'=>0],
                'ABCD%' => ['M'=>0,'F'=>0,'T'=>0],
                'DEU%'  => ['M'=>0,'F'=>0,'T'=>0],
                'totalMale'   => 0,
                'totalFemale' => 0,
                'totalEnrolled' => ['M'=>0,'F'=>0],
            ];
        
            foreach ($rows as $r) {
                foreach (['A','B','C','D','E','U','NS'] as $g) {
                    $tot['grades'][$g]['M'] += $r['grades'][$g]['M'];
                    $tot['grades'][$g]['F'] += $r['grades'][$g]['F'];
                }
                $tot['totalMale']   += $r['totalMale'];
                $tot['totalFemale'] += $r['totalFemale'];
                $tot['totalEnrolled']['M'] += $r['totalEnrolled']['M'];
                $tot['totalEnrolled']['F'] += $r['totalEnrolled']['F'];
            }
        
            $mTotal = $tot['totalMale'];
            $fTotal = $tot['totalFemale'];
            $tTotal = $mTotal + $fTotal;
        
            $percentRanges = [
                'AB%'   => ['A','B'],
                'ABC%'  => ['A','B','C'],
                'ABCD%' => ['A','B','C','D'],
                'DEU%'  => ['D','E','U'],
            ];
        
            foreach ($percentRanges as $col => $letters) {
                $sumM = array_sum(array_map(fn($g) => $tot['grades'][$g]['M'], $letters));
                $sumF = array_sum(array_map(fn($g) => $tot['grades'][$g]['F'], $letters));
                $sumT = $sumM + $sumF;
        
                $tot[$col]['M'] = $mTotal ? round($sumM / $mTotal * 100, 2) : 0;
                $tot[$col]['F'] = $fTotal ? round($sumF / $fTotal * 100, 2) : 0;
                $tot[$col]['T'] = $tTotal ? round($sumT / $tTotal * 100, 2) : 0;
            }
        
            $teacherTotals[$subjKey] = $tot;
        }
        
        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\TeacherPerformanceExport($teacherPerformance, true), 
                "Teacher_Performance_CA_{$klass->name}_" . date('Y-m-d') . ".xlsx"
            );
        }
        
        return view('assessment.junior.subjects-ca-teachers-junior', [
            'teacherPerformance' => $teacherPerformance,
            'school_data' => $school_data,
            'currentTerm' => $currentTerm,
            'test' => $test,
            'subjectList' => $finalSubjectList,
            'teacherTotals'      => $teacherTotals,
            'grade' => $grade,
            'isGrouped' => true
        ]);
    }

    /**
     * Generate overall teacher performance report by grade
     */
    public function generateOverallTeacherPerformanceByGrade($classId, $type, $sequence) {
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm = Term::findOrFail($selectedTermId);
        $school_data = SchoolSetup::first();
        $year = $currentTerm->year;
    
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;
        
        $grade = Grade::findOrFail($gradeId);
        $teachersData = [];
        $klasses = Klass::where('grade_id', $gradeId)->where('term_id', $selectedTermId)->get();
        $test = Test::where('term_id',$selectedTermId)->where('type',$type)->where('sequence',$sequence)->first();
        
        foreach ($klasses as $klass) {
            $students = $klass->currentStudents($selectedTermId, $year)->get();
            $studentIds = $students->pluck('id')->toArray();
            
            $klassSubjects = KlassSubject::where('klass_id', $klass->id)
                ->where('term_id', $selectedTermId)
                ->where('grade_id', $gradeId)
                ->with(['teacher', 'subject.subject', 'klass'])
                ->get();
            
            foreach ($klassSubjects as $klassSubject) {
                $teacherId = $klassSubject->user_id;
                $teacher = $klassSubject->teacher;
                
                if (!isset($teachersData[$teacherId])) {
                    $teachersData[$teacherId] = [
                        'teacher' => $teacher,
                        'assignments' => []
                    ];
                }
                
                $teachersData[$teacherId]['assignments'][] = [
                    'type' => 'klass',
                    'klass_subject' => $klassSubject,
                    'students' => $studentIds
                ];
            }
        }
        
        $optionalSubjects = OptionalSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->with(['teacher', 'gradeSubject.subject'])
            ->get();
        
        foreach ($optionalSubjects as $optionalSubject) {
            $teacherId = $optionalSubject->user_id;
            $teacher = $optionalSubject->teacher;
            
            if (!isset($teachersData[$teacherId])) {
                $teachersData[$teacherId] = [
                    'teacher' => $teacher,
                    'assignments' => []
                ];
            }
            
            $optionalStudents = $optionalSubject->students()
                ->wherePivot('term_id', $selectedTermId)
                ->get();
            
            if (!$optionalStudents->isEmpty()) {
                $teachersData[$teacherId]['assignments'][] = [
                    'type' => 'optional',
                    'optional_subject' => $optionalSubject,
                    'students' => $optionalStudents->pluck('id')->toArray()
                ];
            }
        }
        
        $teacherPerformance = [];
        foreach ($teachersData as $teacherId => $data) {
            $teacher = $data['teacher'];
            $overallPerformance = $this->calculateOverallTeacherPerformance($teacher, $data['assignments'], $selectedTermId, $sequence, $type);
            if ($overallPerformance) {
                $teacherPerformance[] = $overallPerformance;
            }
        }

        usort($teacherPerformance, function($a, $b) {
            $aTotalStudents = $a['totalMale'] + $a['totalFemale'];
            $bTotalStudents = $b['totalMale'] + $b['totalFemale'];

            $aABC = $aTotalStudents > 0 ? 
                (($a['grades']['A']['M'] + $a['grades']['A']['F'] + 
                $a['grades']['B']['M'] + $a['grades']['B']['F'] + 
                $a['grades']['C']['M'] + $a['grades']['C']['F']) / 
                $aTotalStudents * 100) : 0;
                
            $bABC = $bTotalStudents > 0 ? 
                (($b['grades']['A']['M'] + $b['grades']['A']['F'] + 
                $b['grades']['B']['M'] + $b['grades']['B']['F'] + 
                $b['grades']['C']['M'] + $b['grades']['C']['F']) / 
                $bTotalStudents * 100) : 0;
            
            if (abs($aABC - $bABC) >= 0.01) {
                return $bABC <=> $aABC;
            }
            
            $aAB = $aTotalStudents > 0 ? 
                (($a['grades']['A']['M'] + $a['grades']['A']['F'] + 
                $a['grades']['B']['M'] + $a['grades']['B']['F']) / 
                $aTotalStudents * 100) : 0;
                
            $bAB = $bTotalStudents > 0 ? 
                (($b['grades']['A']['M'] + $b['grades']['A']['F'] + 
                $b['grades']['B']['M'] + $b['grades']['B']['F']) / 
                $bTotalStudents * 100) : 0;
            
            return $bAB <=> $aAB;
        });
        
        $totals = [
            'grades' => [
                'A'=>['M'=>0,'F'=>0], 'B'=>['M'=>0,'F'=>0], 'C'=>['M'=>0,'F'=>0],
                'D'=>['M'=>0,'F'=>0], 'E'=>['M'=>0,'F'=>0], 'U'=>['M'=>0,'F'=>0],
            ],
            'totalMale' => 0,
            'totalFemale' => 0,
            'AB%' => ['M'=>0,'F'=>0,'T'=>0],
            'ABC%' => ['M'=>0,'F'=>0,'T'=>0],
            'ABCD%' => ['M'=>0,'F'=>0,'T'=>0],
        ];
        
        foreach ($teacherPerformance as $performance) {
            foreach (['A','B','C','D','E','U'] as $g) {
                $totals['grades'][$g]['M'] += $performance['grades'][$g]['M'];
                $totals['grades'][$g]['F'] += $performance['grades'][$g]['F'];
            }
            $totals['totalMale'] += $performance['totalMale'];
            $totals['totalFemale'] += $performance['totalFemale'];
        }
        
        $mTotal = $totals['totalMale'];
        $fTotal = $totals['totalFemale'];
        $tTotal = $mTotal + $fTotal;
        
        $percentRanges = [
            'AB%' => ['A','B'],
            'ABC%' => ['A','B','C'], 
            'ABCD%' => ['A','B','C','D'],
        ];
        
        foreach ($percentRanges as $col => $letters) {
            $sumM = array_sum(array_map(fn($g) => $totals['grades'][$g]['M'], $letters));
            $sumF = array_sum(array_map(fn($g) => $totals['grades'][$g]['F'], $letters));
            $sumT = $sumM + $sumF;
            
            $totals[$col]['M'] = $mTotal ? round($sumM / $mTotal * 100, 2) : 0;
            $totals[$col]['F'] = $fTotal ? round($sumF / $fTotal * 100, 2) : 0;
            $totals[$col]['T'] = $tTotal ? round($sumT / $tTotal * 100, 2) : 0;
        }
        
        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\OverallTeacherPerformanceExport($teacherPerformance, $totals), 
                "Overall_Teacher_Performance_{$grade->name}_" . date('Y-m-d') . ".xlsx"
            );
        }
        
        return view('assessment.junior.overall-teacher-performance-junior', [
            'teacherPerformance' => $teacherPerformance,
            'totals' => $totals,
            'school_data' => $school_data,
            'grade' => $grade,
            'test' => $test
        ]);
    }

    protected function calculateOverallTeacherPerformance($teacher, $assignments, $selectedTermId, $sequence, $type) {
        $gradeStructure = [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
        ];
        
        $totalMale = 0;
        $totalFemale = 0;
        $processedStudents = [];
        
        foreach ($assignments as $assignment) {
            $studentIds = $assignment['students'];
            
            if ($assignment['type'] === 'klass') {
                $gradeSubjectId = $assignment['klass_subject']->grade_subject_id;
            } else {
                $gradeSubjectId = $assignment['optional_subject']->grade_subject_id;
            }
            
            $tests = Test::where('grade_subject_id', $gradeSubjectId)
                ->where('term_id', $selectedTermId)
                ->where('sequence', $sequence)
                ->where('type', $type)
                ->get();
            
            foreach ($tests as $test) {
                $results = StudentTest::where('test_id', $test->id)
                    ->whereIn('student_id', $studentIds)
                    ->with('student')
                    ->get();
                    
                foreach ($results as $result) {
                    if (!$result->student) continue;
                    
                    $studentKey = $result->student_id . '_' . $test->id;
                    if (isset($processedStudents[$studentKey])) continue; // Avoid duplicates
                    
                    $processedStudents[$studentKey] = true;
                    
                    $gender = $result->student->gender;
                    $grade = $result->grade;
                    
                    if (empty($grade) || !isset($gradeStructure[$grade])) continue;
                    
                    $isMale = in_array(strtolower($gender), ['male', 'm']);
                    if ($isMale) {
                        $totalMale++;
                        $gradeStructure[$grade]['M']++;
                    } else {
                        $totalFemale++;
                        $gradeStructure[$grade]['F']++;
                    }
                }
            }
        }
        
        if ($totalMale + $totalFemale == 0) {
            return null;
        }
        
        $percentRanges = [
            'AB%' => ['A','B'],
            'ABC%' => ['A','B','C'],
            'ABCD%' => ['A','B','C','D'],
        ];
        
        $percentages = [];
        foreach ($percentRanges as $col => $grades) {
            $mSum = array_sum(array_map(fn($g) => $gradeStructure[$g]['M'], $grades));
            $fSum = array_sum(array_map(fn($g) => $gradeStructure[$g]['F'], $grades));
            
            $percentages[$col] = [
                'M' => $totalMale ? round($mSum / $totalMale * 100, 2) : 0,
                'F' => $totalFemale ? round($fSum / $totalFemale * 100, 2) : 0
            ];
        }
        
        $teacherName = '';
        if (is_object($teacher)) {
            $teacherName = $teacher->full_name;
        }
        
        return [
            'teacher_name' => $teacherName,
            'totalMale' => $totalMale,
            'totalFemale' => $totalFemale,
            'grades' => $gradeStructure,
            'AB%' => $percentages['AB%'],
            'ABC%' => $percentages['ABC%'],
            'ABCD%' => $percentages['ABCD%'],
        ];
    }

    /**
     * Generate class analysis report
     */
    public function generateClassAnalysisReport($classId, $sequence, $type) {
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm = Term::findOrFail($selectedTermId);
        $year = $currentTerm->year;
        $school_data = SchoolSetup::first();
        
        $klass = Klass::findOrFail($classId);
        $grade = Grade::findOrFail($klass->grade_id);
    
        $classes = Klass::with(['teacher'])
            ->where('term_id', $selectedTermId)
            ->where('year', $year)
            ->where('grade_id', $klass->grade_id)
            ->orderBy('name')
            ->get();
        
        $classAnalysis = [];
        $totalGrades = [
            'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'U' => 0, 'X' => 0
        ];
        $totalStudents = 0;
        
        foreach ($classes as $class) {
            $students = $class->currentStudents($selectedTermId, $year)->get();
            $studentIds = $students->pluck('id')->toArray();
            $maleCount = $students->where('gender', 'M')->count();
            $femaleCount = $students->where('gender', 'F')->count();
            $totalInClass = $maleCount + $femaleCount;
            $totalStudents += $totalInClass;
            
            $houseInfo = [];
            $houseStudentCount = [];
            
            $studentHouses = DB::table('student_house')
                ->join('houses', 'student_house.house_id', '=', 'houses.id')
                ->whereIn('student_house.student_id', $studentIds)
                ->where('student_house.term_id', $selectedTermId)
                ->select('houses.name as house_name', 'student_house.student_id')
                ->get();
                
            foreach ($studentHouses as $sh) {
                if (!isset($houseStudentCount[$sh->house_name])) {
                    $houseStudentCount[$sh->house_name] = 0;
                }
                $houseStudentCount[$sh->house_name]++;
            }
            
            $mainHouse = !empty($houseStudentCount) ? array_search(max($houseStudentCount), $houseStudentCount) : 'Unassigned';
            
            if ($mainHouse === 'Unassigned' && $students->count() > 0) {
                $studentsWithHouses = Student::with(['houses' => function($query) use ($selectedTermId) {
                    $query->wherePivot('term_id', $selectedTermId);
                }])->whereIn('id', $studentIds)->get();
                
                foreach ($studentsWithHouses as $student) {
                    $house = $student->houses->first();
                    if ($house) {
                        $houseName = $house->name;
                        if (!isset($houseInfo[$houseName])) {
                            $houseInfo[$houseName] = 0;
                        }
                        $houseInfo[$houseName]++;
                    }
                }
                
                $mainHouse = !empty($houseInfo) ? array_search(max($houseInfo), $houseInfo) : 'Unassigned';
            }
            
            $grades = [
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0], 
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'U' => ['M' => 0, 'F' => 0],
                'X' => ['M' => 0, 'F' => 0]
            ];
            
            $tests = Test::where('term_id', $selectedTermId)
                ->where('grade_id', $klass->grade_id)
                ->where('sequence', $sequence)
                ->where('type', $type)
                ->pluck('id');
            
            $studentsWithTests = [];
            
            $studentTests = StudentTest::whereIn('student_id', $studentIds)
                ->whereIn('test_id', $tests)
                ->with(['student', 'test.subject.subject'])
                ->get();
            
            foreach ($studentTests as $studentTest) {
                $student = $studentTest->student;
                $gender = $student->gender ?? 'M';
                $grade = $studentTest->grade ?? 'X';
                
                $studentsWithTests[$student->id] = true;
                
                if (isset($grades[$grade][$gender])) {
                    $grades[$grade][$gender]++;
                }
                
                if (isset($totalGrades[$grade])) {
                    $totalGrades[$grade]++;
                }
            }
            
            foreach ($students as $student) {
                if (!isset($studentsWithTests[$student->id])) {
                    $gender = $student->gender;
                    $grades['X'][$gender]++;
                    $totalGrades['X']++;
                }
            }
    
            $abCount = $grades['A']['M'] + $grades['A']['F'] + $grades['B']['M'] + $grades['B']['F'];
            $abcCount = $abCount + $grades['C']['M'] + $grades['C']['F'];
            $adCount = $abcCount + $grades['D']['M'] + $grades['D']['F'];
            $euCount = $grades['E']['M'] + $grades['E']['F'] + $grades['U']['M'] + $grades['U']['F'];
            
            $totalGradeCount = array_sum(array_map(function($g) {
                return $g['M'] + $g['F'];
            }, $grades));
            
            $abPercentage = $totalGradeCount > 0 ? round(($abCount / $totalGradeCount) * 100, 1) : 0;
            $abcPercentage = $totalGradeCount > 0 ? round(($abcCount / $totalGradeCount) * 100, 1) : 0;
            $adPercentage = $totalGradeCount > 0 ? round(($adCount / $totalGradeCount) * 100, 1) : 0;
            $euPercentage = $totalGradeCount > 0 ? round(($euCount / $totalGradeCount) * 100, 1) : 0;
            
            $classAnalysis[] = [
                'teacher' => $class->teacher->lastname ?? 'Unassigned',
                'class' => $class->name,
                'house' => $mainHouse,
                'house_distribution' => !empty($houseStudentCount) ? $houseStudentCount : $houseInfo,
                'male_count' => $maleCount,
                'female_count' => $femaleCount,
                'total' => $totalInClass,
                'grades' => $grades,
                'a_b_percentage' => $abPercentage,
                'a_c_percentage' => $abcPercentage,
                'a_d_percentage' => $adPercentage,
                'e_u_percentage' => $euPercentage
            ];
        }
        
        $totalGradeCount = array_sum($totalGrades);
        $overallABCount = $totalGrades['A'] + $totalGrades['B'];
        $overallABCCount = $overallABCount + $totalGrades['C'];
        $overallADCount = $overallABCCount + $totalGrades['D'];
        $overallEUCount = $totalGrades['E'] + $totalGrades['U'];
        
        $overallABPercentage = $totalGradeCount > 0 ? round(($overallABCount / $totalGradeCount) * 100, 1) : 0;
        $overallABCPercentage = $totalGradeCount > 0 ? round(($overallABCCount / $totalGradeCount) * 100, 1) : 0;
        $overallADPercentage = $totalGradeCount > 0 ? round(($overallADCount / $totalGradeCount) * 100, 1) : 0;
        $overallEUPercentage = $totalGradeCount > 0 ? round(($overallEUCount / $totalGradeCount) * 100, 1) : 0;
        
        $data = [
            'classes' => $classAnalysis,
            'school_data' => $school_data,
            'currentTerm' => $currentTerm,
            'grade' => $grade,
            'sequence' => $sequence,
            'type' => $type,
            'totalStudents' => $totalStudents,
            'totalGrades' => $totalGrades,
            'overallABPercentage' => $overallABPercentage,
            'overallABCPercentage' => $overallABCPercentage,
            'overallADPercentage' => $overallADPercentage,
            'overallEUPercentage' => $overallEUPercentage
        ];
        
        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\ClassAnalysisExport($data),
                "Class Analysis Report - {$type} " . date('Y-m-d') . ".xlsx"
            );
        }
        return view('assessment.junior.grade-class-analysis-junior', $data);
    }

    /**
     * Generate CA analysis by grade
     */
    public function generateCAByGradeAnalysis($classId, $sequenceId){
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade->id;
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
    
        $students = Student::whereHas('classes', function ($query) use ($gradeId) {
            $query->whereHas('grade', function ($query) use ($gradeId) {
                $query->where('id', $gradeId);
            });
        })->get();

        $test = Test::where('term_id',$selectedTermId)->where('type','CA')->where('sequence',$sequenceId)->first();
        $school_setup = SchoolSetup::first();
        $allGradeSubjects = GradeSubject::where('grade_id', $klass->grade_id)
            ->where('term_id', $selectedTermId)
            ->with('subject')
            ->get();
    
        $allSubjects = $allGradeSubjects->pluck('subject.name')->unique()->sort()->values()->toArray();
        $gradeCounts = [
            'M' => ['M' => 0, 'F' => 0],
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
            'X' => ['M' => 0, 'F' => 0],
        ];
    
        $subjectGradeCounts = [];
        foreach ($allSubjects as $subject) {
            $subjectGradeCounts[$subject] = [
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'U' => ['M' => 0, 'F' => 0],
                'X' => ['M' => 0, 'F' => 0],
                'total' => ['M' => 0, 'F' => 0],
                'enrolled' => ['M' => 0, 'F' => 0],
                'no_scores' => ['M' => 0, 'F' => 0],
            ];
        }
    
        $psleGradeCounts = [
            'Merit' => ['M' => 0, 'F' => 0],
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
        ];
    
        $reportCardsData = [];
        foreach ($students as $student) {
            $isForeigner = $student->nationality !== 'Motswana';
            $hasParticipated = false;
            $subjectScores = [];
    
            $psleGrade = optional($student->psle)->overall_grade;
            if ($psleGrade && isset($psleGradeCounts[$psleGrade])) {
                $gender = $student->gender === 'M' ? 'M' : 'F';
                $psleGradeCounts[$psleGrade][$gender]++;
            }
    
            foreach ($allGradeSubjects as $gradeSubject) {
                $subjectName = optional($gradeSubject->subject)->name ?? 'Unknown Subject';
                $isEnrolled = false;
                $currentClass = $student->currentClass();
                
                if ($currentClass) {
                    $isInClassCurriculum = $currentClass->subjectClasses()
                        ->whereHas('subject', function($q) use ($gradeSubject) {
                            $q->where('grade_subject_id', $gradeSubject->id);
                        })->exists();
                        
                    $isInOptionalSubjects = $student->optionalSubjects()
                        ->where('grade_subject_id', $gradeSubject->id)
                        ->exists();
                        
                    $isEnrolled = $isInClassCurriculum || $isInOptionalSubjects;
                }
    
                $subjectData = $this->calculateSubjectScoresAnalysis(
                    $student,
                    $gradeSubject,
                    $selectedTermId,
                    $klass->grade_id,
                    'CA',        
                    $sequenceId  
                );
    
                if (!is_null($subjectData['percentage'])) {
                    $hasParticipated = true;
                }
    
                $subGrade = $subjectData['grade'] ?? 'X';
                $subPercentage = $subjectData['percentage'] ?? null;
                $subPoints = $subjectData['points'] ?? null;
    
                $subjectScores[$subjectName] = [
                    'percentage' => $subPercentage,
                    'grade' => $subGrade,
                    'enrolled' => $isEnrolled
                ];
            }
    
            foreach ($allSubjects as $subject) {
                if (!isset($subjectScores[$subject])) {
                    $subjectScores[$subject] = [
                        'percentage' => null,
                        'grade' => 'X',
                        'enrolled' => false
                    ];
                }
            }
    
            list($mandatoryPoints, $bestOptionalPoints, $bestCorePoints) = $this->calculatePoints(
                $student,
                $allGradeSubjects,
                $selectedTermId,
                $isForeigner,
                'CA',        
                $sequenceId  
            );

            $totalPoints = $mandatoryPoints + $bestOptionalPoints + $bestCorePoints;
            $currentClass = $student->currentClass();
            $className = $currentClass ? $currentClass->name : '';
            $overallGrade = $hasParticipated ? AssessmentHelper::determineGrade($totalPoints, $currentClass) : 'X';
    
            if ($overallGrade === 'Merit') {
                $overallGrade = 'M';
            }
    
            $reportCardsData[] = [
                'student' => $student,
                'scores' => $subjectScores,
                'totalPoints' => $hasParticipated ? $totalPoints : 'X',
                'grade' => $overallGrade,
                'class_name' => $className,
            ];
        }
    
        usort($reportCardsData, function($a, $b) {
            $aPts = is_numeric($a['totalPoints']) ? $a['totalPoints'] : -1;
            $bPts = is_numeric($b['totalPoints']) ? $b['totalPoints'] : -1;
            if ($aPts !== $bPts) {
                return $bPts <=> $aPts;
            }
    
            $sumA = array_sum(array_map(fn($s) => is_numeric($s['percentage']) ? $s['percentage'] : 0, $a['scores']));
            $sumB = array_sum(array_map(fn($s) => is_numeric($s['percentage']) ? $s['percentage'] : 0, $b['scores']));
            return $sumB <=> $sumA;
        });
    
    
        foreach ($reportCardsData as $index => &$row) {
            $row['position'] = $index + 1;
        }
        
        unset($row);
    
        $maleCount = 0;
        $femaleCount = 0;
    
        foreach ($reportCardsData as $reportCard) {
            $grade = $reportCard['grade'] ?? 'X';
            $gender = $reportCard['student']->gender === 'M' ? 'M' : 'F';
    
            if (isset($gradeCounts[$grade][$gender])) {
                $gradeCounts[$grade][$gender]++;
            }
    
            if ($gender === 'M') {
                $maleCount++;
            } else {
                $femaleCount++;
            }
        }
    
        $totalStudents = count($reportCardsData);
        $safePercentage = function($count, $total) {
            return AssessmentHelper::formatPercentage($count, $total);
        };
    
        $sumM = $gradeCounts['M']['M'] + $gradeCounts['M']['F'];
        $sumA = $gradeCounts['A']['M'] + $gradeCounts['A']['F'];
        $sumB = $gradeCounts['B']['M'] + $gradeCounts['B']['F'];
        $sumC = $gradeCounts['C']['M'] + $gradeCounts['C']['F'];
        $sumD = $gradeCounts['D']['M'] + $gradeCounts['D']['F'];
        $sumE = $gradeCounts['E']['M'] + $gradeCounts['E']['F'];
        $sumU = $gradeCounts['U']['M'] + $gradeCounts['U']['F'];
    
        $m_M = $gradeCounts['M']['M']; $m_F = $gradeCounts['M']['F'];
        $a_M = $gradeCounts['A']['M']; $a_F = $gradeCounts['A']['F'];
        $b_M = $gradeCounts['B']['M']; $b_F = $gradeCounts['B']['F'];
        $c_M = $gradeCounts['C']['M']; $c_F = $gradeCounts['C']['F'];
        $d_M = $gradeCounts['D']['M']; $d_F = $gradeCounts['D']['F'];
        $e_M = $gradeCounts['E']['M']; $e_F = $gradeCounts['E']['F'];
        $u_M = $gradeCounts['U']['M']; $u_F = $gradeCounts['U']['F'];
        $x_M = $gradeCounts['X']['M']; $x_F = $gradeCounts['X']['F'];
    
        $mabCount = $sumM + $sumA + $sumB; 
        $mabcCount = $mabCount + $sumC;
        $mabcdCount = $mabcCount + $sumD;
        $deuCount = $sumD + $sumE + $sumU;
    
        $mab_M = $m_M + $a_M + $b_M;
        $mabc_M = $mab_M + $c_M;
        $mabcd_M = $mabc_M + $d_M;
        $deu_M = $d_M + $e_M + $u_M;
    
        $mab_F = $m_F + $a_F + $b_F;
        $mabc_F = $mab_F + $c_F;
        $mabcd_F = $mabc_F + $d_F;
        $deu_F = $d_F + $e_F + $u_F;
    
        $mab_T = $mab_M + $mab_F;
        $mabc_T = $mabc_M + $mabc_F;
        $mabcd_T = $mabcd_M + $mabcd_F;
        $deu_T = $deu_M + $deu_F;
        $x_T = $x_M + $x_F;
    
        $mabPercentage = $safePercentage($mabCount, $totalStudents);
        $mabcPercentage = $safePercentage($mabcCount, $totalStudents);
        $mabcdPercentage = $safePercentage($mabcdCount, $totalStudents);
        $deuPercentage = $safePercentage($deuCount, $totalStudents);
    
        $mab_M_Percentage = $safePercentage($mab_M, $maleCount);
        $mab_F_Percentage = $safePercentage($mab_F, $femaleCount);
        $mab_T_percentage = $safePercentage($mab_T, $totalStudents);
        $mabc_T_percentage = $safePercentage($mabc_T, $totalStudents);
        $mabcd_T_percentage = $safePercentage($mabcd_T, $totalStudents);
        $deu_T_percentage = $safePercentage($deu_T, $totalStudents);
        $x_T_Percentange = $safePercentage($x_T, $totalStudents);
    
        $mabc_M_Percentage = $safePercentage($mabc_M, $maleCount);
        $mabc_F_Percentage = $safePercentage($mabc_F, $femaleCount);
    
        $mabcd_M_Percentage = $safePercentage($mabcd_M, $maleCount);
        $mabcd_F_Percentage = $safePercentage($mabcd_F, $femaleCount);
    
        $deu_M_Percentage = $safePercentage($deu_M, $maleCount);
        $deu_F_Percentage = $safePercentage($deu_F, $femaleCount);
    
        $x_M_Percentage = $safePercentage($x_M, $maleCount);
        $x_F_Percentage = $safePercentage($x_F, $femaleCount);
    
        foreach ($reportCardsData as $reportCard) {
            if (!isset($reportCard['student'])) {
                continue;
            }
        
            $gender = $reportCard['student']->gender === 'M' ? 'M' : 'F';
            foreach ($reportCard['scores'] as $subject => $data) {
                if (!is_array($data)) {
                    continue;
                }
                
                $isEnrolled = isset($data['enrolled']) ? $data['enrolled'] : false;
                if ($isEnrolled && isset($subjectGradeCounts[$subject]['enrolled'][$gender])) {
                    $subjectGradeCounts[$subject]['enrolled'][$gender]++;
                }
                
                if (!$isEnrolled) {
                    continue;
                }
                
                $grade = isset($data['grade']) ? $data['grade'] : 'X';
                $hasScore = isset($data['percentage']) && !is_null($data['percentage']);
                
                if (!$hasScore && isset($subjectGradeCounts[$subject]['no_scores'][$gender])) {
                    $subjectGradeCounts[$subject]['no_scores'][$gender]++;
                }
                
                if (isset($subjectGradeCounts[$subject][$grade][$gender])) {
                    $subjectGradeCounts[$subject][$grade][$gender]++;
                    $subjectGradeCounts[$subject]['total'][$gender]++;
                }
            }
        }
    
        foreach ($subjectGradeCounts as $subject => &$counts) {
            foreach (['M', 'F'] as $gender) {
                $enrolled = $counts['enrolled'][$gender];
    
                $abCount = $counts['A'][$gender] + $counts['B'][$gender];
                $abcCount = $abCount + $counts['C'][$gender];
                $abcdCount = $abcCount + $counts['D'][$gender];
                $deuCountSub = $counts['D'][$gender] + $counts['E'][$gender] + $counts['U'][$gender];
                $xCountSub = $counts['X'][$gender];
    
                $counts['AB%'][$gender] = $enrolled > 0 ? round(($abCount / $enrolled) * 100, 2) : 0;
                $counts['ABC%'][$gender] = $enrolled > 0 ? round(($abcCount / $enrolled) * 100, 2) : 0;
                $counts['ABCD%'][$gender] = $enrolled > 0 ? round(($abcdCount / $enrolled) * 100, 2) : 0;
                $counts['DEU%'][$gender] = $enrolled > 0 ? round(($deuCountSub / $enrolled) * 100, 2) : 0;
                $counts['X%'][$gender] = $enrolled > 0 ? round(($xCountSub / $enrolled) * 100, 2) : 0;
            }
        }
        unset($counts);   

        $subjectTotals = [
            'A'=>['M'=>0,'F'=>0], 'B'=>['M'=>0,'F'=>0], 'C'=>['M'=>0,'F'=>0],
            'D'=>['M'=>0,'F'=>0], 'E'=>['M'=>0,'F'=>0], 'U'=>['M'=>0,'F'=>0],
            'X'=>['M'=>0,'F'=>0],
            'AB%'=>['M'=>0,'F'=>0,'T'=>0],'ABC%'=>['M'=>0,'F'=>0,'T'=>0],
            'ABCD%'=>['M'=>0,'F'=>0,'T'=>0],'DEU%'=>['M'=>0,'F'=>0,'T'=>0],'X%'=>['M'=>0,'F'=>0,'T'=>0],
            'total'=>['M'=>0,'F'=>0],
            'enrolled'=>['M'=>0,'F'=>0],
            'no_scores'=>['M'=>0,'F'=>0]
        ];
        
        foreach ($subjectGradeCounts as $subj => $c) {
            foreach (['A','B','C','D','E','U','X'] as $g) {
                $subjectTotals[$g]['M'] += $c[$g]['M'];
                $subjectTotals[$g]['F'] += $c[$g]['F'];
            }
            $subjectTotals['total']['M'] += $c['total']['M'];
            $subjectTotals['total']['F'] += $c['total']['F'];
            $subjectTotals['enrolled']['M'] += $c['enrolled']['M'];
            $subjectTotals['enrolled']['F'] += $c['enrolled']['F'];
            $subjectTotals['no_scores']['M'] += $c['no_scores']['M'];
            $subjectTotals['no_scores']['F'] += $c['no_scores']['F'];
        }
        
        foreach (['M', 'F'] as $gender) {
            $totalEnrolled = $subjectTotals['enrolled'][$gender];
            
            if ($totalEnrolled > 0) {
                $totalAB = $subjectTotals['A'][$gender] + $subjectTotals['B'][$gender];
                $subjectTotals['AB%'][$gender] = round(($totalAB / $totalEnrolled) * 100, 2);
                
                $totalABC = $totalAB + $subjectTotals['C'][$gender];
                $subjectTotals['ABC%'][$gender] = round(($totalABC / $totalEnrolled) * 100, 2);
                
                $totalABCD = $totalABC + $subjectTotals['D'][$gender];
                $subjectTotals['ABCD%'][$gender] = round(($totalABCD / $totalEnrolled) * 100, 2);
                
                $totalDEU = $subjectTotals['D'][$gender] + $subjectTotals['E'][$gender] + $subjectTotals['U'][$gender];
                $subjectTotals['DEU%'][$gender] = round(($totalDEU / $totalEnrolled) * 100, 2);
                
                $totalX = $subjectTotals['X'][$gender];
                $subjectTotals['X%'][$gender] = round(($totalX / $totalEnrolled) * 100, 2);
            } else {
                $subjectTotals['AB%'][$gender] = 0;
                $subjectTotals['ABC%'][$gender] = 0;
                $subjectTotals['ABCD%'][$gender] = 0;
                $subjectTotals['DEU%'][$gender] = 0;
                $subjectTotals['X%'][$gender] = 0;
            }
        }
        
        $totalEnrolledCombined = $subjectTotals['enrolled']['M'] + $subjectTotals['enrolled']['F'];
        
        if ($totalEnrolledCombined > 0) {
            $totalABCombined = $subjectTotals['A']['M'] + $subjectTotals['A']['F'] + $subjectTotals['B']['M'] + $subjectTotals['B']['F'];
            $subjectTotals['AB%']['T'] = round(($totalABCombined / $totalEnrolledCombined) * 100, 2);

            $totalABCCombined = $totalABCombined + $subjectTotals['C']['M'] + $subjectTotals['C']['F'];
            $subjectTotals['ABC%']['T'] = round(($totalABCCombined / $totalEnrolledCombined) * 100, 2);
            
            $totalABCDCombined = $totalABCCombined + $subjectTotals['D']['M'] + $subjectTotals['D']['F'];
            $subjectTotals['ABCD%']['T'] = round(($totalABCDCombined / $totalEnrolledCombined) * 100, 2);
            
            $totalDEUCombined = $subjectTotals['D']['M'] + $subjectTotals['D']['F'] + $subjectTotals['E']['M'] + $subjectTotals['E']['F'] + $subjectTotals['U']['M'] + $subjectTotals['U']['F'];
            $subjectTotals['DEU%']['T'] = round(($totalDEUCombined / $totalEnrolledCombined) * 100, 2);
            
            $totalXCombined = $subjectTotals['X']['M'] + $subjectTotals['X']['F'];
            $subjectTotals['X%']['T'] = round(($totalXCombined / $totalEnrolledCombined) * 100, 2);
        } else {
            $subjectTotals['AB%']['T'] = 0;
            $subjectTotals['ABC%']['T'] = 0;
            $subjectTotals['ABCD%']['T'] = 0;
            $subjectTotals['DEU%']['T'] = 0;
            $subjectTotals['X%']['T'] = 0;
        }
    
        $psleTotalM = array_sum(array_column($psleGradeCounts, 'M'));
        $psleTotalF = array_sum(array_column($psleGradeCounts, 'F'));
        $totalPsleStudents = $psleTotalM + $psleTotalF;
    
        $psleA_M = $psleGradeCounts['A']['M']; $psleA_F = $psleGradeCounts['A']['F'];
        $psleB_M = $psleGradeCounts['B']['M']; $psleB_F = $psleGradeCounts['B']['F'];
        $psleC_M = $psleGradeCounts['C']['M']; $psleC_F = $psleGradeCounts['C']['F'];
        $psleD_M = $psleGradeCounts['D']['M']; $psleD_F = $psleGradeCounts['D']['F'];
        $psleE_M = $psleGradeCounts['E']['M']; $psleE_F = $psleGradeCounts['E']['F'];
        $psleU_M = $psleGradeCounts['U']['M']; $psleU_F = $psleGradeCounts['U']['F'];
    
        $psleAB_M = $psleA_M + $psleB_M; $psleAB_F = $psleA_F + $psleB_F;
        $psleAB_T = $psleAB_M + $psleAB_F;
        $psleABC_M = $psleAB_M + $psleC_M; $psleABC_F = $psleAB_F + $psleC_F;
        $psleABC_T = $psleABC_M + $psleABC_F;
        $psleABCD_M = $psleABC_M + $psleD_M; $psleABCD_F = $psleABC_F + $psleD_F;
        $psleABCD_T = $psleABCD_M + $psleABCD_F;
        $psleDEU_M = $psleD_M + $psleE_M + $psleU_M; $psleDEU_F = $psleD_F + $psleE_F + $psleU_F;
        $psleDEU_T = $psleDEU_M + $psleDEU_F;
    
        $psleAB_M_Percentage = $safePercentage($psleAB_M, $psleTotalM);
        $psleAB_F_Percentage = $safePercentage($psleAB_F, $psleTotalF);
        $psleAB_T_percentage = $safePercentage($psleAB_T, $totalPsleStudents);
    
        $psleABC_M_Percentage = $safePercentage($psleABC_M, $psleTotalM);
        $psleABC_F_Percentage = $safePercentage($psleABC_F, $psleTotalF);
        $psleABC_T_percentage = $safePercentage($psleABC_T, $totalPsleStudents);
    
        $psleABCD_M_Percentage = $safePercentage($psleABCD_M, $psleTotalM);
        $psleABCD_F_Percentage = $safePercentage($psleABCD_F, $psleTotalF);
        $psleABCD_T_percentage = $safePercentage($psleABCD_T, $totalPsleStudents);
    
        $psleDEU_M_Percentage = $safePercentage($psleDEU_M, $psleTotalM);
        $psleDEU_F_Percentage = $safePercentage($psleDEU_F, $psleTotalF);
        $psleDEU_T_percentage = $safePercentage($psleDEU_T, $totalPsleStudents);
    
        $data = [
            'reportCards' => $reportCardsData,
            'school_data' => $school_setup,
            'allSubjects' => $allSubjects,
            'currentTerm' => $currentTerm,
            'klass' => $klass,
            'test' => $test,
            'gradeCounts' => $gradeCounts,
            'subjectGradeCounts' => $subjectGradeCounts,
            'totalStudents' => $totalStudents,
            'psleGradeCounts' => $psleGradeCounts,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
    
            'mabCount' => $mabCount,
            'mabcCount' => $mabcCount,
            'mabcdCount' => $mabcdCount,
            'deuCount' => $deuCount,
            'mabPercentage' => $mabPercentage,
            'mabcPercentage' => $mabcPercentage,
            'mabcdPercentage' => $mabcdPercentage,
            'deuPercentage' => $deuPercentage,
    
            'x_T_Percentage' => $x_T_Percentange,
            'x_M_Percentage' => $x_M_Percentage,
            'x_F_Percentage' => $x_F_Percentage,
    
            'mab_M' => $mab_M,
            'mab_F' => $mab_F,
            'mabc_M' => $mabc_M,
            'mabc_F' => $mabc_F,
            'mabcd_M' => $mabcd_M,
            'mabcd_F' => $mabcd_F,
            'deu_M' => $deu_M,
            'deu_F' => $deu_F,
            'mab_T' => $mab_T,
            'mabc_T' => $mabc_T,
            'mabcd_T' => $mabcd_T,
            'deu_T' => $deu_T,
            'x_M' => $x_M,
            'x_F' => $x_F,
            'x_T' => $x_T,
    
            'mab_M_Percentage' => $mab_M_Percentage,
            'mab_F_Percentage' => $mab_F_Percentage,
            'mabc_M_Percentage' => $mabc_M_Percentage,
            'mabc_F_Percentage' => $mabc_F_Percentage,
            'mabcd_M_Percentage' => $mabcd_M_Percentage,
            'mabcd_F_Percentage' => $mabcd_F_Percentage,
            'deu_M_Percentage' => $deu_M_Percentage,
            'deu_F_Percentage' => $deu_F_Percentage,
            'mab_T_percentage' => $mab_T_percentage,
            'mabc_T_percentage' => $mabc_T_percentage,
            'mabcd_T_percentage' => $mabcd_T_percentage,
            'deu_T_percentage' => $deu_T_percentage,
    
            'psleTotalM' => $psleTotalM,
            'psleTotalF' => $psleTotalF,
    
            'psleAB_M' => $psleAB_M,
            'psleAB_F' => $psleAB_F,
            'psleABC_M' => $psleABC_M,
            'psleABC_F' => $psleABC_F,
            'psleABCD_M' => $psleABCD_M,
            'psleABCD_F' => $psleABCD_F,
            'psleDEU_M' => $psleDEU_M,
            'psleDEU_F' => $psleDEU_F,
            'psleAB_T' => $psleAB_T,
            'psleABC_T' => $psleABC_T,
            'psleABCD_T' => $psleABCD_T,
            'psleDEU_T' => $psleDEU_T,
    
            'psleAB_M_Percentage' => $psleAB_M_Percentage,
            'psleAB_F_Percentage' => $psleAB_F_Percentage,
            'psleABC_M_Percentage' => $psleABC_M_Percentage,
            'psleABC_F_Percentage' => $psleABC_F_Percentage,
            'psleABCD_M_Percentage' => $psleABCD_M_Percentage,
            'psleABCD_F_Percentage' => $psleABCD_F_Percentage,
            'psleDEU_M_Percentage' => $psleDEU_M_Percentage,
            'psleDEU_F_Percentage' => $psleDEU_F_Percentage,
    
            'psleAB_T_Percentage' => $psleAB_T_percentage,
            'psleABC_T_Percentage' => $psleABC_T_percentage,
            'psleABCD_T_Percentage' => $psleABCD_T_percentage,
            'psleDEU_T_Percentage' => $psleDEU_T_percentage,
    
            'subjectTotals' => $subjectTotals,
        ];
    
        if (request()->has('export')) {
            return Excel::download(
                new \App\Exports\CAAnalysisExport($data),
                "CA_Grade_Overall_Analysis_{$klass->name}_" . date('Y-m-d') . ".xlsx"
            );
        }
        return view('assessment.shared.overall-ca-grade-analysis', $data);
    }

    private function calculateSubjectScoresAnalysis(Student $student, GradeSubject $subject, $selectedTermId, $grade, $type = 'Exam', $sequence = 1){
        $tests = $student->tests()
            ->where('term_id', $selectedTermId)
            ->where('grade_subject_id', $subject->id)
            ->where('type', $type)
            ->where('grade_id', $grade)
            ->where('sequence', $sequence)
            ->orderBy('sequence', 'asc')
            ->get();

        if ($type === 'Exam') {
            $test = $tests->first();
        } else {
            $test = $tests->first();
        }

        $score = $test ? $test->pivot->score : null;
        $percentage = $test ? $test->pivot->percentage : null;
        $points = $test ? $test->pivot->points : null;
        $grade = $test ? $test->pivot->grade : null;

        return [
            'subject' => $subject->subject->name,
            'score' => $score,
            'percentage' => $percentage,
            'points' => $points,
            'grade' => $grade,
        ];
    }

    /**
     * Show class term analysis report
     */
    public function showClassTermAnalysisReport($classId){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $reportData = $this->prepareClassTermAnalysisReport($classId, $selectedTermId);

        $school_data = SchoolSetup::first();

        $gradeSubjects = GradeSubject::with('subject')
            ->whereHas('tests', function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->where('type', 'Exam');;
            })
            ->get();


        return view('assessment.shared.class-analysis-term', ['reportData' => $reportData, 'gradeSubjects' => $gradeSubjects, 'school_data' => $school_data]);
    }

    public function prepareClassTermAnalysisReport($classId, $selectedTermId){
        $students = Student::whereHas('terms', function ($query) use ($selectedTermId) {
            $query->where('id', $selectedTermId);
        })
            ->where('klass_id', $classId)
            ->with(['tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->where('type', 'Exam');
            }])
            ->get();

        $gradeSubjects = GradeSubject::with('subject')
            ->whereHas('tests', function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->where('type', 'Exam');
            })
            ->get();

        $reportData = [];

        foreach ($students as $student) {
            $studentData = [
                'name' => $student->first_name . ' ' . $student->last_name,
                'subjects' => [],
                'averageScore' => 0,
                'overallGrade' => 'N/A'
            ];

            $totalScore = 0;
            $countSubjects = 0;

            foreach ($gradeSubjects as $gradeSubject) {
                $test = $student->tests->where('grade_subject_id', $gradeSubject->id)->first();
                $subjectName = $gradeSubject->subject->name;
                $score = $test->pivot->percentage ?? 0;
                $totalScore += $score;
                $countSubjects++;

                $studentData['subjects'][$subjectName] = [
                    'mark' => $score,
                    'grade' => $test->pivot->grade ?? 'N/A'
                ];
            }

            if ($countSubjects > 0) {
                $averageScore = $totalScore / $countSubjects;
                $studentData['averageScore'] = $averageScore;
                $studentData['overallGrade'] = self::getOverallGrade($student->grade_id, $averageScore);
            }
            $reportData[] = $studentData;
        }

        usort($reportData, function ($a, $b) {
            return $b['averageScore'] <=> $a['averageScore'];
        });
        return $reportData;
    }

    public static function getOverallGrade($grade_id, $percentage){
        $grade = OverallGradingMatrix::where('grade_id', $grade_id)
            ->where('min_score', '<=', $percentage)
            ->where('max_score', '>=', $percentage)
            ->first();

        if ($grade === null) {
            return null;
        }
        return $grade;
    }

    /**
     * Show class grade analysis report
     */
    public function showClassGradeAnalysisReport($classId){
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        $reportData = $this->prepareClassGradeAnalysisReport($classId);
        $school_data = SchoolSetup::first();

        $students = Student::whereHas('classes')->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)
                ->where('type', 'Exam')
                ->where('assessment', 1);
        }])->get();

        $gradeCounts = [
            'M' => array_fill_keys(['A', 'B', 'C', 'D', 'E'], 0),
            'F' => array_fill_keys(['A', 'B', 'C', 'D', 'E'], 0)
        ];

        foreach ($students as $student) {
            $totalPercentage = 0;
            $examCount = 0;

            foreach ($student->tests as $test) {
                $totalPercentage += $test->pivot->percentage ?? 0;
                $examCount++;
            }

            if ($examCount > 0) {
                $averagePercentage = $totalPercentage / $examCount;
                $overallGrade = self::getOverallGrade($student->grade_id, $averagePercentage)->grade ?? ''; // Calculate overall grade
                $gender = $student->gender; // 'M' or 'F'

                if (isset($gradeCounts[$gender][$overallGrade])) {
                    $gradeCounts[$gender][$overallGrade]++;
                }
            }
        }

        return view('assessment.shared.class-grade-analysis-term', ['reportData' => $reportData, 'school_data' => $school_data, 'gradeCounts' => $gradeCounts]);
    }

    public function prepareClassGradeAnalysisReport($classId){
        $selectedTermId = session('selected_term_id') ?? TermHelper::getCurrentTerm()->id;

        $students =  Student::whereHas('terms', function ($query) use ($selectedTermId) {
            $query->where('id', $selectedTermId);
        })->get();

        $studentIds = $students->pluck('id');
        $klass = Klass::find($classId);

        $gradeSubjects = GradeSubject::where('grade_id', $klass->grade_id)->with(['subject', 'tests' => function ($query) use ($selectedTermId) {
            $query->where('type', 'exam')
                ->where('assessment', 1)
                ->where('term_id', $selectedTermId);
        }, 'tests.studentTests' => function ($query) use ($studentIds) {
            $query->whereIn('student_id', $studentIds);
        }])->whereHas('tests', function ($query) use ($selectedTermId) {
            $query->where('type', 'exam')
                ->where('assessment', 1)
                ->where('term_id', $selectedTermId);
        })->get();

        $gradeCategories = ['A', 'B', 'C', 'D', 'E']; // Standard grade categories
        $passGrades = ['A', 'B', 'C']; // Grades considered as pass
        $failGrades = ['D', 'E']; // Grades considered as fail

        $reportData = [];

        foreach ($gradeSubjects as $gradeSubject) {
            $subjectData = [
                'name' => $gradeSubject->subject->name,
                'grades' => [],
                'pass' => ['count' => 0, 'percentage' => 0], // For ABC
                'fail' => ['count' => 0, 'percentage' => 0]  // For DE
            ];

            $totalStudents = $gradeSubject->tests->flatMap->studentTests->count();

            foreach ($gradeCategories as $grade) {
                $count = $gradeSubject->tests->flatMap->studentTests->where('grade', $grade)->count();
                $percentage = $totalStudents > 0 ? ($count / $totalStudents) * 100 : 0;
                $subjectData['grades'][$grade] = [
                    'count' => $count,
                    'percentage' => $percentage
                ];

                // Counting for pass and fail categories
                if (in_array($grade, $passGrades)) {
                    $subjectData['pass']['count'] += $count;
                } elseif (in_array($grade, $failGrades)) {
                    $subjectData['fail']['count'] += $count;
                }
            }

            // Calculating percentages for pass and fail categories
            $subjectData['pass']['percentage'] = $totalStudents > 0 ? ($subjectData['pass']['count'] / $totalStudents) * 100 : 0;
            $subjectData['fail']['percentage'] = $totalStudents > 0 ? ($subjectData['fail']['count'] / $totalStudents) * 100 : 0;

            $reportData[] = $subjectData;
        }
        return $reportData;
    }

    private function exportClassPerformanceToExcel(
        array $classPerformance,
        array $overallTotals,
        Test $test
    ) {
        return Excel::download(
            new ClassPerformanceExport($classPerformance, $overallTotals, $test),
            'classes_performance_' . strtolower($test->type) . '_' . date('Y-m-d') . '.xlsx'
        );
    }
}
