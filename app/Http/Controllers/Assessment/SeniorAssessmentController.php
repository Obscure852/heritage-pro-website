<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\StudentController;
use App\Helpers\TermHelper;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\User;
use App\Models\KlassSubject;
use App\Models\Klass;
use App\Models\Term;
use App\Models\GradeSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherPerformanceSeniorExport;
use App\Exports\SeniorSubjectABCPerformanceExport;
use App\Exports\CAAnalysisExport;
use App\Models\Grade;
use App\Models\House;
use App\Models\Test;
use App\Exports;
use App\Exports\ClassCreditsSummaryExport;
use App\Exports\GradeSubjectAnalysisExport;
use App\Exports\HouseCreditsPerformanceExport;
use App\Exports\SubjectTeacherGradeAnalysisExport;
use App\Models\OptionalSubject;
use App\Models\StudentTest;
use App\Services\ValueAdditionService;
use App\Exports\ValueAdditionExport;
use App\Exports\TeacherValueAdditionExport;
use App\Exports\HouseAwardAnalysisExport;
use App\Models\ValueAdditionSubjectMapping;
use Illuminate\Support\Facades\Cache;

/**
 * Senior Assessment Controller
 *
 * Handles all assessment functionality specific to Senior Secondary Schools.
 * Includes report card generation, analysis reports, and senior-specific calculations.
 *
 * Methods in this controller (21 total):
 * - Report Cards: htmlReportCardSenior1, htmlReportCardSenior, pdfReportCardSenior,
 *                 generateReportCardSeniorPDF2, generateReportCardSeniorPDF,
 *                 generateSeniorClassListReportCards, generateEmailSeniorClassListReportCards
 * - Analysis: generateSubjectAnalysisReportSenior, generateGradeSubjectAnalysisReportSenior,
 *             generateSeniorGradeStudentList, generateCAAnalysisSenior, generateSeniorCreditsReport,
 *             generateSubjectSeniorABCPerformanceReport, generateGradeAnalysisSenior,
 *             generateCASeniorHousePerformanceReport, generateOverallTeacherPerformanceReportSenior
 * - Calculations: calculateStudentScoresSenior, calculateStudentScoresSeniorCA,
 *                 calculateTeacherPerformanceDataSenior, getStudentPositionSenior,
 *                 calculateClassRankingsSenior
 */
class SeniorAssessmentController extends BaseAssessmentController
{
    private function applySeniorAssessmentStudentScoreFilter($query, string $type): void
    {
        if ($type === 'CA') {
            $query->where(function ($inner) {
                $inner->whereNotNull('avg_score')
                    ->orWhereNotNull('score');
            });
            return;
        }

        $query->whereNotNull('score');
    }

    /**
     * Format a subject grade for display, doubling it for double-award subjects
     * (e.g. double science: "A" -> "AA", "A*" -> "A*A*"). Placeholders and
     * already-doubled grades are returned as-is so the underlying analysis
     * counting logic is never affected.
     */
    private function formatDoubleAwardGrade(string $grade, bool $isDouble): string
    {
        if (!$isDouble) {
            return $grade;
        }

        if ($grade === '' || $grade === 'X' || $grade === '-') {
            return $grade;
        }

        // "A*" is conceptually a single grade that is 2 characters long.
        if ($grade === 'A*') {
            return 'A*A*';
        }

        // Single-letter grade -> duplicate it for the double award representation.
        if (strlen($grade) === 1) {
            return $grade . $grade;
        }

        // Already stored as a two-character double grade (e.g. "AA", "AB").
        return $grade;
    }

    private function resolveSeniorAssessmentValues(?Test $assessmentTest, GradeSubject $subject, int $selectedTermId, string $type): array
    {
        if (!$assessmentTest) {
            return [
                'score' => 0,
                'percentage' => 0,
                'grade' => '',
                'points' => 0,
            ];
        }

        if ($type === 'CA') {
            $averageScore = $assessmentTest->pivot->avg_score;
            $averageGrade = trim((string) ($assessmentTest->pivot->avg_grade ?? ''));

            return [
                'score' => $averageScore ?? $assessmentTest->pivot->score ?? 0,
                'percentage' => $averageScore ?? $assessmentTest->pivot->percentage ?? 0,
                'grade' => $averageGrade !== '' ? $averageGrade : trim((string) ($assessmentTest->pivot->grade ?? '')),
                'points' => $this->resolveSeniorCAPoints($assessmentTest, $subject, $selectedTermId, $averageScore),
            ];
        }

        return [
            'score' => $assessmentTest->pivot->score ?? 0,
            'percentage' => $assessmentTest->pivot->percentage ?? 0,
            'grade' => trim((string) ($assessmentTest->pivot->grade ?? '')),
            'points' => (int) ($assessmentTest->pivot->points ?? 0),
        ];
    }

    private function resolveSeniorCAPoints(Test $assessmentTest, GradeSubject $subject, int $selectedTermId, $averageScore): int
    {
        if ($averageScore === null) {
            return (int) ($assessmentTest->pivot->points ?? 0);
        }

        $points = DB::table('grading_scales')
            ->where('grade_subject_id', $subject->id)
            ->where('term_id', $selectedTermId)
            ->where('grade_id', $assessmentTest->grade_id)
            ->where('min_score', '<=', (int) round($averageScore))
            ->where('max_score', '>=', (int) round($averageScore))
            ->value('points');

        return (int) ($points ?? $assessmentTest->pivot->points ?? 0);
    }

    /**
     * Generate HTML report card for senior student (Version 1)
     */
    public function htmlReportCardSenior1($id)
    {
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'jce'
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);
        $subjects = $student->tests->pluck('subject')->unique();

        $jceGrades = $student->jce ? $student->jce->toArray() : [];
        $overallJceGrade = $jceGrades['overall'] ?? null;

        $scores = [];
        foreach ($subjects as $subject) {
            $examTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'Exam')->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'CA')->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();

            $subjectName = strtolower($subject->subject->name);
            $jceGrade = $jceGrades[$subjectName] ?? $overallJceGrade;

            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'points' => $points,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'jceGrade' => $jceGrade,
                'isOverallJceGrade' => !isset($jceGrades[$subjectName]) && $jceGrade !== null,
            ];
        }

        usort($scores, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        $bestSubjects = array_slice($scores, 0, 6);
        $totalPoints = array_sum(array_column($bestSubjects, 'points'));

        $studentRankings = $this->calculateClassRankingsSenior($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $position = $this->getStudentPosition($studentRankings, $id);

        $grade = $this->determineGrade($totalPoints, $currentClass);

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,
            'position' => $position,
            'classAverage' => round($classAverage, 2),
            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,
            'scores' => $scores,
            'bestSubjects' => $bestSubjects,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,
            'overallJceGrade' => $overallJceGrade,
        ];

        return view('assessment.senior.report-card-html-senior', $data);
    }

    /**
     * Generate HTML report card for senior student
     */
    public function htmlReportCardSenior($id)
    {
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'jce'
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $currentClassId = $currentClass->id;

        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);
        $subjects = $student->tests->pluck('subject')->unique();

        $jceGrades = $student->jce ? $student->jce->toArray() : [];
        $overallJceGrade = $jceGrades['overall'] ?? null;

        $scores = [];
        foreach ($subjects as $subject) {
            $examTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'Exam')->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'CA')->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();

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

            $subjectName = strtolower($subject->subject->name);
            $jceGrade = $jceGrades[$subjectName] ?? $overallJceGrade;

            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);
            $isDouble = (bool) $subject->subject->is_double;

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'is_double' => $isDouble,
                'points' => $points,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'jceGrade' => $jceGrade,
                'isOverallJceGrade' => !isset($jceGrades[$subjectName]) && $jceGrade !== null,
                'teacher' => $teacherName,
            ];
        }

        $scoresForCalculation = $scores;
        foreach ($scoresForCalculation as &$score) {
            $score['slotsNeeded'] = $score['is_double'] ? 2 : 1;
        }
        unset($score);
        usort($scoresForCalculation, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        $totalSlots = 0;
        $totalPoints = 0;
        $bestSubjects = [];

        foreach ($scoresForCalculation as $score) {
            $slotsNeeded = $score['slotsNeeded'];
            if ($totalSlots + $slotsNeeded <= 6) {
                $bestSubjects[] = $score;
                $totalSlots += $slotsNeeded;
                if ($score['is_double']) {
                    $totalPoints += $score['points'] * 2;
                } else {
                    $totalPoints += $score['points'];
                }
            }
            if ($totalSlots >= 6) {
                break;
            }
        }

        $studentRankings = $this->calculateClassRankingsSenior($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $position = $this->getStudentPositionSenior($studentRankings, $student->id);

        $grade = $this->determineGrade($totalPoints, $currentClass);

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        $manualEntry = $student->manualAttendanceEntries()->where('term_id', $selectedTermId)->first();
        $absentDays = $manualEntry && $manualEntry->days_absent !== null
            ? $manualEntry->days_absent
            : $student->absentDays()->where('term_id', $selectedTermId)->count();

        $school_fees = $manualEntry && $manualEntry->school_fees_owing !== null
            ? $manualEntry->school_fees_owing
            : null;
        $other_info = $manualEntry && $manualEntry->other_info !== null
            ? $manualEntry->other_info
            : null;

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,
            'position' => $position,
            'classAverage' => round($classAverage, 2),
            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,
            'scores' => $scores,
            'bestSubjects' => $bestSubjects,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,
            'overallJceGrade' => $overallJceGrade,
            'absentDays' => $absentDays,
            'school_fees' => $school_fees,
            'otherInfo' => $other_info
        ];
        return view('assessment.senior.report-card-html-senior', $data);
    }

    /**
     * Generate PDF report card for senior student
     */
    public function pdfReportCardSenior($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'jce'
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $currentClassId = $currentClass->id;

        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);
        $subjects = $student->tests->pluck('subject')->unique();

        $jceGrades = $student->jce ? $student->jce->toArray() : [];
        $overallJceGrade = $jceGrades['overall'] ?? null;

        $scores = [];
        foreach ($subjects as $subject) {
            $examTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'Exam')->first();
            $caTest   = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'CA')->first();

            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();
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

            Log::info($caTest);

            $subjectName = strtolower($subject->subject->name);
            $jceGrade = $jceGrades[$subjectName] ?? $overallJceGrade;

            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);
            $isDouble = (bool) $subject->subject->is_double;

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'is_double' => $isDouble,
                'points' => $points,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'jceGrade' => $jceGrade,
                'isOverallJceGrade' => !isset($jceGrades[$subjectName]) && $jceGrade !== null,
                'teacher' => $teacherName,
            ];
        }

        $scores = array_filter($scores, function($score) {
            return ($score['score'] > 0 || $score['caAverage'] > 0);
        });

        $scores = array_values($scores);
        $scoresForCalculation = $scores;

        foreach ($scoresForCalculation as &$score) {
            $score['slotsNeeded'] = $score['is_double'] ? 2 : 1;
        }
        unset($score);
        usort($scoresForCalculation, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        $totalSlots = 0;
        $totalPoints = 0;
        $bestSubjects = [];

        foreach ($scoresForCalculation as $score) {
            $slotsNeeded = $score['slotsNeeded'];
            if ($totalSlots + $slotsNeeded <= 6) {
                $bestSubjects[] = $score;
                $totalSlots += $slotsNeeded;
                if ($score['is_double']) {
                    $totalPoints += $score['points'] * 2;
                } else {
                    $totalPoints += $score['points'];
                }
            }
            if ($totalSlots >= 6) {
                break;
            }
        }

        $studentRankings = $this->calculateClassRankingsSenior($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $position = $this->getStudentPositionSenior($studentRankings, $student->id);

        $grade = $this->determineGrade($totalPoints, $currentClass);

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        $manualEntry = $student->manualAttendanceEntries()->where('term_id', $selectedTermId)->first();
        $absentDays = $manualEntry && $manualEntry->days_absent !== null
            ? $manualEntry->days_absent
            : $student->absentDays()->where('term_id', $selectedTermId)->count();

        $school_fees = $manualEntry && $manualEntry->school_fees_owing !== null
            ? $manualEntry->school_fees_owing
            : null;
        $other_info = $manualEntry && $manualEntry->other_info !== null
            ? $manualEntry->other_info
            : null;

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,
            'position' => $position,
            'classAverage' => round($classAverage, 2),
            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,
            'scores' => $scores,
            'bestSubjects' => $bestSubjects,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,
            'overallJceGrade' => $overallJceGrade,
            'absentDays' => $absentDays,
            'school_fees' => $school_fees,
            'otherInfo' => $other_info
        ];

        $pdf = PDF::loadView('assessment.senior.report-card-pdf-senior', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream('senior-student-report-card.pdf');
    }

    /**
     * Generate PDF report card for senior student (Version 2 - for email)
     */
    public function generateReportCardSeniorPDF2($id)
    {
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'jce'
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);
        $subjects = $student->tests->pluck('subject')->unique();

        $jceGrades = $student->jce ? $student->jce->toArray() : [];
        $overallJceGrade = $jceGrades['overall'] ?? null;

        $scores = [];
        $classId = $currentClass ? $currentClass->id : null;

        foreach ($subjects as $subject) {
            $examTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'Exam')->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'CA')->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();

            $subjectName = strtolower($subject->subject->name);
            $jceGrade = $jceGrades[$subjectName] ?? $overallJceGrade;

            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);

            // Get teacher name
            $teacherName = 'N/A';
            if ($classId) {
                $klassSubject = KlassSubject::where('grade_subject_id', $subject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('klass_id', $classId)
                    ->first();

                if ($klassSubject && $klassSubject->user_id) {
                    $teacher = User::find($klassSubject->user_id);
                    $teacherName = $teacher ? $teacher->lastname : 'N/A';
                } else {
                    $studentOptionalSubject = DB::table('student_optional_subjects')
                        ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                        ->where('student_optional_subjects.student_id', $student->id)
                        ->where('student_optional_subjects.term_id', $selectedTermId)
                        ->where('student_optional_subjects.klass_id', $classId)
                        ->where('optional_subjects.grade_subject_id', $subject->id)
                        ->first();

                    if ($studentOptionalSubject && $studentOptionalSubject->user_id) {
                        $teacher = User::find($studentOptionalSubject->user_id);
                        $teacherName = $teacher ? $teacher->lastname : 'N/A';
                    }
                }
            }

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'points' => $points,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'jceGrade' => $jceGrade,
                'isOverallJceGrade' => !isset($jceGrades[$subjectName]) && $jceGrade !== null,
                'teacher' => $teacherName,
            ];
        }

        usort($scores, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        $bestSubjects = array_slice($scores, 0, 6);
        $totalPoints = array_sum(array_column($bestSubjects, 'points'));

        $studentRankings = $this->calculateClassRankingsSenior($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $position = $this->getStudentPosition($studentRankings, $id);

        $grade = $this->determineGrade($totalPoints, $currentClass);

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        $manualEntry = $student->manualAttendanceEntries()->where('term_id', $selectedTermId)->first();
        $absentDays = $manualEntry && $manualEntry->days_absent !== null
            ? $manualEntry->days_absent
            : $student->absentDays()->where('term_id', $selectedTermId)->count();

        $school_fees = $manualEntry && $manualEntry->school_fees_owing !== null
            ? $manualEntry->school_fees_owing
            : null;
        $other_info = $manualEntry && $manualEntry->other_info !== null
            ? $manualEntry->other_info
            : null;

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,
            'position' => $position,
            'classAverage' => round($classAverage, 2),
            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,
            'scores' => $scores,
            'bestSubjects' => $bestSubjects,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,
            'overallJceGrade' => $overallJceGrade,
            'absentDays' => $absentDays,
            'school_fees' => $school_fees,
            'otherInfo' => $other_info
        ];

        $pdf = PDF::loadView('assessment.senior.report-card-pdf-senior', $data);
        $filename = strtolower($student->first_name . '_' . $student->last_name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    /**
     * Generate PDF report card for senior student
     */
    public function generateReportCardSeniorPDF($id){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();

        $student = Student::with([
            'tests' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId)->with('subject');
            },
            'overallComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'subjectComments' => function ($query) use ($selectedTermId) {
                $query->where('term_id', $selectedTermId);
            },
            'jce'
        ])->findOrFail($id);

        $currentClass = $student->currentClass();
        $allStudents = $currentClass->students()->with(['tests' => function ($query) use ($selectedTermId) {
            $query->where('term_id', $selectedTermId)->where('type', 'Exam');
        }])->get();

        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);
        $subjects = $student->tests->pluck('subject')->unique();

        $jceGrades = $student->jce ? $student->jce->toArray() : [];
        $overallJceGrade = $jceGrades['overall'] ?? null;

        $scores = [];
        $classId = $currentClass ? $currentClass->id : null;

        foreach ($subjects as $subject) {
            $examTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'Exam')->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)->where('type', 'CA')->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)->first();

            $subjectName = strtolower($subject->subject->name);
            $jceGrade = $jceGrades[$subjectName] ?? $overallJceGrade;

            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);

            // Get teacher name
            $teacherName = 'N/A';
            if ($classId) {
                $klassSubject = KlassSubject::where('grade_subject_id', $subject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('klass_id', $classId)
                    ->first();

                if ($klassSubject && $klassSubject->user_id) {
                    $teacher = User::find($klassSubject->user_id);
                    $teacherName = $teacher ? $teacher->lastname : 'N/A';
                } else {
                    $studentOptionalSubject = DB::table('student_optional_subjects')
                        ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                        ->where('student_optional_subjects.student_id', $student->id)
                        ->where('student_optional_subjects.term_id', $selectedTermId)
                        ->where('student_optional_subjects.klass_id', $classId)
                        ->where('optional_subjects.grade_subject_id', $subject->id)
                        ->first();

                    if ($studentOptionalSubject && $studentOptionalSubject->user_id) {
                        $teacher = User::find($studentOptionalSubject->user_id);
                        $teacherName = $teacher ? $teacher->lastname : 'N/A';
                    }
                }
            }

            $isDouble = (bool) $subject->subject->is_double;

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'is_double' => $isDouble,
                'points' => $points,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'jceGrade' => $jceGrade,
                'isOverallJceGrade' => !isset($jceGrades[$subjectName]) && $jceGrade !== null,
                'teacher' => $teacherName,
            ];
        }

        $scoresForCalculation = $scores;

        foreach ($scoresForCalculation as &$score) {
            $score['slotsNeeded'] = $score['is_double'] ? 2 : 1;
        }
        unset($score);

        usort($scoresForCalculation, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        $totalSlots = 0;
        $totalPoints = 0;
        $bestSubjects = [];

        foreach ($scoresForCalculation as $score) {
            $slotsNeeded = $score['slotsNeeded'];
            if ($totalSlots + $slotsNeeded <= 6) {
                $bestSubjects[] = $score;
                $totalSlots += $slotsNeeded;
                if ($score['is_double']) {
                    $totalPoints += $score['points'] * 2;
                } else {
                    $totalPoints += $score['points'];
                }
            }
            if ($totalSlots >= 6) {
                break;
            }
        }

        $studentRankings = $this->calculateClassRankingsSenior($allStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);
        $position = $this->getStudentPositionSenior($studentRankings, $student->id);

        $grade = $this->determineGrade($totalPoints, $currentClass);

        $classTeacherRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->class_teacher_remarks ?? 'No remarks provided.';

        $headTeachersRemarks = $student->overallComments
            ->where('term_id', $selectedTermId)
            ->first()->school_head_remarks ?? 'No remarks provided.';

        $manualEntry = $student->manualAttendanceEntries()->where('term_id', $selectedTermId)->first();
        $absentDays = $manualEntry && $manualEntry->days_absent !== null
            ? $manualEntry->days_absent
            : $student->absentDays()->where('term_id', $selectedTermId)->count();

        $school_fees = $manualEntry && $manualEntry->school_fees_owing !== null
            ? $manualEntry->school_fees_owing
            : null;
        $other_info = $manualEntry && $manualEntry->other_info !== null
            ? $manualEntry->other_info
            : null;

        $data = [
            'student' => $student,
            'currentClass' => $currentClass,
            'position' => $position,
            'classAverage' => round($classAverage, 2),
            'school_setup' => $school_setup,
            'classTeacherRemarks' => $classTeacherRemarks,
            'headTeachersRemarks' => $headTeachersRemarks,
            'school_head' => $school_head,
            'scores' => $scores,
            'bestSubjects' => $bestSubjects,
            'nextTermStartDate' => $nextTermStartDate,
            'totalPoints' => $totalPoints,
            'grade' => $grade,
            'overallJceGrade' => $overallJceGrade,
            'absentDays' => $absentDays,
            'school_fees' => $school_fees,
            'otherInfo' => $other_info
        ];

        $pdf = PDF::loadView('assessment.senior.report-card-pdf-senior', $data);
        $filename = strtolower($student->first_name . '_' . $student->last_name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    /**
     * Generate report cards for all students in a senior class
     */
    public function generateSeniorClassListReportCards($classId){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $selectedTerm = Term::findOrFail($selectedTermId);
        $klass = Klass::with(['students.tests.subject', 'students.overallComments', 'students.jce', 'teacher'])
            ->where('id', $classId)
            ->where('term_id', $selectedTermId)
            ->first();

        if (!$klass) {
            abort(404, 'Class not found for the selected term.');
        }

        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);

        $activeStudents = $klass->students->filter(function ($student) use ($selectedTermId) {
            return $student->pivot->active && $student->pivot->term_id == $selectedTermId;
        });

        $studentRankings = $this->calculateClassRankingsSenior($activeStudents, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);

        $reportCardsData = [];

        foreach ($activeStudents as $student) {
            $scores = $this->calculateStudentScoresSenior($student, $selectedTermId, $classId);

            $totalSlots = 0;
            $totalPoints = 0;
            $bestSubjects = [];

            foreach ($scores as $score) {
                $slotsNeeded = $score['slotsNeeded'];
                if ($totalSlots + $slotsNeeded <= 6) {
                    $bestSubjects[] = $score;
                    $totalSlots += $slotsNeeded;
                    if ($score['is_double']) {
                        $totalPoints += $score['points'] * 2;
                    } else {
                        $totalPoints += $score['points'];
                    }
                }
                if ($totalSlots >= 6) {
                    break;
                }
            }

            $grade = $this->determineGrade($totalPoints, $klass);
            $position = $this->getStudentPosition1($studentRankings, $student->id);

            $manualEntry = $student->manualAttendanceEntries()->where('term_id', $selectedTermId)->first();
            $absentDays = $manualEntry && $manualEntry->days_absent !== null
                ? $manualEntry->days_absent
                : $student->absentDays()->where('term_id', $selectedTermId)->count();

            $overallComment = $student->overallComments->where('term_id', $selectedTermId)->first();
            $classTeacherRemarks = $overallComment->class_teacher_remarks ?? 'No remarks provided.';
            $headTeachersRemarks = $overallComment->school_head_remarks ?? 'No remarks provided.';

            $reportCardsData[] = [
                'student' => $student,
                'currentClass' => $klass,
                'scores' => $scores,
                'bestSubjects' => $bestSubjects,
                'totalPoints' => $totalPoints,
                'grade' => $grade,
                'position' => $position,
                'classAverage' => round($classAverage, 2),
                'nextTermStartDate' => $nextTermStartDate,
                'absentDays' => $absentDays,
                'classTeacherRemarks' => $classTeacherRemarks,
                'headTeachersRemarks' => $headTeachersRemarks,
                'school_fees' => $manualEntry ? $manualEntry->school_fees_owing : null,
                'otherInfo' => $manualEntry ? $manualEntry->other_info : null,
            ];
        }

        $data = [
            'reportCards' => $reportCardsData,
            'school_setup' => $school_setup,
            'school_head' => $school_head,
        ];

        $pdf = PDF::loadView('assessment.senior.report-card-class-email-senior', $data);
        return $pdf->stream('class-report-cards.pdf');
    }

    /**
     * Email report cards to all students in a senior class
     */
    public function generateEmailSeniorClassListReportCards($classId){
        $klass = Klass::with(['students.tests.subject', 'students.overallComments', 'students.jce', 'teacher'])->findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $school_setup = SchoolSetup::first();
        $school_head = User::where('position', 'School Head')->first();
        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);

        $studentRankings = $this->calculateClassRankingsSenior($klass->students, $selectedTermId);
        $classAverage = $this->calculateClassAverageFromRankings($studentRankings);

        $reportCardsData = [];

        foreach ($klass->students as $student) {
            if (!$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                continue;
            }

            $scores = $this->calculateStudentScoresSenior($student, $selectedTermId);
            $totalPoints = array_sum(array_column(array_slice($scores, 0, 6), 'points'));
            $grade = $this->determineGrade($totalPoints, $klass);
            $position = $this->getStudentPosition($studentRankings, $student->id);

            $manualEntry = $student->manualAttendanceEntries()->where('term_id', $selectedTermId)->first();
            $absentDays = $manualEntry && $manualEntry->days_absent !== null
                ? $manualEntry->days_absent
                : $student->absentDays()->where('term_id', $selectedTermId)->count();

            $reportCardsData[] = [
                'student' => $student,
                'currentClass' => $klass,
                'scores' => $scores,
                'totalPoints' => $totalPoints,
                'grade' => $grade,
                'position' => $position,
                'classAverage' => round($classAverage, 2),
                'nextTermStartDate' => $nextTermStartDate,
                'absentDays' => $absentDays,
                'classTeacherRemarks' => $student->overallComments->where('term_id', $selectedTermId)->first()->class_teacher_remarks ?? 'No remarks provided.',
                'headTeachersRemarks' => $student->overallComments->where('term_id', $selectedTermId)->first()->school_head_remarks ?? 'No remarks provided.',
                'school_fees' => $manualEntry ? $manualEntry->school_fees_owing : null,
                'otherInfo' => $manualEntry ? $manualEntry->other_info : null,
            ];
        }

        $data = [
            'reportCards' => $reportCardsData,
            'school_setup' => $school_setup,
            'school_head' => $school_head,
        ];

        $pdf = PDF::loadView('assessment.senior.report-card-class-email-senior', $data);
        $filename = strtolower($klass->name . '_term_' . $currentTerm->term . '_report_card.pdf');
        $pdf->setOptions(['filename' => $filename]);
        return $pdf;
    }

    /**
     * Get student position/rank for senior school
     */
    protected function getStudentPositionSenior($studentRankings, $studentId)
    {
        foreach ($studentRankings as $index => $student) {
            if ($student['studentId'] == $studentId) {
                return $index + 1;
            }
        }
        return 'N/A';
    }

    /**
     * Get student position (alternate method)
     */
    protected function getStudentPosition1($studentRankings, $studentId)
    {
        foreach ($studentRankings as $index => $student) {
            if ((string)$student['studentId'] === (string)$studentId) {
                return $index + 1;
            }
        }
        return 'N/A';
    }

    /**
     * Calculate class rankings for senior school
     */
    protected function calculateClassRankingsSenior($students, $selectedTermId){
        $studentTotals = [];

        foreach ($students as $student) {
            if (!$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                continue;
            }

            $scores = $this->calculateStudentScoresSenior($student, $selectedTermId);
            $totalSlots = 0;
            $totalPoints = 0;

            foreach ($scores as $score) {
                $slotsNeeded = $score['slotsNeeded'];
                if ($totalSlots + $slotsNeeded <= 6) {
                    $totalSlots += $slotsNeeded;
                    if ($score['is_double']) {
                        $totalPoints += $score['points'] * 2;
                    } else {
                        $totalPoints += $score['points'];
                    }
                }
                if ($totalSlots >= 6) {
                    break;
                }
            }

            $studentTotals[] = [
                'studentId' => (string)$student->id,
                'totalPoints' => $totalPoints,
            ];
        }

        usort($studentTotals, function ($a, $b) {
            return $b['totalPoints'] <=> $a['totalPoints'];
        });

        return $studentTotals;
    }

    /**
     * Calculate student scores for senior school
     */
    protected function calculateStudentScoresSenior($student, $selectedTermId, $classId = null)
    {
        $scores = [];
        $subjects = $student->tests->where('term_id', $selectedTermId)->pluck('subject')->unique('id');
        $jceGrades = $student->jce ? $student->jce->toArray() : [];
        $overallJceGrade = $jceGrades['overall'] ?? null;

        if (!$classId) {
            $currentClass = $student->currentClass();
            $classId = $currentClass ? $currentClass->id : null;
        }

        foreach ($subjects as $subject) {
            $examTest = $student->tests->where('grade_subject_id', $subject->id)
                ->where('type', 'Exam')
                ->where('term_id', $selectedTermId)
                ->first();
            $caTest = $student->tests->where('grade_subject_id', $subject->id)
                ->where('type', 'CA')
                ->where('term_id', $selectedTermId)
                ->first();
            $subjectComment = $student->subjectComments->where('grade_subject_id', $subject->id)
                ->where('term_id', $selectedTermId)
                ->first();

            $teacherName = 'N/A';
            if ($classId) {
                $klassSubject = KlassSubject::where('grade_subject_id', $subject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('klass_id', $classId)
                    ->first();

                if ($klassSubject && $klassSubject->user_id) {
                    $teacher = User::find($klassSubject->user_id);
                    $teacherName = $teacher ? $teacher->lastname : 'N/A';
                } else {
                    $studentOptionalSubject = DB::table('student_optional_subjects')
                        ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
                        ->where('student_optional_subjects.student_id', $student->id)
                        ->where('student_optional_subjects.term_id', $selectedTermId)
                        ->where('student_optional_subjects.klass_id', $classId)
                        ->where('optional_subjects.grade_subject_id', $subject->id)
                        ->first();

                    if ($studentOptionalSubject && $studentOptionalSubject->user_id) {
                        $teacher = User::find($studentOptionalSubject->user_id);
                        $teacherName = $teacher ? $teacher->lastname : 'N/A';
                    }
                }
            }

            $subjectName = strtolower($subject->subject->name);
            $jceGrade = $jceGrades[$subjectName] ?? $overallJceGrade;

            $points = $this->getSubjectPoints($student, $subject, $selectedTermId);
            $isDouble = (bool) $subject->subject->is_double;
            $slotsNeeded = $isDouble ? 2 : 1;

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'is_double' => $isDouble,
                'points' => $points,
                'slotsNeeded' => $slotsNeeded,
                'score' => $examTest ? $examTest->pivot->score : 0,
                'percentage' => $examTest ? $examTest->pivot->percentage : 0,
                'grade' => $examTest ? $examTest->pivot->grade : '',
                'comments' => $subjectComment ? $subjectComment->remarks : 'N/A',
                'caAverage' => $caTest ? $caTest->pivot->avg_score : 0,
                'caAverageGrade' => $caTest ? $caTest->pivot->avg_grade : '',
                'jceGrade' => $jceGrade,
                'isOverallJceGrade' => !isset($jceGrades[$subjectName]) && $jceGrade !== null,
                'teacher' => $teacherName,
            ];
        }
        usort($scores, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });
        return $scores;
    }

    /**
     * Generate subject analysis report for senior school
     */

     public function generateSubjectAnalysisReportSenior(Request $request, $classId, $sequenceId, $type){
        $klass = Klass::with(['students.tests.subject'])->findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $subjectData = [];
        $totalStudents = $klass->students->count();
        $test = Test::where('type', $type)->where('term_id', $selectedTermId)->where('sequence', $sequenceId)->first();

        foreach ($klass->students as $student) {
            $scores = $this->calculateStudentScoresSeniorCA($student, $selectedTermId, $type, $sequenceId);
            foreach ($scores as $score) {
                $subject = $score['subject'];
                if (!isset($subjectData[$subject])) {
                    $subjectData[$subject] = [
                        'A*' => ['M' => 0, 'F' => 0],
                        'A' => ['M' => 0, 'F' => 0],
                        'B' => ['M' => 0, 'F' => 0],
                        'C' => ['M' => 0, 'F' => 0],
                        'D' => ['M' => 0, 'F' => 0],
                        'E' => ['M' => 0, 'F' => 0],
                        'F' => ['M' => 0, 'F' => 0],
                        'G' => ['M' => 0, 'F' => 0],
                        'U' => ['M' => 0, 'F' => 0],
                        'TOTAL' => ['M' => 0, 'F' => 0]
                    ];
                }

                $gender = $student->gender === 'M' ? 'M' : 'F';
                $grade = $score['grade'];
                $isDouble = $score['is_double'] ?? false;

                if ($subject && $grade && $gender) {
                    if ($isDouble && strlen($grade) == 2) {
                        // Double subject: count each grade letter separately
                        foreach ([$grade[0], $grade[1]] as $g) {
                            if (isset($subjectData[$subject][$g][$gender])) {
                                $subjectData[$subject][$g][$gender]++;
                            }
                        }
                        $subjectData[$subject]['TOTAL'][$gender] += 2;
                    } else {
                        // Single subject (or double with identical grades collapsed)
                        if (strlen($grade) == 2 && $grade[0] == $grade[1]) {
                            $grade = $grade[0];
                        }
                        if (isset($subjectData[$subject][$grade][$gender])) {
                            $subjectData[$subject][$grade][$gender]++;
                        }
                        $subjectData[$subject]['TOTAL'][$gender]++;
                    }
                } else {
                    $subjectData[$subject]['TOTAL'][$gender]++;
                }
            }
        }

        $report = [];
        foreach ($subjectData as $subject => $grades) {
            $totalStudents = $grades['TOTAL']['M'] + $grades['TOTAL']['F'];
            $creditPercentage = ($grades['A*']['M'] + $grades['A*']['F'] + $grades['A']['M'] + $grades['A']['F'] + $grades['B']['M'] + $grades['B']['F']) / $totalStudents * 100;
            $passPercentage = ($grades['A*']['M'] + $grades['A*']['F'] + $grades['A']['M'] + $grades['A']['F'] + $grades['B']['M'] + $grades['B']['F'] + $grades['C']['M'] + $grades['C']['F']) / $totalStudents * 100;

            $report[] = [
                'SUBJECT' => $subject,
                'A*' => $grades['A*'],
                'A' => $grades['A'],
                'B' => $grades['B'],
                'C' => $grades['C'],
                'CREDIT %' => round($creditPercentage, 1),
                'D' => $grades['D'],
                'E' => $grades['E'],
                'PASS %' => round($passPercentage, 1),
                'F' => $grades['F'],
                'G' => $grades['G'],
                'U' => $grades['U'],
                'TOTAL' => $grades['TOTAL']
            ];
        }

        usort($report, function ($a, $b) {
            return $b['CREDIT %'] <=> $a['CREDIT %'];
        });

        foreach ($report as $index => $subject) {
            $report[$index]['POSITION'] = $index + 1;
        }

        $totals = array_reduce($report, function ($carry, $item) {
            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    $carry[$key]['M'] = ($carry[$key]['M'] ?? 0) + $value['M'];
                    $carry[$key]['F'] = ($carry[$key]['F'] ?? 0) + $value['F'];
                } elseif (is_numeric($value)) {
                    $carry[$key] = ($carry[$key] ?? 0) + $value;
                }
            }
            return $carry;
        }, []);

        $totalStudents = $totals['TOTAL']['M'] + $totals['TOTAL']['F'];
        $totals['CREDIT %'] = round(($totals['A*']['M'] + $totals['A*']['F'] + $totals['A']['M'] + $totals['A']['F'] + $totals['B']['M'] + $totals['B']['F']) / $totalStudents * 100, 1);
        $totals['PASS %'] = round(($totals['A*']['M'] + $totals['A*']['F'] + $totals['A']['M'] + $totals['A']['F'] + $totals['B']['M'] + $totals['B']['F'] + $totals['C']['M'] + $totals['C']['F']) / $totalStudents * 100, 1);

        $exportData = [
            'className' => $klass->name,
            'type' => $type,
            'report' => $report,
            'totals' => $totals,
            'test' => $test,
        ];

        if ($request->query('export') === 'excel') {
            return Excel::download(new \App\Exports\ClassSubjectAnalysisExport($exportData), 'subject_analysis_report.xlsx');
        }

        return view('assessment.senior.ca-class-subjects-analysis-senior', [
            'report' => $report,
            'totals' => $totals,
            'className' => $klass->name,
            'termName' => $currentTerm->name,
            'sequenceId' => $sequenceId,
            'type' => $type,
            'test' => $test,
            'school_data' => SchoolSetup::first(),
        ]);
    }

    /**
     * Generate grade subject analysis report for senior school
     */
    public function generateGradeSubjectAnalysisReportSenior(Request $request, $classId, $sequenceId, $type){
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade->id;
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $test = Test::where('type', $type)->where('term_id', $selectedTermId)->where('grade_id', $gradeId)->where('sequence', $sequenceId)->first();

        $students = Student::whereHas('studentTerms', function ($query) use ($gradeId, $selectedTermId) {
            $query->where('term_id', $selectedTermId)
                ->where('grade_id', $gradeId)
                ->where('status', 'Current');
        })->get();

        $gradesList = ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'TOTAL'];
        $creditGrades = ['A*', 'A', 'B'];
        $passGrades = ['A*', 'A', 'B', 'C'];

        $subjectData = [];

        $gradeMapping = [
            'AA*' => 'A*',
            'AA'  => 'A',
            'BB'  => 'B',
            'CC'  => 'C',
            'DD'  => 'D',
            'EE'  => 'E',
            'FF'  => 'F',
            'GG'  => 'G',
            'UU'  => 'U'
        ];

        foreach ($students as $student) {
            $scores = $this->calculateStudentScoresSeniorCA($student, $selectedTermId, $type, $sequenceId);
            foreach ($scores as $score) {
                $subject = $score['subject'];
                if (!isset($subjectData[$subject])) {
                    $subjectData[$subject] = [];
                    foreach ($gradesList as $gradeKey) {
                        $subjectData[$subject][$gradeKey] = ['M' => 0, 'F' => 0];
                    }
                }
                $gender = $student->gender === 'M' ? 'M' : 'F';
                $grade = $score['grade'];
                $isDouble = $score['is_double'] ?? false;

                if ($isDouble && strlen($grade) == 2) {
                    // Double subject: count each grade letter separately
                    foreach ([$grade[0], $grade[1]] as $g) {
                        if (in_array($g, $gradesList)) {
                            $subjectData[$subject][$g][$gender]++;
                        }
                    }
                    $subjectData[$subject]['TOTAL'][$gender] += 2;
                } else {
                    if (isset($gradeMapping[$grade])) {
                        $grade = $gradeMapping[$grade];
                    }
                    if (in_array($grade, $gradesList)) {
                        $subjectData[$subject][$grade][$gender]++;
                        $subjectData[$subject]['TOTAL'][$gender]++;
                    }
                }
            }
        }

        $report = [];
        foreach ($subjectData as $subject => $grades) {
            $totalStudents = $grades['TOTAL']['M'] + $grades['TOTAL']['F'];

            $creditCount = 0;
            foreach ($creditGrades as $gradeKey) {
                $creditCount += $grades[$gradeKey]['M'] + $grades[$gradeKey]['F'];
            }
            $creditPercentage = $totalStudents > 0 ? ($creditCount / $totalStudents * 100) : 0;

            $passCount = 0;
            foreach ($passGrades as $gradeKey) {
                $passCount += $grades[$gradeKey]['M'] + $grades[$gradeKey]['F'];
            }
            $passPercentage = $totalStudents > 0 ? ($passCount / $totalStudents * 100) : 0;

            $reportItem = [
                'SUBJECT' => $subject,
            ];

            foreach ($gradesList as $gradeKey) {
                $reportItem[$gradeKey] = $grades[$gradeKey];
            }

            $reportItem['CREDIT %'] = round($creditPercentage, 1);
            $reportItem['PASS %'] = round($passPercentage, 1);

            $report[] = $reportItem;
        }

        usort($report, function ($a, $b) {
            return $b['CREDIT %'] <=> $a['CREDIT %'];
        });

        foreach ($report as $index => $subject) {
            $report[$index]['POSITION'] = $index + 1;
        }

        $totals = array_reduce($report, function ($carry, $item) use ($gradesList) {
            foreach ($gradesList as $key) {
                if (isset($item[$key])) {
                    if (is_array($item[$key])) {
                        $carry[$key]['M'] = ($carry[$key]['M'] ?? 0) + $item[$key]['M'];
                        $carry[$key]['F'] = ($carry[$key]['F'] ?? 0) + $item[$key]['F'];
                    }
                }
            }
            return $carry;
        }, []);

        $totalStudents = $totals['TOTAL']['M'] + $totals['TOTAL']['F'];

        $creditCount = 0;
        foreach ($creditGrades as $gradeKey) {
            $creditCount += $totals[$gradeKey]['M'] + $totals[$gradeKey]['F'];
        }
        $totals['CREDIT %'] = $totalStudents > 0 ? round($creditCount / $totalStudents * 100, 1) : 0;

        $passCount = 0;
        foreach ($passGrades as $gradeKey) {
            $passCount += $totals[$gradeKey]['M'] + $totals[$gradeKey]['F'];
        }
        $totals['PASS %'] = $totalStudents > 0 ? round($passCount / $totalStudents * 100, 1) : 0;

        $exportData = [
            'gradeName' => $klass->grade->name,
            'type' => $type,
            'report' => $report,
            'totals' => $totals,
            'testName' => $test,
        ];

        if ($request->query('export') === 'excel') {
            return Excel::download(new \App\Exports\GradeSubjectAnalysisExport($exportData), 'grade_subject_analysis_report.xlsx');
        }

        return view('assessment.senior.ca-grade-subjects-analysis-senior', [
            'report' => $report,
            'totals' => $totals,
            'gradeName' => $klass->grade->name,
            'termName' => $currentTerm->name,
            'sequenceId' => $sequenceId,
            'type' => $type,
            'test' => $test,
            'school_data' => SchoolSetup::first(),
        ]);
    }

    /**
     * Generate student list for senior grade
     */
    public function generateSeniorGradeStudentList($classId, $type, $sequenceId){
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade->id;
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);

        $students = Student::whereHas('studentTerms', function ($query) use ($gradeId, $selectedTermId) {
            $query->where('term_id', $selectedTermId)
                ->where('grade_id', $gradeId)
                ->where('status', 'Current');
        })->with('jce')->get();

        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->whereHas('tests', function ($query) use ($sequenceId) {
                $query->where('sequence', '<=', $sequenceId);
            })
            ->whereHas('tests.students', function ($query) use ($type) {
                $this->applySeniorAssessmentStudentScoreFilter($query, $type);
            })
            ->with(['subject'])
            ->get();

        $allSubjects = $allGradeSubjects->pluck('subject')->map(function ($subject) {
            return [
                'name' => $subject->name,
                'abbrev' => $subject->abbrev
            ];
        })->toArray();

        $studentData = [];
        $subjectAnalysis = [
            'A*' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'A' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'B' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'C' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'D' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'E' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'F' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'G' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'U' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'Total' => 0,
            'A*AB' => ['Total' => 0, 'Percentage' => 0],
            'A*ABC' => ['Total' => 0, 'Percentage' => 0]
        ];

        $jceSubjectAnalysis = [
            'A' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'B' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'C' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'D' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'E' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'F' => ['Total' => 0, 'Percentage' => 0, 'Male' => 0, 'Female' => 0],
            'AB%' => 0,
            'ABC%' => 0,
            'Total' => 0
        ];

        foreach ($students as $student) {
            $studentClass = $student->currentClass();

            if (!$studentClass) {
                continue;
            }

            $subjectScores = [];
            $allPoints = [];
            $creditsCount = 0;

            foreach ($allGradeSubjects as $gradeSubject) {
                $subjectScore = $this->calculateSubjectScoresAnalysis($student, $gradeSubject, $selectedTermId, $gradeId, $type, $sequenceId);

                $subject = $gradeSubject->subject->name;

                if ($subjectScore['score'] !== null) {
                    $subjectScores[$subject] = [
                        'percentage' => $subjectScore['percentage'],
                        'grade' => $subjectScore['grade']
                    ];

                    // Count credits
                    $grade = $subjectScores[$subject]['grade'];
                    if (in_array($grade, ['A*', 'A', 'B', 'C'])) {
                        $creditsCount++;
                    } elseif (strlen($grade) == 2) {
                        // For potential double grades, count each qualifying grade
                        if (in_array($grade[0], ['A', 'B', 'C'])) $creditsCount++;
                        if (in_array($grade[1], ['A', 'B', 'C'])) $creditsCount++;
                    }

                    // Update subject analysis
                    $singleGrade = (strlen($grade) == 2 && $grade[0] == $grade[1]) ? $grade[0] : $grade;
                    if (isset($subjectAnalysis[$singleGrade])) {
                        $subjectAnalysis[$singleGrade]['Total']++;
                        $subjectAnalysis['Total']++;

                        if ($student->gender === 'M') {
                            $subjectAnalysis[$singleGrade]['Male']++;
                        } elseif ($student->gender === 'F') {
                            $subjectAnalysis[$singleGrade]['Female']++;
                        }

                        // Double count for Double Science in analysis
                        if ($subject === 'Double Science') {
                            $subjectAnalysis[$singleGrade]['Total']++;
                            $subjectAnalysis['Total']++;
                            if ($student->gender === 'M') {
                                $subjectAnalysis[$singleGrade]['Male']++;
                            } elseif ($student->gender === 'F') {
                                $subjectAnalysis[$singleGrade]['Female']++;
                            }
                        }
                    }

                    // Handle points
                    if ($subject === 'Double Science') {
                        $allPoints[] = $subjectScore['points'];
                        $allPoints[] = $subjectScore['points'];
                    } else {
                        $allPoints[] = $subjectScore['points'];
                    }
                } else {
                    $subjectScores[$subject] = [
                        'percentage' => '-',
                        'grade' => '-'
                    ];
                }
            }

            // Count grades for JCE subjects
            if ($student->jce) {
                foreach ($student->jce->toArray() as $subject => $grade) {
                    if (in_array($subject, ['overall', 'student_id', 'created_at', 'updated_at'])) {
                        continue; // Skip non-subject fields
                    }

                    if (isset($jceSubjectAnalysis[$grade])) {
                        $jceSubjectAnalysis[$grade]['Total']++;
                        $jceSubjectAnalysis['Total']++;

                        if ($student->gender === 'M') {
                            $jceSubjectAnalysis[$grade]['Male']++;
                        } elseif ($student->gender === 'F') {
                            $jceSubjectAnalysis[$grade]['Female']++;
                        }
                    }
                }
            }

            // Sort points in descending order and take the best 6
            rsort($allPoints);
            $totalPoints = array_sum(array_slice($allPoints, 0, 6));

            $studentData[] = [
                'name' => $student->full_name,
                'class' => $studentClass->name,
                'gender' => $student->gender,
                'jce' => $student->jce ? $student->jce->overall : '-',
                'subjects' => $subjectScores,
                'totalPoints' => $totalPoints,
                'creditCount' => $creditsCount
            ];
        }

        // Calculate percentages for Grade Subjects and JCE Subjects
        foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade) {
            if (isset($subjectAnalysis[$grade])) {
                $subjectAnalysis[$grade]['Percentage'] = ($subjectAnalysis['Total'] > 0) ?
                    ($subjectAnalysis[$grade]['Total'] / $subjectAnalysis['Total']) * 100 : 0;
            }
        }

        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade) {
            $jceSubjectAnalysis[$grade]['Percentage'] = ($jceSubjectAnalysis['Total'] > 0) ?
                ($jceSubjectAnalysis[$grade]['Total'] / $jceSubjectAnalysis['Total']) * 100 : 0;
        }

        // Calculate A*AB% and A*ABC% for Grade Subjects
        $subjectAnalysis['A*AB']['Total'] = $subjectAnalysis['A*']['Total'] + $subjectAnalysis['A']['Total'] + $subjectAnalysis['B']['Total'];
        $subjectAnalysis['A*AB']['Percentage'] = ($subjectAnalysis['Total'] > 0) ?
            ($subjectAnalysis['A*AB']['Total'] / $subjectAnalysis['Total']) * 100 : 0;

        $subjectAnalysis['A*ABC']['Total'] = $subjectAnalysis['A*AB']['Total'] + $subjectAnalysis['C']['Total'];
        $subjectAnalysis['A*ABC']['Percentage'] = ($subjectAnalysis['Total'] > 0) ?
            ($subjectAnalysis['A*ABC']['Total'] / $subjectAnalysis['Total']) * 100 : 0;

        // Calculate AB% and ABC% for JCE subjects
        $jceSubjectAnalysis['AB%'] = ($jceSubjectAnalysis['Total'] > 0) ?
            (($jceSubjectAnalysis['A']['Total'] + $jceSubjectAnalysis['B']['Total']) / $jceSubjectAnalysis['Total']) * 100 : 0;
        $jceSubjectAnalysis['ABC%'] = ($jceSubjectAnalysis['Total'] > 0) ?
            (($jceSubjectAnalysis['A']['Total'] + $jceSubjectAnalysis['B']['Total'] + $jceSubjectAnalysis['C']['Total']) / $jceSubjectAnalysis['Total']) * 100 : 0;

        // Sort students by total points (descending) and assign positions
        usort($studentData, function ($a, $b) {
            return $b['totalPoints'] - $a['totalPoints'];
        });

        foreach ($studentData as $position => $data) {
            $studentData[$position]['position'] = $position + 1;
        }

        return view('assessment.senior.ca-grade-analysis-senior', [
            'gradeName' => $klass->grade->name,
            'students' => $studentData,
            'allSubjects' => $allSubjects,
            'subjectAnalysis' => $subjectAnalysis,
            'jceSubjectAnalysis' => $jceSubjectAnalysis
        ]);
    }

    /**
     * Generate CA analysis for senior school
     */
    #End of Junior schools analysis reports - Analysis Senior 1
    public function generateCAAnalysisSenior(Request $request, $classId, $sequenceId, $type){
        $klass = Klass::with(['students.tests.subject', 'students.jce', 'grade'])->findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
    
        $school_setup = SchoolSetup::first();
        $allGradeSubjects = GradeSubject::where('grade_id', $klass->grade_id)
            ->where('term_id', $selectedTermId)
            ->where('active', true)
            ->whereHas('tests', function ($query) use ($sequenceId) {
                $query->where('sequence', $sequenceId);
            })
            ->whereHas('tests.students', function ($query) use ($type) {
                $this->applySeniorAssessmentStudentScoreFilter($query, $type);
            })
            ->with(['subject', 'tests' => function ($query) use ($sequenceId, $type) {
                $query->where('sequence', $sequenceId)
                    ->with(['students' => function ($query) use ($type) {
                        $this->applySeniorAssessmentStudentScoreFilter($query, $type);
                    }]);
            }])->get();
    
        $caTest = Test::where('term_id', $selectedTermId)
            ->where('type', $type)
            ->where('sequence', $sequenceId)
            ->first();

        $allSubjects = $allGradeSubjects->pluck('subject.name')->unique()->sort()->values()->toArray();
        $doubleSubjects = $allGradeSubjects
            ->filter(fn($gs) => $gs->subject->is_double)
            ->pluck('subject.name')
            ->unique()
            ->toArray();
        $studentData = [];
        $jceSubjects = [
            'mathematics',
            'english',
            'science',
            'setswana',
            'design_and_technology',
            'home_economics',
            'agriculture',
            'social_studies',
            'moral_education',
            'music',
            'physical_education',
            'art',
            'office_procedures',
            'accounting',
            'french'
        ];
    
        $jceAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'B' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'C' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'D' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'E' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'F' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'Total' => 0,
            'AB%' => 0,
            'ABC%' => 0
        ];
    
        $subjectAnalysis = [
            'A*' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'A' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'B' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'C' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'D' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'E' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'F' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'G' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'U' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'Total' => 0,
            'A*AB%' => 0,
            'A*ABC%' => 0
        ];
    
        foreach ($klass->students as $index => $student) {
            if (!$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                continue;
            }

            // Normalize gender to M/F to avoid undefined array key errors
            $studentGender = $student->gender === 'M' ? 'M' : 'F';

            $scores = $this->calculateStudentScoresSeniorCA($student, $selectedTermId, $type, $sequenceId);
            $test = collect($scores)->first();
            $subjectScores = [];
            $totalPoints = 0;
            $creditsCount = 0;

            if ($student->jce) {
                foreach ($jceSubjects as $subject) {
                    $jceGrade = $student->jce->{$subject} ?? null;
                    if ($jceGrade && array_key_exists($jceGrade, $jceAnalysis) && isset($jceAnalysis[$jceGrade][$studentGender])) {
                        $jceAnalysis[$jceGrade][$studentGender]++;
                        $jceAnalysis[$jceGrade]['Total']++;
                        $jceAnalysis['Total']++;
                    }
                }
            }
    
            $enrolledSubjects = $this->getStudentEnrolledSubjects($student, $klass, $selectedTermId);
            foreach ($allSubjects as $subject) {
                $score = collect($scores)->firstWhere('subject', $subject);
                $isEnrolled = in_array($subject, $enrolledSubjects);
                $isDoubleSubject = in_array($subject, $doubleSubjects);
                if ($score) {
                    $rawGrade = trim($score['grade']);
                    $subjectScores[$subject] = [
                        'score' => $score['score'],
                        'percentage' => $score['percentage'],
                        'grade' => $rawGrade,
                        'display_grade' => $this->formatDoubleAwardGrade($rawGrade, $isDoubleSubject),
                        'points' => $score['points'],
                    ];
                } elseif ($isEnrolled) {
                    $subjectScores[$subject] = [
                        'score' => 'X',
                        'percentage' => 'X',
                        'grade' => 'X',
                        'display_grade' => 'X',
                        'points' => 0,
                    ];
                } else {
                    $subjectScores[$subject] = [
                        'score' => '-',
                        'percentage' => '-',
                        'grade' => '-',
                        'display_grade' => '-',
                        'points' => 0,
                    ];
                }

                $grade = $subjectScores[$subject]['grade'];
                $grade = trim($grade);
                if ($grade !== 'X' && $grade !== '-') {
                    if (in_array($subject, $doubleSubjects) && strlen($grade) == 2) {
                        if (in_array($grade[0], ['A', 'B', 'C'])) $creditsCount++;
                        if (in_array($grade[1], ['A', 'B', 'C'])) $creditsCount++;
                    } elseif (in_array($grade, ['A*', 'A', 'B', 'C'])) {
                        $creditsCount++;
                    }

                    if (strlen($grade) == 2 && $grade[0] == $grade[1]) {
                        $grade = trim($grade[0]);
                    }

                    // Ensure grade is trimmed and exists in subjectAnalysis before accessing
                    $grade = trim($grade);
                    if (array_key_exists($grade, $subjectAnalysis) && isset($subjectAnalysis[$grade][$studentGender])) {
                        $subjectAnalysis[$grade][$studentGender]++;
                        $subjectAnalysis[$grade]['Total']++;
                        $subjectAnalysis['Total']++;

                        if (in_array($subject, $doubleSubjects) && strlen($subjectScores[$subject]['grade']) == 2) {
                            $subjectAnalysis[$grade][$studentGender]++;
                            $subjectAnalysis[$grade]['Total']++;
                            $subjectAnalysis['Total']++;
                        }
                    }
                }
            }

            // Best-6 total points with double subject slot awareness
            $sorted = collect($subjectScores)->sortByDesc('points');
            $totalSlots = 0;
            $totalPoints = 0;
            foreach ($sorted as $subjectName => $data) {
                $slotsNeeded = in_array($subjectName, $doubleSubjects) ? 2 : 1;
                if ($totalSlots + $slotsNeeded <= 6) {
                    $totalSlots += $slotsNeeded;
                    $totalPoints += in_array($subjectName, $doubleSubjects) ? $data['points'] * 2 : $data['points'];
                }
                if ($totalSlots >= 6) break;
            }
            $overallGrade = $this->determineGrade($totalPoints, $klass);
    
            $studentData[] = [
                'index' => $index + 1,
                'name' => $student->full_name,
                'class' => $klass->name,
                'gender' => $student->gender,
                'jce' => $student->jce->overall ?? '-',
                'subjects' => $subjectScores,
                'totalPoints' => $totalPoints,
                'grade' => $overallGrade,
                'credits' => $creditsCount,
            ];
        }
    
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade) {
            $jceAnalysis[$grade]['%'] = $jceAnalysis['Total'] > 0 ?
                round(($jceAnalysis[$grade]['Total'] / $jceAnalysis['Total']) * 100, 2) : 0;
        }
        $jceAnalysis['AB%'] = $jceAnalysis['Total'] > 0 ?
            round((($jceAnalysis['A']['Total'] + $jceAnalysis['B']['Total']) / $jceAnalysis['Total']) * 100, 2) : 0;
        $jceAnalysis['ABC%'] = $jceAnalysis['Total'] > 0 ?
            round((($jceAnalysis['A']['Total'] + $jceAnalysis['B']['Total'] + $jceAnalysis['C']['Total']) / $jceAnalysis['Total']) * 100, 2) : 0;
    
        foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade) {
            $subjectAnalysis[$grade]['%'] = $subjectAnalysis['Total'] > 0 ?
                round(($subjectAnalysis[$grade]['Total'] / $subjectAnalysis['Total']) * 100, 2) : 0;
        }
        $subjectAnalysis['A*AB%'] = $subjectAnalysis['Total'] > 0 ?
            round((($subjectAnalysis['A*']['Total'] + $subjectAnalysis['A']['Total'] + $subjectAnalysis['B']['Total']) / $subjectAnalysis['Total']) * 100, 2) : 0;
        $subjectAnalysis['A*ABC%'] = $subjectAnalysis['Total'] > 0 ?
            round((($subjectAnalysis['A*']['Total'] + $subjectAnalysis['A']['Total'] + $subjectAnalysis['B']['Total'] + $subjectAnalysis['C']['Total']) / $subjectAnalysis['Total']) * 100, 2) : 0;
    
        usort($studentData, function ($a, $b) {
            return $b['totalPoints'] <=> $a['totalPoints'];
        });
    
        foreach ($studentData as $position => $data) {
            $studentData[$position]['position'] = $position + 1;
        }
    
        $exportData = [
            'className' => $klass->name,
            'test' => $caTest,
            'jceAnalysis' => $jceAnalysis,
            'subjectAnalysis' => $subjectAnalysis,
            'students' => $studentData,
            'allSubjects' => $allSubjects,
            'doubleSubjects' => $doubleSubjects,
            'type' => $type,
        ];

        if ($request->query('export') === 'excel') {
            return Excel::download(new \App\Exports\ClassListAnalysisExport($exportData), 'class_list_analysis.xlsx');
        }

        return view('assessment.senior.ca-class-analysis-senior', [
            'className' => $klass->name,
            'students' => $studentData,
            'allSubjects' => $allSubjects,
            'doubleSubjects' => $doubleSubjects,
            'sequenceId' => $sequenceId,
            'type' => $type,
            'test' => $caTest,
            'school_setup' => $school_setup,
            'jceAnalysis' => $jceAnalysis,
            'subjectAnalysis' => $subjectAnalysis,
            'school_data' => SchoolSetup::first(),
        ]);
    }

    /**
     * Generate credits report for senior school
     */
    public function generateSeniorCreditsReport(Request $request, $classId, $sequenceId,$type){
        $klass = Klass::with('students', 'students.jce', 'teacher')->findOrFail($classId);
        $gradeD = Grade::with('klasses')->findOrFail($klass->grade_id);

        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
        $test = Test::where('term_id', $selectedTermId)->where('type', $type)->where('grade_id', $klass->grade_id)->where('sequence', $sequenceId)->first();
        
        $classes = $gradeD->klasses()
            ->where('term_id', $selectedTermId)
            ->with(['students', 'students.jce', 'teacher'])
            ->get();
        
        $classStats = [];
        $classTeachers = [];
        $creditCategories = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0];
        $gradeTotals = [
            'total' => 0,
            'credits' => array_fill_keys($creditCategories, 0),
            'pointsGte34' => 0,
            'pointsGte46' => 0
        ];
        
        foreach ($classes as $klass) {
            $className = $klass->name;
            $classStats[$className] = [
                'total' => 0,
                'credits' => array_fill_keys($creditCategories, 0),
                'pointsGte34' => 0,
                'pointsGte46' => 0
            ];
            
            $classTeachers[$className] = $klass->teacher ? $klass->teacher->full_name : 'No Teacher Assigned';
            
            foreach ($klass->students as $student) {
                if (!$student->pivot || !$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                    continue;
                }
                
                $scores = $this->calculateStudentScoresSeniorCA($student, $selectedTermId, $type, $sequenceId);
                
                if (empty($scores)) {
                    continue;
                }
                
                $creditsCount = 0;
                foreach ($scores as $score) {
                    $grade = trim($score['grade'] ?? '');

                    if (empty($grade)) {
                        continue;
                    }

                    $isDouble = $score['is_double'] ?? false;
                    if ($isDouble && strlen($grade) == 2) {
                        if (in_array($grade[0], ['A', 'B', 'C'])) $creditsCount++;
                        if (in_array($grade[1], ['A', 'B', 'C'])) $creditsCount++;
                    } elseif (in_array($grade, ['A*', 'A', 'B', 'C'])) {
                        $creditsCount++;
                    }
                }

                // Best-6 total points with double subject slot awareness
                $topSixPoints = 0;
                $totalSlots = 0;
                foreach ($scores as $score) {
                    $isDouble = $score['is_double'] ?? false;
                    $slotsNeeded = $isDouble ? 2 : 1;
                    if ($totalSlots + $slotsNeeded <= 6) {
                        $totalSlots += $slotsNeeded;
                        $topSixPoints += $isDouble ? ($score['points'] ?? 0) * 2 : ($score['points'] ?? 0);
                    }
                    if ($totalSlots >= 6) break;
                }
                
                // Increment student count for this class
                $classStats[$className]['total']++;
                
                // Cap credits at 10 (use array key 10 for anything 10 or higher)
                $creditsKey = min(10, $creditsCount);
                $classStats[$className]['credits'][$creditsKey]++;
                
                // Check if best 6 points are >=34 or >=46
                if ($topSixPoints >= 34) {
                    $classStats[$className]['pointsGte34']++;
                }
                if ($topSixPoints >= 46) {
                    $classStats[$className]['pointsGte46']++;
                }
                
                // Add to grade totals
                $gradeTotals['total']++;
                $gradeTotals['credits'][$creditsKey]++;
                if ($topSixPoints >= 34) {
                    $gradeTotals['pointsGte34']++;
                }
                if ($topSixPoints >= 46) {
                    $gradeTotals['pointsGte46']++;
                }
            }
        }
        
        if ($request->query('export') === 'excel') {
            return Excel::download(
                new \App\Exports\ClassCreditsPerformanceExport([
                    'gradeName' => $gradeD->name,
                    'type' => $type,
                    'test' => $test,
                    'sequence' => $sequenceId,
                    'classStats' => $classStats,
                    'classTeachers' => $classTeachers,
                    'creditCategories' => $creditCategories,
                    'gradeTotals' => $gradeTotals
                ]), 
                $gradeD->name . '_credits_performance_analysis.xlsx'
            );
        }

        return view('assessment.senior.credits-analysis-senior', [
            'gradeName' => $gradeD->name,
            'type' => $type,
            'test' => $test,
            'sequence' => $sequenceId,
            'classStats' => $classStats,
            'classTeachers' => $classTeachers,
            'creditCategories' => $creditCategories,
            'gradeTotals' => $gradeTotals,
            'school_data' => $school_setup
        ]);
    }

    /**
     * Generate ABC performance report by subject for senior
     */
    public function generateSubjectSeniorABCPerformanceReport(Request $request, $classId, $type, $sequence){
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $klass = Klass::findOrFail($classId);

        $students = Student::whereHas('terms', function ($query) use ($selectedTermId, $klass) {
            $query->where('student_term.term_id', $selectedTermId)->where('grade_id', $klass->grade_id);
        })->get();

        $test1 = Test::where('term_id',$selectedTermId)->where('type',$type)->where('grade_id',$klass->grade_id)->where('sequence',$sequence)->first();
        $school_setup = SchoolSetup::first();
        $allGradeSubjects = GradeSubject::where('grade_id', $klass->grade_id)
            ->where('term_id', $selectedTermId)
            ->where('active',true)
            ->with('subject')
            ->get();

        $subjectPerformance = [];
        foreach ($allGradeSubjects as $gradeSubject) {
            $subjectName = $gradeSubject->subject->name;
            $subjectPerformance[$subjectName] = [
                'A*' => ['M' => 0, 'F' => 0],
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'ABC' => ['M' => 0, 'F' => 0],
                'total' => ['M' => 0, 'F' => 0],
            ];
        }

        foreach ($students as $student) {
            $genderKey = $student->gender === 'M' ? 'M' : 'F';
            foreach ($allGradeSubjects as $gradeSubject) {
                $test = $student->tests()->where('term_id', $selectedTermId)
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->where('type', $type)
                    ->where('sequence', $sequence)
                    ->first();

                $grade = $test ? $test->pivot->grade : null;
                $subjectName = $gradeSubject->subject->name;

                if ($grade) {
                    $isDouble = (bool) ($gradeSubject->subject->is_double ?? false);

                    if ($isDouble && strlen($grade) == 2) {
                        // Double subject: each grade letter counted separately, total += 2
                        $subjectPerformance[$subjectName]['total'][$genderKey] += 2;
                        foreach ([$grade[0], $grade[1]] as $g) {
                            if (in_array($g, ['A*', 'A', 'B', 'C'])) {
                                $subjectPerformance[$subjectName][$g][$genderKey]++;
                            }
                        }
                    } else {
                        $subjectPerformance[$subjectName]['total'][$genderKey]++;
                        $this->handleSingleGrade($subjectPerformance[$subjectName], $grade, $genderKey);
                    }
                }
            }
        }

        foreach ($subjectPerformance as $subjectName => &$counts) {
            $abcCountMale = $counts['A*']['M'] + $counts['A']['M'] + $counts['B']['M'] + $counts['C']['M'];
            $abcCountFemale = $counts['A*']['F'] + $counts['A']['F'] + $counts['B']['F'] + $counts['C']['F'];

            $totalCountMale = $counts['total']['M'];
            $totalCountFemale = $counts['total']['F'];

            $counts['ABC']['M'] = $abcCountMale;
            $counts['ABC']['F'] = $abcCountFemale;

            $counts['ABC%'] = [
                'M' => $totalCountMale > 0 ? round(($abcCountMale / $totalCountMale) * 100, 2) : 0,
                'F' => $totalCountFemale > 0 ? round(($abcCountFemale / $totalCountFemale) * 100, 2) : 0
            ];
        }
        unset($counts);

        if ($request->query('export') === 'excel') {
            return Excel::download(new \App\Exports\SeniorSubjectABCPerformanceExport($subjectPerformance, $test1, $school_setup), 'senior_subject_abc_performance_report.xlsx');
        }

        return view('assessment.senior.grade-senior-subject-analysis', [
            'subjectPerformance' => $subjectPerformance,
            'school_data' => $school_setup,
            'test' => $test1,
        ]);
    }

    private function handleDoubleScience(&$subjectCounts, $grade, $genderKey){
        $grades = str_split($grade, 2);
        foreach ($grades as $singleGrade) {
            if (in_array($singleGrade, ['A*', 'A', 'B', 'C'])) {
                $subjectCounts[$singleGrade][$genderKey]++;
            }
        }
    }

    private function handleSingleGrade(&$subjectCounts, $grade, $genderKey){
        if (in_array($grade, ['A*', 'A', 'B', 'C'])) {
            $subjectCounts[$grade][$genderKey]++;
        }
    }

    /**
     * Generate overall grade analysis for senior school
     */
    public function generateGradeAnalysisSenior(Request $request, $classId, $sequenceId, $type){
        $klass = Klass::findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();
    
        $gradeD = Grade::findOrFail($klass->grade_id);
        $gradeId = $klass->grade_id;
    
        $classes = $gradeD->klasses()->where('term_id', $selectedTermId)->with(['students.tests.subject', 'students.jce'])->get();
        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->where('active',true)
            ->whereHas('tests', function ($query) use ($sequenceId) {
                $query->where('sequence', $sequenceId);
            })->whereHas('tests.students', function ($query) use ($type) {
                $this->applySeniorAssessmentStudentScoreFilter($query, $type);
            })->with(['subject', 'tests' => function ($query) use ($sequenceId, $type) {
                $query->where('sequence', $sequenceId)->with(['students' => function ($query) use ($type) {
                        $this->applySeniorAssessmentStudentScoreFilter($query, $type);
                    }]);
            }])->get();
    
        $caTest = Test::where('term_id', $selectedTermId)
            ->where('type', $type)
            ->where('sequence', $sequenceId)
            ->where('grade_id', $gradeId)
            ->first();
    
        $allSubjects = $allGradeSubjects->pluck('subject.name')->unique()->sort()->values()->toArray();
        $doubleSubjects = $allGradeSubjects
            ->filter(fn($gs) => $gs->subject->is_double)
            ->pluck('subject.name')
            ->unique()
            ->toArray();
        $studentData = [];
        $jceSubjects = [
            'mathematics',
            'english',
            'science',
            'setswana',
            'design_and_technology',
            'home_economics',
            'agriculture',
            'social_studies',
            'moral_education',
            'music',
            'physical_education',
            'art',
            'office_procedures',
            'accounting',
            'french'
        ];

        $jceAnalysis = [
            'A' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'B' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'C' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'D' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'E' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'F' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'Total' => 0,
            'AB%' => 0,
            'ABC%' => 0
        ];

        $subjectAnalysis = [
            'A*' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'A' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'B' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'C' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'D' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'E' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'F' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'G' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'U' => ['M' => 0, 'F' => 0, 'Total' => 0, '%' => 0],
            'Total' => 0,
            'A*AB%' => 0,
            'A*ABC%' => 0
        ];

        $classStats = [];
        $totalIndex = 0;
    
        foreach ($classes as $classItem) {
            $classCredits = 0;
            $classTotalPoints = 0;
            $classStudentCount = 0;
            
            foreach ($classItem->students as $student) {
                if (!$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                    continue;
                }
                
                $totalIndex++;
                $classStudentCount++;
                
                $gender = in_array($student->gender, ['M', 'F']) ? $student->gender : 'M';
                $scores = $this->calculateStudentScoresSeniorCA($student, $selectedTermId, $type, $sequenceId);
                $subjectScores = [];
                $totalPoints = 0;
                $creditsCount = 0;
    
                if ($student->jce) {
                    foreach ($jceSubjects as $subject) {
                        $jceGrade = $student->jce->{$subject};
                        if ($jceGrade && array_key_exists($jceGrade, $jceAnalysis)) {
                            $jceAnalysis[$jceGrade][$gender]++;
                            $jceAnalysis[$jceGrade]['Total']++;
                            $jceAnalysis['Total']++;
                        }
                    }
                }
    
                $enrolledSubjects = $this->getStudentEnrolledSubjects($student, $classItem, $selectedTermId);
                foreach ($allSubjects as $subject) {
                    $score = collect($scores)->firstWhere('subject', $subject);
                    $isEnrolled = in_array($subject, $enrolledSubjects);
                    $isDoubleSubject = in_array($subject, $doubleSubjects);

                    if ($score) {
                        $rawGrade = trim((string) $score['grade']);
                        $subjectScores[$subject] = [
                            'score' => $score['score'],
                            'percentage' => $score['percentage'],
                            'grade' => $rawGrade,
                            'display_grade' => $this->formatDoubleAwardGrade($rawGrade, $isDoubleSubject),
                            'points' => $score['points'],
                        ];
                    } elseif ($isEnrolled) {
                        $subjectScores[$subject] = [
                            'score' => 'X',
                            'percentage' => 'X',
                            'grade' => 'X',
                            'display_grade' => 'X',
                            'points' => 0,
                        ];
                    } else {
                        $subjectScores[$subject] = [
                            'score' => '-',
                            'percentage' => '-',
                            'grade' => '-',
                            'display_grade' => '-',
                            'points' => 0,
                        ];
                    }

                    $grade = $subjectScores[$subject]['grade'];
                    if ($grade !== 'X' && $grade !== '-') {
                        if (in_array($subject, $doubleSubjects) && strlen($grade) == 2) {
                            if (in_array($grade[0], ['A', 'B', 'C'])) $creditsCount++;
                            if (in_array($grade[1], ['A', 'B', 'C'])) $creditsCount++;
                        } elseif (in_array($grade, ['A*', 'A', 'B', 'C'])) {
                            $creditsCount++;
                        }

                        if (strlen($grade) == 2 && $grade[0] == $grade[1]) {
                            $grade = $grade[0];
                        }

                        if (array_key_exists($grade, $subjectAnalysis)) {
                            $subjectAnalysis[$grade][$gender]++;
                            $subjectAnalysis[$grade]['Total']++;
                            $subjectAnalysis['Total']++;

                            if (in_array($subject, $doubleSubjects) && strlen($subjectScores[$subject]['grade']) == 2) {
                                $subjectAnalysis[$grade][$gender]++;
                                $subjectAnalysis[$grade]['Total']++;
                                $subjectAnalysis['Total']++;
                            }
                        }
                    }
                }

                // Best-6 total points with double subject slot awareness
                $sorted = collect($subjectScores)->sortByDesc('points');
                $totalSlots = 0;
                $totalPoints = 0;
                foreach ($sorted as $subjectName => $data) {
                    $slotsNeeded = in_array($subjectName, $doubleSubjects) ? 2 : 1;
                    if ($totalSlots + $slotsNeeded <= 6) {
                        $totalSlots += $slotsNeeded;
                        $totalPoints += in_array($subjectName, $doubleSubjects) ? $data['points'] * 2 : $data['points'];
                    }
                    if ($totalSlots >= 6) break;
                }
                $overallGrade = $this->determineGrade($totalPoints, $classItem);
                
                $classCredits += $creditsCount;
                $classTotalPoints += $totalPoints;
    
                $studentData[] = [
                    'index' => $totalIndex,
                    'name' => $student->full_name,
                    'class' => $classItem->name,
                    'gender' => $gender, 
                    'jce' => $student->jce->overall ?? '-',
                    'subjects' => $subjectScores,
                    'totalPoints' => $totalPoints,
                    'grade' => $overallGrade,
                    'credits' => $creditsCount,
                ];
            }
            
            if ($classStudentCount > 0) {
                $classStats[$classItem->name] = [
                    'count' => $classStudentCount,
                    'averageCredits' => round($classCredits / $classStudentCount, 2),
                    'averagePoints' => round($classTotalPoints / $classStudentCount, 2)
                ];
            }
        }
    
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade) {
            $jceAnalysis[$grade]['%'] = $jceAnalysis['Total'] > 0 ?
                round(($jceAnalysis[$grade]['Total'] / $jceAnalysis['Total']) * 100, 2) : 0;
        }
        $jceAnalysis['AB%'] = $jceAnalysis['Total'] > 0 ?
            round((($jceAnalysis['A']['Total'] + $jceAnalysis['B']['Total']) / $jceAnalysis['Total']) * 100, 2) : 0;
        $jceAnalysis['ABC%'] = $jceAnalysis['Total'] > 0 ?
            round((($jceAnalysis['A']['Total'] + $jceAnalysis['B']['Total'] + $jceAnalysis['C']['Total']) / $jceAnalysis['Total']) * 100, 2) : 0;
    
        foreach (['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'] as $grade) {
            $subjectAnalysis[$grade]['%'] = $subjectAnalysis['Total'] > 0 ?
                round(($subjectAnalysis[$grade]['Total'] / $subjectAnalysis['Total']) * 100, 2) : 0;
        }
        $subjectAnalysis['A*AB%'] = $subjectAnalysis['Total'] > 0 ?
            round((($subjectAnalysis['A*']['Total'] + $subjectAnalysis['A']['Total'] + $subjectAnalysis['B']['Total']) / $subjectAnalysis['Total']) * 100, 2) : 0;
        $subjectAnalysis['A*ABC%'] = $subjectAnalysis['Total'] > 0 ?
            round((($subjectAnalysis['A*']['Total'] + $subjectAnalysis['A']['Total'] + $subjectAnalysis['B']['Total'] + $subjectAnalysis['C']['Total']) / $subjectAnalysis['Total']) * 100, 2) : 0;
    
        usort($studentData, function ($a, $b) {
            return $b['totalPoints'] <=> $a['totalPoints'];
        });
    
        foreach ($studentData as $position => $data) {
            $studentData[$position]['position'] = $position + 1;
        }
    
        $exportStudents = array_map(function($student) {
            return $student;
        }, $studentData);
    
        $exportData = [
            'gradeName' => $gradeD->name,
            'jceAnalysis' => $jceAnalysis,
            'subjectAnalysis' => $subjectAnalysis,
            'students' => $exportStudents,
            'allSubjects' => $allSubjects,
            'doubleSubjects' => $doubleSubjects,
            'type' => $type,
            'classStats' => $classStats,
            'test' => $caTest,
        ];

        if ($request->query('export') === 'excel') {
            return Excel::download(new  \App\Exports\GradeAnalysisExport($exportData), $gradeD->name . '_overall_analysis.xlsx');
        }

        return view('assessment.senior.all-grade-analysis-senior', [
            'gradeName' => $gradeD->name,
            'students' => $studentData,
            'allSubjects' => $allSubjects,
            'doubleSubjects' => $doubleSubjects,
            'sequenceId' => $sequenceId,
            'type' => $type,
            'test' => $caTest,
            'school_setup' => $school_setup,
            'jceAnalysis' => $jceAnalysis,
            'subjectAnalysis' => $subjectAnalysis,
            'classStats' => $classStats,
            'school_data' => SchoolSetup::first(),
        ]);
    }

    /**
     * Generate Triple Award Analysis report for a grade
     */
    public function generateAwardTypeAnalysis(Request $request, $classId, $sequenceId, $type, $awardType) {
        $awardTypeMap = [
            'triple' => Klass::TYPE_TRIPLE_AWARD,
            'double' => Klass::TYPE_DOUBLE_AWARD,
            'single' => Klass::TYPE_SINGLE_AWARD,
        ];
        $klassType = $awardTypeMap[$awardType] ?? abort(404);
        $awardLabel = $klassType;

        $klass = Klass::findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();

        $gradeD = Grade::findOrFail($klass->grade_id);
        $gradeId = $klass->grade_id;

        $classes = $gradeD->klasses()
            ->where('term_id', $selectedTermId)
            ->where('type', $klassType)
            ->with(['students.tests.subject', 'students.jce'])
            ->get();

        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->where('active', true)
            ->whereHas('tests', function ($query) use ($sequenceId) {
                $query->where('sequence', $sequenceId);
            })->whereHas('tests.students', function ($query) use ($type) {
                $this->applySeniorAssessmentStudentScoreFilter($query, $type);
            })->with(['subject', 'tests' => function ($query) use ($sequenceId, $type) {
                $query->where('sequence', $sequenceId)->with(['students' => function ($query) use ($type) {
                    $this->applySeniorAssessmentStudentScoreFilter($query, $type);
                }]);
            }])->get();

        $caTest = Test::where('term_id', $selectedTermId)
            ->where('type', $type)
            ->where('sequence', $sequenceId)
            ->where('grade_id', $gradeId)
            ->first();

        $allSubjects = $allGradeSubjects->pluck('subject.name')->unique()->sort()->values()->toArray();
        $doubleSubjects = $allGradeSubjects
            ->filter(fn($gs) => $gs->subject->is_double)
            ->pluck('subject.name')
            ->unique()
            ->toArray();

        $studentData = [];
        $totalIndex = 0;

        foreach ($classes as $classItem) {
            foreach ($classItem->students as $student) {
                if (!$student->pivot->active || $student->pivot->term_id != $selectedTermId) {
                    continue;
                }

                $totalIndex++;
                $gender = in_array($student->gender, ['M', 'F']) ? $student->gender : 'M';
                $scores = $this->calculateStudentScoresSeniorCA($student, $selectedTermId, $type, $sequenceId);
                $subjectScores = [];

                $enrolledSubjects = $this->getStudentEnrolledSubjects($student, $classItem, $selectedTermId);
                foreach ($allSubjects as $subject) {
                    $score = collect($scores)->firstWhere('subject', $subject);
                    $isEnrolled = in_array($subject, $enrolledSubjects);

                    if ($score) {
                        $subjectScores[$subject] = [
                            'grade' => $score['grade'],
                            'points' => $score['points'],
                        ];
                    } elseif ($isEnrolled) {
                        $subjectScores[$subject] = [
                            'grade' => 'X',
                            'points' => 0,
                        ];
                    } else {
                        $subjectScores[$subject] = [
                            'grade' => '-',
                            'points' => 0,
                        ];
                    }
                }

                // Best-6 total points with double subject slot awareness
                $sorted = collect($subjectScores)->sortByDesc('points');
                $totalSlots = 0;
                $totalPoints = 0;
                $best6Count = 0;
                foreach ($sorted as $subjectName => $data) {
                    if ($data['grade'] === '-' || $data['grade'] === 'X') continue;
                    $slotsNeeded = in_array($subjectName, $doubleSubjects) ? 2 : 1;
                    if ($totalSlots + $slotsNeeded <= 6) {
                        $totalSlots += $slotsNeeded;
                        $totalPoints += in_array($subjectName, $doubleSubjects) ? $data['points'] * 2 : $data['points'];
                        $best6Count++;
                    }
                    if ($totalSlots >= 6) break;
                }

                // Overall points = sum of ALL subject points
                $overallPoints = 0;
                foreach ($subjectScores as $subjectName => $data) {
                    if ($data['grade'] !== '-' && $data['grade'] !== 'X') {
                        $overallPoints += in_array($subjectName, $doubleSubjects) ? $data['points'] * 2 : $data['points'];
                    }
                }

                $studentData[] = [
                    'surname' => $student->last_name,
                    'firstname' => $student->first_name,
                    'class' => $classItem->name,
                    'gender' => $gender,
                    'jce' => $student->jce->overall ?? '-',
                    'subjects' => $subjectScores,
                    'overallPoints' => $overallPoints,
                    'best6Points' => $totalPoints,
                    'best6Count' => $best6Count,
                ];
            }
        }

        usort($studentData, function ($a, $b) {
            return $b['overallPoints'] <=> $a['overallPoints'];
        });

        if ($request->query('export') === 'excel') {
            return Excel::download(
                new \App\Exports\AwardTypeAnalysisExport([
                    'gradeName' => $gradeD->name,
                    'students' => $studentData,
                    'allSubjects' => $allSubjects,
                    'type' => $type,
                    'test' => $caTest,
                    'awardLabel' => $awardLabel,
                ]),
                $gradeD->name . '_' . strtolower(str_replace(' ', '_', $awardLabel)) . '_analysis.xlsx'
            );
        }

        return view('assessment.senior.award-type-analysis', [
            'gradeName' => $gradeD->name,
            'students' => $studentData,
            'allSubjects' => $allSubjects,
            'sequenceId' => $sequenceId,
            'type' => $type,
            'test' => $caTest,
            'school_setup' => $school_setup,
            'school_data' => SchoolSetup::first(),
            'awardLabel' => $awardLabel,
            'awardType' => $awardType,
        ]);
    }

    public function generateHouseAwardAnalysis(Request $request, $classId, $sequenceId, $type) {
        $klass = Klass::findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();

        $gradeD = Grade::findOrFail($klass->grade_id);
        $gradeId = $klass->grade_id;

        $classes = $gradeD->klasses()
            ->where('term_id', $selectedTermId)
            ->with(['students.tests.subject', 'students.jce'])
            ->get();

        // Build student → class map
        $studentClassMap = [];
        foreach ($classes as $classItem) {
            foreach ($classItem->students as $student) {
                if ($student->pivot->active && $student->pivot->term_id == $selectedTermId) {
                    $studentClassMap[$student->id] = [
                        'name' => $classItem->name,
                        'id' => $classItem->id,
                        'klass' => $classItem,
                    ];
                }
            }
        }

        // Grade subjects and CA test
        $allGradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->where('term_id', $selectedTermId)
            ->where('active', true)
            ->whereHas('tests', function ($query) use ($sequenceId) {
                $query->where('sequence', $sequenceId);
            })->whereHas('tests.students', function ($query) use ($type) {
                $this->applySeniorAssessmentStudentScoreFilter($query, $type);
            })->with(['subject', 'tests' => function ($query) use ($sequenceId, $type) {
                $query->where('sequence', $sequenceId)->with(['students' => function ($query) use ($type) {
                    $this->applySeniorAssessmentStudentScoreFilter($query, $type);
                }]);
            }])->get();

        $caTest = Test::where('term_id', $selectedTermId)
            ->where('type', $type)
            ->where('sequence', $sequenceId)
            ->where('grade_id', $gradeId)
            ->first();

        $allSubjects = $allGradeSubjects->pluck('subject.name')->unique()->sort()->values()->toArray();
        $doubleSubjects = $allGradeSubjects
            ->filter(fn($gs) => $gs->subject->is_double)
            ->pluck('subject.name')
            ->unique()
            ->toArray();

        // Fetch houses with students for this term
        $houses = House::with([
            'students' => function ($query) use ($selectedTermId) {
                $query->wherePivot('term_id', $selectedTermId);
            }
        ])->where('term_id', $selectedTermId)->get();

        $housesData = [];

        foreach ($houses as $house) {
            $houseStudents = [];

            foreach ($house->students as $houseStudent) {
                // Skip students not in this grade
                if (!isset($studentClassMap[$houseStudent->id])) {
                    continue;
                }

                $classInfo = $studentClassMap[$houseStudent->id];

                // Find the eager-loaded student instance from classes collection
                $studentInstance = null;
                foreach ($classes as $classItem) {
                    if ($classItem->id !== $classInfo['id']) continue;
                    foreach ($classItem->students as $s) {
                        if ($s->id === $houseStudent->id) {
                            $studentInstance = $s;
                            break 2;
                        }
                    }
                }

                if (!$studentInstance) continue;

                $gender = in_array($studentInstance->gender, ['M', 'F']) ? $studentInstance->gender : 'M';
                $scores = $this->calculateStudentScoresSeniorCA($studentInstance, $selectedTermId, $type, $sequenceId);
                $subjectScores = [];

                $enrolledSubjects = $this->getStudentEnrolledSubjects($studentInstance, $classInfo['klass'], $selectedTermId);
                foreach ($allSubjects as $subject) {
                    $score = collect($scores)->firstWhere('subject', $subject);
                    $isEnrolled = in_array($subject, $enrolledSubjects);

                    if ($score) {
                        $subjectScores[$subject] = [
                            'grade' => $score['grade'],
                            'points' => $score['points'],
                        ];
                    } elseif ($isEnrolled) {
                        $subjectScores[$subject] = [
                            'grade' => 'X',
                            'points' => 0,
                        ];
                    } else {
                        $subjectScores[$subject] = [
                            'grade' => '-',
                            'points' => 0,
                        ];
                    }
                }

                // Best-6 total points with double subject slot awareness
                $sorted = collect($subjectScores)->sortByDesc('points');
                $totalSlots = 0;
                $totalPoints = 0;
                foreach ($sorted as $subjectName => $data) {
                    if ($data['grade'] === '-' || $data['grade'] === 'X') continue;
                    $slotsNeeded = in_array($subjectName, $doubleSubjects) ? 2 : 1;
                    if ($totalSlots + $slotsNeeded <= 6) {
                        $totalSlots += $slotsNeeded;
                        $totalPoints += in_array($subjectName, $doubleSubjects) ? $data['points'] * 2 : $data['points'];
                    }
                    if ($totalSlots >= 6) break;
                }

                // Overall points
                $overallPoints = 0;
                foreach ($subjectScores as $subjectName => $data) {
                    if ($data['grade'] !== '-' && $data['grade'] !== 'X') {
                        $overallPoints += in_array($subjectName, $doubleSubjects) ? $data['points'] * 2 : $data['points'];
                    }
                }

                // Credits count (A*, A, B, C)
                $creditsCount = 0;
                foreach ($scores as $score) {
                    $grade = trim($score['grade'] ?? '');
                    if (empty($grade)) continue;

                    $isDouble = $score['is_double'] ?? false;
                    if ($isDouble && strlen($grade) == 2) {
                        if (in_array($grade[0], ['A', 'B', 'C'])) $creditsCount++;
                        if (in_array($grade[1], ['A', 'B', 'C'])) $creditsCount++;
                    } elseif (in_array($grade, ['A*', 'A', 'B', 'C'])) {
                        $creditsCount++;
                    }
                }

                $houseStudents[] = [
                    'surname' => $studentInstance->last_name,
                    'firstname' => $studentInstance->first_name,
                    'class' => $classInfo['name'],
                    'gender' => $gender,
                    'jce' => $studentInstance->jce->overall ?? '-',
                    'subjects' => $subjectScores,
                    'overallPoints' => $overallPoints,
                    'best6Points' => $totalPoints,
                    'credits' => $creditsCount,
                ];
            }

            // Group by class, sort each class by overallPoints desc (best6 as tiebreaker)
            $grouped = collect($houseStudents)->groupBy('class')->map(function ($classStudents) {
                return $classStudents->sortByDesc(function ($s) {
                    return [$s['overallPoints'], $s['best6Points']];
                })->values()->all();
            })->sortKeys()->all();

            if (!empty($houseStudents)) {
                $housesData[$house->name] = $grouped;
            }
        }

        if ($request->query('export') === 'excel') {
            return Excel::download(
                new HouseAwardAnalysisExport([
                    'gradeName' => $gradeD->name,
                    'housesData' => $housesData,
                    'allSubjects' => $allSubjects,
                    'type' => $type,
                    'test' => $caTest,
                ]),
                $gradeD->name . '_house_award_analysis.xlsx'
            );
        }

        return view('assessment.senior.house-award-analysis', [
            'gradeName' => $gradeD->name,
            'housesData' => $housesData,
            'allSubjects' => $allSubjects,
            'sequenceId' => $sequenceId,
            'type' => $type,
            'test' => $caTest,
            'school_data' => $school_setup,
        ]);
    }

    private function getStudentEnrolledSubjects($student, $klass, $termId) {
        $enrolledSubjects = [];
        $coreSubjects = DB::table('klass_subject')
            ->join('grade_subject', 'klass_subject.grade_subject_id', '=', 'grade_subject.id')
            ->join('subjects', 'grade_subject.subject_id', '=', 'subjects.id')
            ->where('klass_subject.klass_id', $klass->id)
            ->where('klass_subject.term_id', $termId)
            ->whereNull('klass_subject.deleted_at')
            ->pluck('subjects.name')
            ->toArray();
        
        $optionalSubjects = DB::table('student_optional_subjects')
            ->join('optional_subjects', 'student_optional_subjects.optional_subject_id', '=', 'optional_subjects.id')
            ->join('grade_subject', 'optional_subjects.grade_subject_id', '=', 'grade_subject.id')
            ->join('subjects', 'grade_subject.subject_id', '=', 'subjects.id')
            ->where('student_optional_subjects.student_id', $student->id)
            ->where('student_optional_subjects.term_id', $termId)
            ->pluck('subjects.name')
            ->toArray();
        
        return array_unique(array_merge($coreSubjects, $optionalSubjects));
    }

    /**
     * Generate CA house performance report for senior school
     */
    public function generateCASeniorHousePerformanceReport($sequence,$type){
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $houses = House::with([
            'students' => function ($query) use ($selectedTermId, $sequence,$type) {
                $query->where('student_house.term_id', $selectedTermId);
                $query->with(['tests' => function ($query) use ($selectedTermId, $sequence,$type) {
                    $query->where('tests.term_id', $selectedTermId)
                        ->where('sequence', $sequence)
                        ->where('type', $type)
                        ->with('subject.subject');
                }]);
            }
        ])->get();

        $test = Test::where('term_id', $selectedTermId)->where('type', $type)->where('sequence', $sequence)->first();
        $housePerformance = [];
        foreach ($houses as $house) {
            $houseName = $house->name;
            $gradesCount = [
                'A*' => ['M' => 0, 'F' => 0],
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'U' => ['M' => 0, 'F' => 0],
                'total' => ['M' => 0, 'F' => 0]
            ];

            foreach ($house->students as $student) {
                $gender = $student->gender === 'M' ? 'M' : 'F';
                foreach ($student->tests as $test) {
                    $grade = $test->pivot->grade;
                    $isDouble = (bool) ($test->subject->subject->is_double ?? false);

                    if ($isDouble && strlen($grade) == 2) {
                        // Double subject: count each grade letter separately
                        foreach ([$grade[0], $grade[1]] as $g) {
                            if (isset($gradesCount[$g][$gender])) {
                                $gradesCount[$g][$gender]++;
                            }
                        }
                        $gradesCount['total'][$gender] += 2;
                    } else {
                        if (isset($gradesCount[$grade][$gender])) {
                            $gradesCount[$grade][$gender]++;
                            $gradesCount['total'][$gender]++;
                        }
                    }
                }
            }

            $totalMale = $gradesCount['total']['M'];
            $totalFemale = $gradesCount['total']['F'];

            $AStarABCount_M = $gradesCount['A*']['M'] + $gradesCount['A']['M'] + $gradesCount['B']['M'];
            $AStarABCount_F = $gradesCount['A*']['F'] + $gradesCount['A']['F'] + $gradesCount['B']['F'];

            $AStarABCCount_M = $AStarABCount_M + $gradesCount['C']['M'];
            $AStarABCCount_F = $AStarABCount_F + $gradesCount['C']['F'];

            $AStarABCDCount_M = $AStarABCCount_M + $gradesCount['D']['M'];
            $AStarABCDCount_F = $AStarABCCount_F + $gradesCount['D']['F'];

            $DEUCount_M = $gradesCount['D']['M'] + $gradesCount['E']['M'] + $gradesCount['U']['M'];
            $DEUCount_F = $gradesCount['D']['F'] + $gradesCount['E']['F'] + $gradesCount['U']['F'];

            $AStarABPercentage_M = $totalMale > 0 ? round(($AStarABCount_M / $totalMale) * 100, 2) : 0;
            $AStarABPercentage_F = $totalFemale > 0 ? round(($AStarABCount_F / $totalFemale) * 100, 2) : 0;

            $AStarABCPercentage_M = $totalMale > 0 ? round(($AStarABCCount_M / $totalMale) * 100, 2) : 0;
            $AStarABCPercentage_F = $totalFemale > 0 ? round(($AStarABCCount_F / $totalFemale) * 100, 2) : 0;

            $AStarABCDPercentage_M = $totalMale > 0 ? round(($AStarABCDCount_M / $totalMale) * 100, 2) : 0;
            $AStarABCDPercentage_F = $totalFemale > 0 ? round(($AStarABCDCount_F / $totalFemale) * 100, 2) : 0;

            $DEUPercentage_M = $totalMale > 0 ? round(($DEUCount_M / $totalMale) * 100, 2) : 0;
            $DEUPercentage_F = $totalFemale > 0 ? round(($DEUCount_F / $totalFemale) * 100, 2) : 0;

            $housePerformance[$houseName] = [
                'grades' => $gradesCount,
                'A*AB%' => ['M' => $AStarABPercentage_M, 'F' => $AStarABPercentage_F],
                'A*ABC%' => ['M' => $AStarABCPercentage_M, 'F' => $AStarABCPercentage_F],
                'A*ABCD%' => ['M' => $AStarABCDPercentage_M, 'F' => $AStarABCDPercentage_F],
                'DEU%' => ['M' => $DEUPercentage_M, 'F' => $DEUPercentage_F],
                'totalMale' => $totalMale,
                'totalFemale' => $totalFemale
            ];
        }

        $school_data = SchoolSetup::first();
        return view('houses.subjects-houses-statistics-senior', [
            'housePerformance' => $housePerformance,
            'school_data' => $school_data,
            'type' => "Exam",
            'test' => $test
        ]);
    }

    /**
     * Generate overall teacher performance report for senior school
     */
    public function generateOverallTeacherPerformanceReportSenior($classId, $type, $sequence) {
        $selectedTermId = session('selected_term_id', TermHelper::getCurrentTerm()->id);
        $currentTerm = TermHelper::getCurrentTerm();
        $school_data = SchoolSetup::first();
        $year = $currentTerm->year;
        
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;

        $test = Test::where('grade_id', $gradeId)->where('term_id', $selectedTermId)->where('sequence', $sequence)->where('type', $type)->first();
        
        $grade = Grade::with(['klasses' => function($query) use ($selectedTermId, $year) {
            $query->where('term_id', $selectedTermId);
        }])->findOrFail($gradeId);
        
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

                if (!$teacher) {
                    continue;
                }

                $subjectName = $gradeSubject->subject->name;
                if (!in_array($subjectName, $subjectList)) {
                    $subjectList[] = $subjectName;
                }

                $tests = Test::where('grade_subject_id', $gradeSubject->id)
                    ->where('term_id', $selectedTermId)
                    ->where('sequence', $sequence)
                    ->where('type', $type)
                    ->get();

                $isDouble = (bool) ($gradeSubject->subject->is_double ?? false);
                $performanceData = $this->calculateTeacherPerformanceDataSenior($teacher, $class, $subjectName, $tests, $studentIds, $isDouble);

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
            
            $isDouble = (bool) ($gradeSubject->subject->is_double ?? false);
            $performanceData = $this->calculateTeacherPerformanceDataSenior($teacher, $optionalSubject, $displayName, $tests, $studentIds, $isDouble);

            if ($performanceData) {
                if (!isset($teacherPerformanceBySubject[$subjectName])) {
                    $teacherPerformanceBySubject[$subjectName] = [];
                }
                $teacherPerformanceBySubject[$subjectName][] = $performanceData;
            }
        }

        foreach ($teacherPerformanceBySubject as &$performances) {
            usort($performances, function($a, $b) {
                return $b['abcPercentage'] <=> $a['abcPercentage'];
            });
        }
        
        sort($subjectList);
        
        $teacherPerformance = [];
        $finalSubjectList = [];
        foreach ($subjectList as $subject) {
            if (isset($teacherPerformanceBySubject[$subject]) && !empty($teacherPerformanceBySubject[$subject])) {
                $teacherPerformance[$subject] = $teacherPerformanceBySubject[$subject];
                $finalSubjectList[] = $subject;
            }
        }
    
        $teacherTotals = [];
        $isGrouped = true;
    
        $subjectsToIterate = $isGrouped ? $subjectList : ['__overall__'];
    
        foreach ($subjectsToIterate as $subjKey) {
            $rows = $subjKey === '__overall__'
                    ? $teacherPerformance 
                    : ($teacherPerformance[$subjKey] ?? []);
    
            $tot = [
                'grades' => [
                    'A*'=>['M'=>0,'F'=>0,'T'=>0],
                    'A'=>['M'=>0,'F'=>0,'T'=>0],'B'=>['M'=>0,'F'=>0,'T'=>0],'C'=>['M'=>0,'F'=>0,'T'=>0],
                    'D'=>['M'=>0,'F'=>0,'T'=>0],'E'=>['M'=>0,'F'=>0,'T'=>0],'F'=>['M'=>0,'F'=>0,'T'=>0],
                    'G'=>['M'=>0,'F'=>0,'T'=>0],'U'=>['M'=>0,'F'=>0,'T'=>0],'NS'=>['M'=>0,'F'=>0,'T'=>0],
                    'total'=>['M'=>0,'F'=>0,'T'=>0]
                ],
                'AB%'=>['M'=>0,'F'=>0], 'ABC%'=>['M'=>0,'F'=>0],
                'ABCD%'=>['M'=>0,'F'=>0],'DEFGU%'=>['M'=>0,'F'=>0],
                'totalMale'=>0,'totalFemale'=>0
            ];

            foreach ($rows as $r) {
                foreach (['A*','A','B','C','D','E','F','G','U','NS','total'] as $g) {
                    $tot['grades'][$g]['M'] += $r['grades'][$g]['M'] ?? 0;
                    $tot['grades'][$g]['F'] += $r['grades'][$g]['F'] ?? 0;
                    $tot['grades'][$g]['T'] += $r['grades'][$g]['T'] ?? 0;
                }
                foreach (['AB%','ABC%','ABCD%','DEFGU%'] as $k) {
                    $tot[$k]['M'] += $r[$k]['M'] ?? 0;
                    $tot[$k]['F'] += $r[$k]['F'] ?? 0;
                }
                $tot['totalMale']   += $r['totalMale'];
                $tot['totalFemale'] += $r['totalFemale'];
            }

            $div = max(count($rows),1);
            foreach (['AB%','ABC%','ABCD%','DEFGU%'] as $k) {
                $tot[$k]['M'] = round($tot[$k]['M'] / $div, 2);
                $tot[$k]['F'] = round($tot[$k]['F'] / $div, 2);
            }
    
            $teacherTotals[$subjKey] = $tot;
        }
        
        if (request()->has('export')) {
            return Excel::download(
                new TeacherPerformanceSeniorExport($teacherPerformance, $test, true), 
                "Teacher_Performance_Senior_{$klass->name}_" . date('Y-m-d') . ".xlsx"
            );
        }
        
        return view('assessment.senior.subjects-teachers-analysis-senior', [
            'teacherPerformance' => $teacherPerformance,
            'school_data' => $school_data,
            'currentTerm' => $currentTerm,
            'type' => $type,
            'test' => $test,
            'subjectList' => $finalSubjectList,
            'teacherTotals' => $teacherTotals,
            'isGrouped' => true
        ]);
    }

    /**
     * Calculate CA scores for senior students
     */
    private function calculateStudentScoresSeniorCA($student, $selectedTermId, $type, $sequence){
        $scores = [];
        $subjects = $student->tests->where('term_id', $selectedTermId)->pluck('subject')->unique('id');
        $jceGrade = $student->jce->overall ?? '-';

        foreach ($subjects as $subject) {
            $caTest = $student->tests
                ->where('term_id', $selectedTermId)
                ->where('grade_subject_id', $subject->id)
                ->where('type', $type)
                ->where('sequence', $sequence)
                ->first();

            $assessmentValues = $this->resolveSeniorAssessmentValues($caTest, $subject, $selectedTermId, $type);

            $scores[] = [
                'subject' => $subject->subject->name ?? '',
                'is_double' => (bool) ($subject->subject->is_double ?? false),
                'points' => $assessmentValues['points'],
                'score' => $assessmentValues['score'],
                'percentage' => $assessmentValues['percentage'],
                'grade' => $assessmentValues['grade'],
                'jceGrade' => $jceGrade,
                'test' => $caTest,
            ];
        }

        usort($scores, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });
        return $scores;
    }

    /**
     * Calculate teacher performance data for senior school
     */
    protected function calculateTeacherPerformanceDataSenior($teacher, $class, $subjectName, $tests, $studentIds, bool $isDouble = false) {
        $validGrades = ['A*','A','B','C','D','E','F','G','U'];
        $gradeCounts = [
            'A*' => ['M' => 0, 'F' => 0, 'T' => 0],
            'A' => ['M' => 0, 'F' => 0, 'T' => 0],
            'B' => ['M' => 0, 'F' => 0, 'T' => 0],
            'C' => ['M' => 0, 'F' => 0, 'T' => 0],
            'D' => ['M' => 0, 'F' => 0, 'T' => 0],
            'E' => ['M' => 0, 'F' => 0, 'T' => 0],
            'F' => ['M' => 0, 'F' => 0, 'T' => 0],
            'G' => ['M' => 0, 'F' => 0, 'T' => 0],
            'U' => ['M' => 0, 'F' => 0, 'T' => 0],
            'NS' => ['M' => 0, 'F' => 0, 'T' => 0],
            'total' => ['M' => 0, 'F' => 0, 'T' => 0],
        ];

        // Build a map of student_id -> raw grade from all tests (most recent grade wins)
        $testIds = $tests->pluck('id')->all();
        $finalByStudent = [];

        StudentTest::whereIn('test_id', $testIds)
            ->whereIn('student_id', $studentIds)
            ->select(['student_id', 'grade', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->chunk(2000, function ($chunk) use (&$finalByStudent, $isDouble, $validGrades) {
                foreach ($chunk as $st) {
                    if (!isset($finalByStudent[$st->student_id])) {
                        $g = $st->grade ?? '';
                        if ($g === '') continue;
                        if ($isDouble && is_string($g) && strlen($g) === 2) {
                            // Keep raw 2-char grade for double subjects
                            $finalByStudent[$st->student_id] = $g;
                        } else {
                            $finalByStudent[$st->student_id] = in_array($g, $validGrades, true) ? $g : 'U';
                        }
                    }
                }
            });

        // Count all enrolled students
        $enrolledStudents = Student::whereIn('id', $studentIds)->get();

        foreach ($enrolledStudents as $student) {
            $isMale = in_array(strtolower($student->gender ?? ''), ['male', 'm']);
            $gender = $isMale ? 'M' : 'F';

            if (isset($finalByStudent[$student->id])) {
                $g = $finalByStudent[$student->id];
                if ($isDouble && is_string($g) && strlen($g) === 2) {
                    // Double subject: split into two individual grades
                    foreach (str_split($g) as $char) {
                        $mapped = in_array($char, $validGrades, true) ? $char : 'U';
                        $gradeCounts[$mapped][$gender]++;
                        $gradeCounts[$mapped]['T']++;
                        $gradeCounts['total'][$gender]++;
                        $gradeCounts['total']['T']++;
                    }
                } else {
                    $gradeCounts[$g][$gender]++;
                    $gradeCounts[$g]['T']++;
                    $gradeCounts['total'][$gender]++;
                    $gradeCounts['total']['T']++;
                }
            } else {
                $slots = $isDouble ? 2 : 1;
                $gradeCounts['NS'][$gender] += $slots;
                $gradeCounts['NS']['T'] += $slots;
                $gradeCounts['total'][$gender] += $slots;
                $gradeCounts['total']['T'] += $slots;
            }
        }

        $totalMale = $gradeCounts['total']['M'];
        $totalFemale = $gradeCounts['total']['F'];
        $totalStudents = $gradeCounts['total']['T'];
        if ($totalStudents === 0) {
            return null;
        }

        $sumAStar = $gradeCounts['A*']['T'];
        $sumA = $gradeCounts['A']['T'];
        $sumB = $gradeCounts['B']['T'];
        $sumC = $gradeCounts['C']['T'];
        $sumD = $gradeCounts['D']['T'];
        $sumE = $gradeCounts['E']['T'];
        $sumF = $gradeCounts['F']['T'];
        $sumG = $gradeCounts['G']['T'];
        $sumU = $gradeCounts['U']['T'];

        $abCount = $sumAStar + $sumA + $sumB;
        $abPercentage = $totalStudents > 0 ? round(($abCount / $totalStudents) * 100, 2) : 0;

        $abcCount = $abCount + $sumC;
        $abcPercentage = $totalStudents > 0 ? round(($abcCount / $totalStudents) * 100, 2) : 0;

        $abcdCount = $abcCount + $sumD;
        $abcdPercentage = $totalStudents > 0 ? round(($abcdCount / $totalStudents) * 100, 2) : 0;

        $defguCount = $sumD + $sumE + $sumF + $sumG + $sumU;
        $defguPercentage = $totalStudents > 0 ? round(($defguCount / $totalStudents) * 100, 2) : 0;

        $abCountM = $gradeCounts['A*']['M'] + $gradeCounts['A']['M'] + $gradeCounts['B']['M'];
        $abPercentageM = $totalMale > 0 ? round(($abCountM / $totalMale) * 100, 2) : 0;

        $abcCountM = $abCountM + $gradeCounts['C']['M'];
        $abcPercentageM = $totalMale > 0 ? round(($abcCountM / $totalMale) * 100, 2) : 0;

        $abcdCountM = $abcCountM + $gradeCounts['D']['M'];
        $abcdPercentageM = $totalMale > 0 ? round(($abcdCountM / $totalMale) * 100, 2) : 0;

        $defguCountM = $gradeCounts['D']['M'] + $gradeCounts['E']['M'] + $gradeCounts['F']['M'] + $gradeCounts['G']['M'] + $gradeCounts['U']['M'];
        $defguPercentageM = $totalMale > 0 ? round(($defguCountM / $totalMale) * 100, 2) : 0;

        $abCountF = $gradeCounts['A*']['F'] + $gradeCounts['A']['F'] + $gradeCounts['B']['F'];
        $abPercentageF = $totalFemale > 0 ? round(($abCountF / $totalFemale) * 100, 2) : 0;

        $abcCountF = $abCountF + $gradeCounts['C']['F'];
        $abcPercentageF = $totalFemale > 0 ? round(($abcCountF / $totalFemale) * 100, 2) : 0;

        $abcdCountF = $abcCountF + $gradeCounts['D']['F'];
        $abcdPercentageF = $totalFemale > 0 ? round(($abcdCountF / $totalFemale) * 100, 2) : 0;

        $defguCountF = $gradeCounts['D']['F'] + $gradeCounts['E']['F'] + $gradeCounts['F']['F'] + $gradeCounts['G']['F'] + $gradeCounts['U']['F'];
        $defguPercentageF = $totalFemale > 0 ? round(($defguCountF / $totalFemale) * 100, 2) : 0;

        return [
            'teacher_name' => $teacher->fullName,
            'class_name' => $class->name,
            'subject_name' => $subjectName,
            'grades' => $gradeCounts,
            'AB%' => ['M' => $abPercentageM, 'F' => $abPercentageF],
            'ABC%' => ['M' => $abcPercentageM, 'F' => $abcPercentageF],
            'ABCD%' => ['M' => $abcdPercentageM, 'F' => $abcdPercentageF],
            'DEFGU%' => ['M' => $defguPercentageM, 'F' => $defguPercentageF],
            'totalMale' => $totalMale,
            'totalFemale' => $totalFemale,
            'totalStudents' => $totalStudents,
            'abcPercentage' => $abcPercentage,
        ];
    }

    /**
     * Show subject teacher grade analysis for senior school
     */
    public function showSubjectTeacherGradeAnalysis(Request $request, $classId, $sequence, $type){
        $klass   = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;
        $termId  = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        $test1 = Test::query()
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->where('sequence', $sequence)
            ->where('type', $type)
            ->first();

        $report = [];
        $seen   = [];

        $init = static function ($teacher, $className, $subjectName) {
            return [
                'TEACHER'  => $teacher,
                'CLASS'    => $className,
                'SUBJECT'  => $subjectName,
                'A*' => 0, 'A' => 0, 'B' => 0, 'C' => 0,
                '% CREDIT' => 0,
                'D' => 0, 'E' => 0,
                '% PASS'   => 0,
                'F' => 0, 'G' => 0, 'U' => 0,
                'TOTAL' => 0,
            ];
        };

        $finalize = static function (&$row) {
            $credits = $row['A*'] + $row['A'] + $row['B'] + $row['C'];
            $passes  = $credits + $row['D'] + $row['E'];
            $t = max(1, $row['TOTAL']);
            $row['% CREDIT'] = round(($credits / $t) * 100, 1);
            $row['% PASS']   = round(($passes  / $t) * 100, 1);
        };

        $klassSubjects = \App\Models\KlassSubject::query()
            ->where('term_id', $termId)->whereHas('gradeSubject', fn($q) => $q->where('grade_id', $gradeId))->with([
                'klass:id,name,grade_id',
                'klass.students' => fn($q) => $q->wherePivot('term_id', $termId),
                'gradeSubject.subject:id,name,is_double',
                'teacher:id,firstname,lastname',
            ])->get();

        $validGrades = ['A*','A','B','C','D','E','F','G','U'];

        foreach ($klassSubjects as $ks) {
            $className   = $ks->klass->name ?? '—';
            $subjectName = optional($ks->gradeSubject->subject)->name ?? '—';
            $teacherName = $ks->teacher ? trim(($ks->teacher->firstname ?? '').' '.($ks->teacher->lastname ?? '')) : '—';
            $isDouble = (bool) ($ks->gradeSubject->subject->is_double ?? false);

            $rowKey = "{$teacherName}_{$className}_{$subjectName}";
            if (!isset($report[$rowKey])) $report[$rowKey] = $init($teacherName, $className, $subjectName);

            $testIds = \App\Models\Test::query()
                ->where('grade_subject_id', $ks->grade_subject_id)
                ->where('term_id', $termId)
                ->where('sequence', $sequence)
                ->where('type', $type)
                ->pluck('id');

            if ($testIds->isEmpty()) { $finalize($report[$rowKey]); continue; }

            $classStudentIds = $ks->klass->students->pluck('id')->all();
            if (empty($classStudentIds)) { $finalize($report[$rowKey]); continue; }

            $finalByStudent = [];
            StudentTest::query()
                ->whereIn('test_id', $testIds)
                ->select(['id','student_id','grade','updated_at'])
                ->orderBy('updated_at','desc')
                ->chunk(2000, function ($chunk) use (&$finalByStudent, $isDouble, $validGrades) {
                    foreach ($chunk as $st) {
                        if (!isset($finalByStudent[$st->student_id])) {
                            $g = $st->grade ?? '';
                            if ($g === '') continue;
                            if ($isDouble && is_string($g) && strlen($g) === 2) {
                                $finalByStudent[$st->student_id] = $g;
                            } else {
                                $finalByStudent[$st->student_id] = in_array($g, $validGrades, true) ? $g : 'U';
                            }
                        }
                    }
                });

            foreach ($classStudentIds as $sid) {
                if (!isset($finalByStudent[$sid])) continue;
                $g = $finalByStudent[$sid];
                if ($isDouble && is_string($g) && strlen($g) === 2) {
                    foreach (str_split($g) as $char) {
                        $mapped = in_array($char, $validGrades, true) ? $char : 'U';
                        if (isset($report[$rowKey][$mapped])) $report[$rowKey][$mapped]++;
                        $report[$rowKey]['TOTAL']++;
                    }
                } else {
                    if (isset($report[$rowKey][$g])) $report[$rowKey][$g]++; elseif ($g === 'U') $report[$rowKey]['U']++;
                    $report[$rowKey]['TOTAL']++;
                }
            }

            $finalize($report[$rowKey]);
        }

        $optionalSubjects = OptionalSubject::query()
            ->where('term_id', $termId)->whereHas('gradeSubject', fn($q) => $q->where('grade_id', $gradeId))->with([
                'gradeSubject.subject:id,name,is_double',
                'teacher:id,firstname,lastname',
            ])->get();

        foreach ($optionalSubjects as $opt) {
            $subjectName = optional($opt->gradeSubject->subject)->name ?? '—';
            $teacherName = $opt->teacher ? trim(($opt->teacher->firstname ?? '').' '.($opt->teacher->lastname ?? '')) : '—';
            $isDouble = (bool) ($opt->gradeSubject->subject->is_double ?? false);

            $optionalDisplayName = $opt->name ?? '—';
            $rowKey = "OPT_{$opt->id}_T{$opt->teacher_id}";
            if (!isset($report[$rowKey])) $report[$rowKey] = $init($teacherName, $optionalDisplayName, $subjectName);
            $seen[$rowKey] = $seen[$rowKey] ?? [];

            $testIds = \App\Models\Test::query()
                ->where('grade_subject_id', $opt->grade_subject_id)
                ->where('term_id', $termId)
                ->where('sequence', $sequence)
                ->where('type', $type)
                ->pluck('id');

            if ($testIds->isEmpty()) { $finalize($report[$rowKey]); continue; }
            $finalByStudent = [];
            StudentTest::query()
                ->whereIn('test_id', $testIds)
                ->select(['id','student_id','grade','updated_at'])
                ->orderBy('updated_at','desc')
                ->chunk(2000, function ($chunk) use (&$finalByStudent, $isDouble, $validGrades) {
                    foreach ($chunk as $st) {
                        if (!isset($finalByStudent[$st->student_id])) {
                            $g = $st->grade ?? '';
                            if ($g === '') continue;
                            if ($isDouble && is_string($g) && strlen($g) === 2) {
                                $finalByStudent[$st->student_id] = $g;
                            } else {
                                $finalByStudent[$st->student_id] = in_array($g, $validGrades, true) ? $g : 'U';
                            }
                        }
                    }
                });

            $studentIds = $opt->students()->wherePivot('term_id', $termId)->distinct('students.id')->pluck('students.id')->all();
            if (empty($studentIds)) { $finalize($report[$rowKey]); continue; }

            foreach ($studentIds as $sid) {
                if (isset($seen[$rowKey][$sid])) continue;
                if (!isset($finalByStudent[$sid])) continue;

                $g = $finalByStudent[$sid];
                if ($isDouble && is_string($g) && strlen($g) === 2) {
                    foreach (str_split($g) as $char) {
                        $mapped = in_array($char, $validGrades, true) ? $char : 'U';
                        if (isset($report[$rowKey][$mapped])) $report[$rowKey][$mapped]++;
                        $report[$rowKey]['TOTAL']++;
                    }
                } else {
                    if (isset($report[$rowKey][$g])) $report[$rowKey][$g]++; elseif ($g === 'U') $report[$rowKey]['U']++;
                    $report[$rowKey]['TOTAL']++;
                }
                $seen[$rowKey][$sid] = true;
            }

            $finalize($report[$rowKey]);
        }

        $rows = array_values($report);
        usort($rows, function ($a, $b) {
            $t = strcasecmp($a['TEACHER'], $b['TEACHER']);
            if ($t !== 0) return $t;
            return ($b['% CREDIT'] <=> $a['% CREDIT']);
        });

        $groupedOutput = [];

        $currentTeacher = null;
        $bucket = [];

        $emitSubtotal = function($teacher, $bucketRows) use (&$groupedOutput, $finalize) {
            if (empty($bucketRows)) return;

            foreach ($bucketRows as $r) {
                $groupedOutput[] = $r;
            }

            $sub = [
                'TEACHER' => $teacher.' — TOTAL',
                'CLASS'   => '',
                'SUBJECT' => '',
                'A*'=>0,'A'=>0,'B'=>0,'C'=>0,'% CREDIT'=>0,'D'=>0,'E'=>0,'% PASS'=>0,'F'=>0,'G'=>0,'U'=>0,'TOTAL'=>0,
                '_group_total' => true,
            ];
            $collectKeys = ['A*','A','B','C','D','E','F','G','U','TOTAL'];
            foreach ($bucketRows as $r) {
                foreach ($collectKeys as $k) { $sub[$k] += $r[$k]; }
            }
            $finalize($sub);
            $groupedOutput[] = $sub;
        };

        foreach ($rows as $r) {
            if ($currentTeacher === null) {
                $currentTeacher = $r['TEACHER'];
                $bucket = [$r];
                continue;
            }
            if (strcasecmp($currentTeacher, $r['TEACHER']) === 0) {
                $bucket[] = $r;
            } else {
                $emitSubtotal($currentTeacher, $bucket);
                $currentTeacher = $r['TEACHER'];
                $bucket = [$r];
            }
        }

        $emitSubtotal($currentTeacher, $bucket);

        if ($request->query('export') === 'excel') {
            return Excel::download(
                new SubjectTeacherGradeAnalysisExport($groupedOutput, $test1),
                'subject_teacher_grade_analysis_report.xlsx'
            );
        }

        return view('assessment.shared.grade-teachers-analysis', [
            'reportData'  => $groupedOutput,
            'school_data' => SchoolSetup::first(),
            'test'        => $test1,
        ]);
    }

    /**
     * Teacher-by-Teacher Value Addition Analysis (per-test).
     * Grouped by subject, each row = teacher + class with VA columns.
     */
    public function showTeacherValueAdditionAnalysis(Request $request, $classId, $sequence, $type) {
        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        $test1 = Test::query()
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->where('sequence', $sequence)
            ->where('type', $type)
            ->first();

        $jceGrades = ['A', 'B', 'C', 'D', 'E', 'U'];
        $seniorGrades = ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'];

        // Load VA subject mappings
        $mappings = ValueAdditionSubjectMapping::where('school_type', 'Senior')
            ->where('exam_type', 'JCE')
            ->where('is_active', true)
            ->get()
            ->keyBy('subject_id')
            ->map(fn($m) => $m->source_key)
            ->toArray();

        $report = [];

        // --- Core subjects via KlassSubject ---
        $klassSubjects = KlassSubject::query()
            ->where('term_id', $termId)
            ->whereHas('gradeSubject', fn($q) => $q->where('grade_id', $gradeId))
            ->with([
                'klass:id,name,grade_id',
                'klass.students' => fn($q) => $q->wherePivot('term_id', $termId)->with('jce'),
                'gradeSubject.subject:id,name,is_double',
                'teacher:id,firstname,lastname',
            ])->get();

        foreach ($klassSubjects as $ks) {
            $className = $ks->klass->name ?? '—';
            $subjectName = optional($ks->gradeSubject->subject)->name ?? '—';
            $subjectId = $ks->gradeSubject->subject_id ?? 0;
            $teacherName = $ks->teacher ? trim(($ks->teacher->firstname ?? '') . ' ' . ($ks->teacher->lastname ?? '')) : '—';
            $isDouble = (bool) ($ks->gradeSubject->subject->is_double ?? false);

            $rowKey = "{$teacherName}_{$className}_{$subjectName}";
            if (isset($report[$rowKey])) continue;

            // Get test IDs for this subject/sequence/type
            $testIds = Test::query()
                ->where('grade_subject_id', $ks->grade_subject_id)
                ->where('term_id', $termId)
                ->where('sequence', $sequence)
                ->where('type', $type)
                ->pluck('id');

            $classStudents = $ks->klass->students;
            $classStudentIds = $classStudents->pluck('id')->all();

            // Grade distribution
            $dist = array_fill_keys($seniorGrades, 0);
            $dist['X'] = 0;
            $total = 0;

            if ($testIds->isNotEmpty() && !empty($classStudentIds)) {
                $finalByStudent = [];
                StudentTest::query()
                    ->whereIn('test_id', $testIds)
                    ->select(['id', 'student_id', 'grade', 'updated_at'])
                    ->orderBy('updated_at', 'desc')
                    ->chunk(2000, function ($chunk) use (&$finalByStudent, $seniorGrades, $isDouble) {
                        foreach ($chunk as $st) {
                            if (!isset($finalByStudent[$st->student_id])) {
                                $g = $st->grade ?? '';
                                if ($g === '') continue;
                                if ($isDouble && is_string($g) && strlen($g) === 2) {
                                    $finalByStudent[$st->student_id] = $g;
                                } else {
                                    $finalByStudent[$st->student_id] = in_array($g, $seniorGrades, true) ? $g : 'U';
                                }
                            }
                        }
                    });

                foreach ($classStudentIds as $sid) {
                    if (!isset($finalByStudent[$sid])) {
                        $slots = $isDouble ? 2 : 1;
                        $dist['X'] += $slots;
                        $total += $slots;
                        continue;
                    }
                    $g = $finalByStudent[$sid];
                    if ($isDouble && is_string($g) && strlen($g) === 2) {
                        foreach (str_split($g) as $char) {
                            $mapped = in_array($char, $seniorGrades, true) ? $char : 'U';
                            if (isset($dist[$mapped])) $dist[$mapped]++;
                            else $dist['U']++;
                            $total++;
                        }
                    } else {
                        if (isset($dist[$g])) $dist[$g]++;
                        else $dist['X']++;
                        $total++;
                    }
                }
            }

            // ABC% and AE%
            $t = max(1, $total);
            $abcCount = $dist['A*'] + $dist['A'] + $dist['B'] + $dist['C'];
            $aeCount = $abcCount + $dist['D'] + $dist['E'];
            $abcPercent = round(($abcCount / $t) * 100, 1);
            $aePercent = round(($aeCount / $t) * 100, 1);

            // JCE baseline scoped to students in this class
            $sourceKey = $mappings[$subjectId] ?? null;
            $jcTotal = 0;
            $jcAbcCount = 0;
            foreach ($classStudents as $student) {
                if (!$student->jce) continue;
                $column = $sourceKey ?? 'overall';
                $grade = $student->jce->{$column} ?? null;
                if (!$grade) continue;
                $grade = trim($grade);
                if ($grade === 'Merit') $grade = 'A';
                if (in_array($grade, $jceGrades)) {
                    $jcTotal++;
                    if (in_array($grade, ['A', 'B', 'C'])) $jcAbcCount++;
                }
            }
            $jcAbcPercent = $jcTotal > 0 ? round(($jcAbcCount / $jcTotal) * 100, 1) : 0;
            $va = round($abcPercent - $jcAbcPercent, 1);

            $report[$rowKey] = [
                'teacher' => $teacherName,
                'class' => $className,
                'subject' => $subjectName,
                'A*' => $dist['A*'], 'A' => $dist['A'], 'B' => $dist['B'], 'C' => $dist['C'],
                'D' => $dist['D'], 'E' => $dist['E'], 'F' => $dist['F'], 'G' => $dist['G'],
                'U' => $dist['U'], 'X' => $dist['X'],
                'total' => $total,
                'abcPercent' => $abcPercent,
                'aePercent' => $aePercent,
                'jcAbcPercent' => $jcAbcPercent,
                'va' => $va,
                '_subject_total' => false,
            ];
        }

        // --- Optional subjects ---
        $optionalSubjects = OptionalSubject::query()
            ->where('term_id', $termId)
            ->whereHas('gradeSubject', fn($q) => $q->where('grade_id', $gradeId))
            ->with([
                'gradeSubject.subject:id,name,is_double',
                'teacher:id,firstname,lastname',
            ])->get();

        foreach ($optionalSubjects as $opt) {
            $subjectName = optional($opt->gradeSubject->subject)->name ?? '—';
            $subjectId = $opt->gradeSubject->subject_id ?? 0;
            $teacherName = $opt->teacher ? trim(($opt->teacher->firstname ?? '') . ' ' . ($opt->teacher->lastname ?? '')) : '—';
            $optionalDisplayName = $opt->name ?? '—';
            $isDouble = (bool) ($opt->gradeSubject->subject->is_double ?? false);

            $rowKey = "OPT_{$opt->id}_T{$opt->teacher_id}";
            if (isset($report[$rowKey])) continue;

            $testIds = Test::query()
                ->where('grade_subject_id', $opt->grade_subject_id)
                ->where('term_id', $termId)
                ->where('sequence', $sequence)
                ->where('type', $type)
                ->pluck('id');

            $studentIds = $opt->students()->wherePivot('term_id', $termId)->distinct('students.id')->pluck('students.id')->all();
            $students = Student::whereIn('id', $studentIds)->with('jce')->get();

            $dist = array_fill_keys($seniorGrades, 0);
            $dist['X'] = 0;
            $total = 0;

            if ($testIds->isNotEmpty() && !empty($studentIds)) {
                $finalByStudent = [];
                StudentTest::query()
                    ->whereIn('test_id', $testIds)
                    ->select(['id', 'student_id', 'grade', 'updated_at'])
                    ->orderBy('updated_at', 'desc')
                    ->chunk(2000, function ($chunk) use (&$finalByStudent, $seniorGrades, $isDouble) {
                        foreach ($chunk as $st) {
                            if (!isset($finalByStudent[$st->student_id])) {
                                $g = $st->grade ?? '';
                                if ($g === '') continue;
                                if ($isDouble && is_string($g) && strlen($g) === 2) {
                                    $finalByStudent[$st->student_id] = $g;
                                } else {
                                    $finalByStudent[$st->student_id] = in_array($g, $seniorGrades, true) ? $g : 'U';
                                }
                            }
                        }
                    });

                foreach ($studentIds as $sid) {
                    if (!isset($finalByStudent[$sid])) {
                        $slots = $isDouble ? 2 : 1;
                        $dist['X'] += $slots;
                        $total += $slots;
                        continue;
                    }
                    $g = $finalByStudent[$sid];
                    if ($isDouble && is_string($g) && strlen($g) === 2) {
                        foreach (str_split($g) as $char) {
                            $mapped = in_array($char, $seniorGrades, true) ? $char : 'U';
                            if (isset($dist[$mapped])) $dist[$mapped]++;
                            else $dist['U']++;
                            $total++;
                        }
                    } else {
                        if (isset($dist[$g])) $dist[$g]++;
                        else $dist['X']++;
                        $total++;
                    }
                }
            }

            $t = max(1, $total);
            $abcCount = $dist['A*'] + $dist['A'] + $dist['B'] + $dist['C'];
            $aeCount = $abcCount + $dist['D'] + $dist['E'];
            $abcPercent = round(($abcCount / $t) * 100, 1);
            $aePercent = round(($aeCount / $t) * 100, 1);

            $sourceKey = $mappings[$subjectId] ?? null;
            $jcTotal = 0;
            $jcAbcCount = 0;
            foreach ($students as $student) {
                if (!$student->jce) continue;
                $column = $sourceKey ?? 'overall';
                $grade = $student->jce->{$column} ?? null;
                if (!$grade) continue;
                $grade = trim($grade);
                if ($grade === 'Merit') $grade = 'A';
                if (in_array($grade, $jceGrades)) {
                    $jcTotal++;
                    if (in_array($grade, ['A', 'B', 'C'])) $jcAbcCount++;
                }
            }
            $jcAbcPercent = $jcTotal > 0 ? round(($jcAbcCount / $jcTotal) * 100, 1) : 0;
            $va = round($abcPercent - $jcAbcPercent, 1);

            $report[$rowKey] = [
                'teacher' => $teacherName,
                'class' => $optionalDisplayName,
                'subject' => $subjectName,
                'A*' => $dist['A*'], 'A' => $dist['A'], 'B' => $dist['B'], 'C' => $dist['C'],
                'D' => $dist['D'], 'E' => $dist['E'], 'F' => $dist['F'], 'G' => $dist['G'],
                'U' => $dist['U'], 'X' => $dist['X'],
                'total' => $total,
                'abcPercent' => $abcPercent,
                'aePercent' => $aePercent,
                'jcAbcPercent' => $jcAbcPercent,
                'va' => $va,
                '_subject_total' => false,
            ];
        }

        // Group by subject, add Department Overall subtotals
        $rows = array_values($report);
        usort($rows, function ($a, $b) {
            $s = strcasecmp($a['subject'], $b['subject']);
            if ($s !== 0) return $s;
            return strcasecmp($a['class'], $b['class']);
        });

        $subjectGroups = [];
        $currentSubject = null;
        $bucket = [];

        $buildGroup = function ($subjectName, $bucketRows) {
            if (empty($bucketRows)) return null;

            $sub = [
                'teacher' => 'Department Overall',
                'class' => '',
                'subject' => $subjectName,
                'A*' => 0, 'A' => 0, 'B' => 0, 'C' => 0,
                'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0,
                'U' => 0, 'X' => 0,
                'total' => 0,
                'abcPercent' => 0,
                'aePercent' => 0,
                'jcAbcPercent' => 0,
                'va' => 0,
                '_subject_total' => true,
            ];
            $gradeKeys = ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U', 'X', 'total'];
            foreach ($bucketRows as $r) {
                foreach ($gradeKeys as $k) {
                    $sub[$k] += $r[$k];
                }
            }
            $t = max(1, $sub['total']);
            $abcCount = $sub['A*'] + $sub['A'] + $sub['B'] + $sub['C'];
            $aeCount = $abcCount + $sub['D'] + $sub['E'];
            $sub['abcPercent'] = round(($abcCount / $t) * 100, 1);
            $sub['aePercent'] = round(($aeCount / $t) * 100, 1);

            $totalStudents = 0;
            $totalJcAbc = 0;
            foreach ($bucketRows as $r) {
                $totalStudents += $r['total'];
                $totalJcAbc += ($r['jcAbcPercent'] / 100) * $r['total'];
            }
            $sub['jcAbcPercent'] = $totalStudents > 0 ? round(($totalJcAbc / $totalStudents) * 100, 1) : 0;
            $sub['va'] = round($sub['abcPercent'] - $sub['jcAbcPercent'], 1);

            return [
                'name' => $subjectName,
                'rows' => $bucketRows,
                'total' => $sub,
            ];
        };

        foreach ($rows as $r) {
            if ($currentSubject === null) {
                $currentSubject = $r['subject'];
                $bucket = [$r];
                continue;
            }
            if (strcasecmp($currentSubject, $r['subject']) === 0) {
                $bucket[] = $r;
            } else {
                $group = $buildGroup($currentSubject, $bucket);
                if ($group) $subjectGroups[] = $group;
                $currentSubject = $r['subject'];
                $bucket = [$r];
            }
        }
        $group = $buildGroup($currentSubject, $bucket);
        if ($group) $subjectGroups[] = $group;

        if ($request->query('export') === 'excel') {
            return Excel::download(
                new TeacherValueAdditionExport($subjectGroups, $test1),
                'teacher_value_addition_analysis.xlsx'
            );
        }

        return view('assessment.senior.teacher-value-addition-analysis', [
            'subjectGroups' => $subjectGroups,
            'school_data' => SchoolSetup::first(),
            'test' => $test1,
        ]);
    }

    /**
     * Show subject grade analysis for senior school
     */
    public function showSubjectGradeAnalysis(Request $request, $classId, $sequenceId, $type){
        if ($request->query('export') === 'excel') {
            $gradeSubjectData = $this->generateSubjectGradeReport($classId, $sequenceId, $type);
            return Excel::download(new GradeSubjectAnalysisExport($gradeSubjectData), 'grade_subject_analysis_report.xlsx');
        }

        $subjectData = $this->generateSubjectGradeReport($classId, $sequenceId, $type);
        return view('houses.grade-subjects-analysis', ['subjectData' => $subjectData]);
    }

    public function generateSubjectGradeReport($classId, $sequence, $type){
        $klass = Klass::findOrfail($classId);
        $gradeId = $klass->grade_id;
        $cacheKey = "subject_grade_report_{$gradeId}_{$sequence}_{$type}";
        $cacheDuration = 60 * 24;

        return Cache::remember($cacheKey, $cacheDuration, function () use ($gradeId, $sequence, $type) {
            $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

            $tests = Test::where('grade_id', $gradeId)
                ->where('sequence', $sequence)
                ->where('type', $type)
                ->where('term_id', $termId)
                ->with(['subject.subject', 'studentTests'])
                ->get();

            $subjectData = [];
            foreach ($tests as $test) {
                $subjectName = $test->subject->subject->name;

                if (!isset($subjectData[$subjectName])) {
                    $subjectData[$subjectName] = $this->initializeSubjectRow($subjectName);
                }

                foreach ($test->studentTests as $studentTest) {
                    $grade = $studentTest->grade;
                    if (isset($subjectData[$subjectName][$grade])) {
                        $subjectData[$subjectName][$grade]++;
                    } elseif ($grade == 'U') {
                        $subjectData[$subjectName]['U']++;
                    }
                    $subjectData[$subjectName]['TOTAL']++;
                }
            }

            foreach ($subjectData as &$data) {
                $this->calculateSubjectPercentages($data);
            }

            uasort($subjectData, function ($a, $b) {
                return $b['% CREDIT'] <=> $a['% CREDIT'];
            });

            $totalRow = $this->calculateTotalSubjectRow($subjectData);
            $subjectData['TOTAL'] = $totalRow;
            return $subjectData;
        });
    }

    private function initializeSubjectRow($subjectName){
        return [
            'SUBJECT' => $subjectName,
            'A*' => 0,
            'A' => 0,
            'B' => 0,
            'C' => 0,
            '% CREDIT' => 0,
            'D' => 0,
            'E' => 0,
            '% PASS' => 0,
            'F' => 0,
            'G' => 0,
            'U' => 0,
            'TOTAL' => 0
        ];
    }

    private function calculateSubjectPercentages(&$data){
        $credits = $data['A*'] + $data['A'] + $data['B'] + $data['C'];
        $passes = $credits + $data['D'] + $data['E'];
        $data['% CREDIT'] = $data['TOTAL'] > 0 ? round(($credits / $data['TOTAL']) * 100, 1) : 0;
        $data['% PASS'] = $data['TOTAL'] > 0 ? round(($passes / $data['TOTAL']) * 100, 1) : 0;
    }

    private function calculateTotalSubjectRow($subjectData){
        $totalRow = $this->initializeSubjectRow('TOTAL');

        foreach ($subjectData as $data) {
            foreach ($data as $key => $value) {
                if ($key != 'SUBJECT' && $key != '% CREDIT' && $key != '% PASS') {
                    $totalRow[$key] += $value;
                }
            }
        }
        $this->calculateSubjectPercentages($totalRow);
        return $totalRow;
    }

    /**
     * Generate class credits summary for senior school
     */
    public function generateClassCreditsSummary(Request $request, $classId, $type, $sequence){
        $termId = session('selected_term_id', TermHelper::getCurrentTerm()->id);

        $klass = Klass::findOrFail($classId);
        $test1 = Test::where('term_id', $termId)->where('type', $type)->where('sequence', $sequence)->where('grade_id', $klass->grade_id)->first();
        $classes = Klass::where('grade_id', $klass->grade_id)->get();

        $school_setup = SchoolSetup::first();
        $summary = [];
        $overall = [
            'name' => 'Overall',
            'students' => 0,
            'gte_6_credits' => 0,
            'gte_5_credits' => 0,
            'male_gte_6' => 0,
            'female_gte_6' => 0,
            'male_gte_5' => 0,
            'female_gte_5' => 0,
            'male_count' => 0,
            'female_count' => 0
        ];

        foreach ($classes as $class) {
            $classData = [
                'name' => $class->name,
                'students' => 0,
                'gte_6_credits' => 0,
                'gte_5_credits' => 0,
                'male_gte_6' => 0,
                'female_gte_6' => 0,
                'male_gte_5' => 0,
                'female_gte_5' => 0,
                'male_count' => 0,
                'female_count' => 0
            ];

            $students = $class->students()->wherePivot('term_id', $termId)->get();
            foreach ($students as $student) {
                $creditCount = $this->calculateStudentCredits($student, $termId, $type, $sequence);
                $classData['students']++;
                $overall['students']++;

                if ($student->gender === 'M') {
                    $classData['male_count']++;
                    $overall['male_count']++;
                } else {
                    $classData['female_count']++;
                    $overall['female_count']++;
                }

                if ($creditCount >= 6) {
                    $classData['gte_6_credits']++;
                    $overall['gte_6_credits']++;
                    $student->gender === 'M' ? $classData['male_gte_6']++ : $classData['female_gte_6']++;
                    $student->gender === 'M' ? $overall['male_gte_6']++ : $overall['female_gte_6']++;
                }

                if ($creditCount >= 5) {
                    $classData['gte_5_credits']++;
                    $overall['gte_5_credits']++;
                    $student->gender === 'M' ? $classData['male_gte_5']++ : $classData['female_gte_5']++;
                    $student->gender === 'M' ? $overall['male_gte_5']++ : $overall['female_gte_5']++;
                }
            }

            $summary[] = $classData;
        }
        $summary[] = $overall;
        if ($request->query('export') === 'excel') {
            return Excel::download(new ClassCreditsSummaryExport($summary,$test1), 'class_credits_summary_report.xlsx');
        }

        return view('assessment.senior.credits-senior-subject-analysis', [
            'summary' => $summary,
            'type' => $type,
            'school_data' => $school_setup,
            'test' => $test1
        ]);
    }

    private function calculateStudentCredits($student, $termId, $type, $sequence){
        $credits = 0;
        $tests = $student->tests()
            ->where('term_id', $termId)
            ->where('type', $type)
            ->where('sequence', $sequence)
            ->with('subject.subject')
            ->get();

        foreach ($tests as $test) {
            $grade = $test->pivot->grade;
            $isDouble = (bool) ($test->subject->subject->is_double ?? false);

            if ($isDouble && strlen($grade) == 2) {
                // Double subject: each grade letter counted separately
                if (in_array($grade[0], ['A', 'B', 'C'])) $credits++;
                if (in_array($grade[1], ['A', 'B', 'C'])) $credits++;
            } else {
                if (in_array($grade, ['A*', 'A', 'B', 'C'])) {
                    $credits++;
                }
            }
        }
        return $credits;
    }

    /**
     * Generate house credits report for senior school
     */
    public function generateHouseCreditsReport(Request $request, $sequenceId, $type) {
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();

        $test = Test::where('term_id', $selectedTermId)->where('type', $type)->where('sequence', $sequenceId)->first();
        $houses = House::with([
            'students' => function($query) use ($selectedTermId) {
                $query->wherePivot('term_id', $selectedTermId);
            },
            'houseHead',
            'houseAssistant'
        ])->where('term_id', $selectedTermId)->get();

        $houseData = [];
        $creditCategories = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0];
        $cumulativeThresholds = [9, 8, 7, 6, 5, 4, 3];
        $totalStats = [
            'classSize' => 0,
            'total' => 0,
            'credits' => array_fill_keys($creditCategories, 0),
            'pointsGte34' => 0,
            'pointsGte46' => 0
        ];

        $klasses = Klass::where('term_id', $selectedTermId)->with('teacher', 'students')->get();
        $studentClassMap = [];
        $classTeacherMap = [];
        $classSizeMap = [];

        foreach ($klasses as $klass) {
            $classTeacherMap[$klass->id] = [
                'name' => $klass->name,
                'teacher' => $klass->teacher ? $klass->teacher->full_name : 'No Teacher Assigned'
            ];
            $classSizeMap[$klass->id] = $klass->students->count();

            foreach ($klass->students as $student) {
                if ($student->pivot && $student->pivot->active && $student->pivot->term_id == $selectedTermId) {
                    $studentClassMap[$student->id] = $klass->id;
                }
            }
        }

        foreach ($houses as $house) {
            $houseName = $house->name;
            $houseHead = $house->houseHead ? $house->houseHead->full_name : 'No Head Assigned';

            $houseData[$houseName] = [
                'houseHead' => $houseHead,
                'stats' => [
                    'classSize' => 0,
                    'total' => 0,
                    'credits' => array_fill_keys($creditCategories, 0),
                    'pointsGte34' => 0,
                    'pointsGte46' => 0
                ],
                'classes' => []
            ];

            $classesInHouse = [];
            foreach ($house->students as $student) {
                if ($student->pivot->term_id != $selectedTermId) {
                    continue;
                }

                $classId = $studentClassMap[$student->id] ?? null;
                if (!$classId) {
                    continue;
                }

                $className = $classTeacherMap[$classId]['name'];
                $classTeacher = $classTeacherMap[$classId]['teacher'];

                if (!isset($classesInHouse[$className])) {
                    $classesInHouse[$className] = [
                        'teacher' => $classTeacher,
                        'classSize' => 0,
                        'total' => 0,
                        'credits' => array_fill_keys($creditCategories, 0),
                        'pointsGte34' => 0,
                        'pointsGte46' => 0
                    ];
                }

                // Count every student in the house+class as classSize
                $classesInHouse[$className]['classSize']++;
                $houseData[$houseName]['stats']['classSize']++;
                $totalStats['classSize']++;

                $scores = $this->calculateStudentScoresSeniorCA($student, $selectedTermId, $type, $sequenceId);

                if (empty($scores)) {
                    continue;
                }

                $creditsCount = 0;
                foreach ($scores as $score) {
                    $grade = trim($score['grade'] ?? '');

                    if (empty($grade)) {
                        continue;
                    }

                    $isDouble = $score['is_double'] ?? false;
                    if ($isDouble && strlen($grade) == 2) {
                        if (in_array($grade[0], ['A', 'B', 'C'])) $creditsCount++;
                        if (in_array($grade[1], ['A', 'B', 'C'])) $creditsCount++;
                    } elseif (in_array($grade, ['A*', 'A', 'B', 'C'])) {
                        $creditsCount++;
                    }
                }

                // Best-6 total points with double subject slot awareness
                $topSixPoints = 0;
                $totalSlots = 0;
                foreach ($scores as $score) {
                    $isDouble = $score['is_double'] ?? false;
                    $slotsNeeded = $isDouble ? 2 : 1;
                    if ($totalSlots + $slotsNeeded <= 6) {
                        $totalSlots += $slotsNeeded;
                        $topSixPoints += $isDouble ? ($score['points'] ?? 0) * 2 : ($score['points'] ?? 0);
                    }
                    if ($totalSlots >= 6) break;
                }

                $classesInHouse[$className]['total']++;
                $creditsKey = min(10, $creditsCount);
                $classesInHouse[$className]['credits'][$creditsKey]++;

                if ($topSixPoints >= 34) {
                    $classesInHouse[$className]['pointsGte34']++;
                }
                if ($topSixPoints >= 46) {
                    $classesInHouse[$className]['pointsGte46']++;
                }

                $houseData[$houseName]['stats']['total']++;
                $houseData[$houseName]['stats']['credits'][$creditsKey]++;

                if ($topSixPoints >= 34) {
                    $houseData[$houseName]['stats']['pointsGte34']++;
                }
                if ($topSixPoints >= 46) {
                    $houseData[$houseName]['stats']['pointsGte46']++;
                }

                $totalStats['total']++;
                $totalStats['credits'][$creditsKey]++;

                if ($topSixPoints >= 34) {
                    $totalStats['pointsGte34']++;
                }
                if ($topSixPoints >= 46) {
                    $totalStats['pointsGte46']++;
                }
            }

            $houseData[$houseName]['classes'] = $classesInHouse;
        }

        // Compute cumulative bands for each class, house total, and school total
        $computeCumulative = function (array $credits, int $numWrote) use ($cumulativeThresholds) {
            $cumNo = [];
            $cumPct = [];
            $running = 0;
            for ($k = 10; $k >= 3; $k--) {
                $running += $credits[$k] ?? 0;
                if (in_array($k, $cumulativeThresholds)) {
                    $cumNo[$k] = $running;
                    $cumPct[$k] = $numWrote > 0 ? round(($running / $numWrote) * 100, 2) : 0;
                }
            }
            return ['no' => $cumNo, 'pct' => $cumPct];
        };

        foreach ($houseData as $houseName => &$data) {
            foreach ($data['classes'] as $className => &$classStats) {
                $classStats['cumulative'] = $computeCumulative($classStats['credits'], $classStats['total']);
            }
            unset($classStats);
            $data['stats']['cumulative'] = $computeCumulative($data['stats']['credits'], $data['stats']['total']);
        }
        unset($data);
        $totalStats['cumulative'] = $computeCumulative($totalStats['credits'], $totalStats['total']);

        if ($request->query('export') === 'excel') {
            return Excel::download(
                new HouseCreditsPerformanceExport([
                    'type' => $type,
                    'test' => $test,
                    'sequence' => $sequenceId,
                    'houseData' => $houseData,
                    'creditCategories' => $creditCategories,
                    'cumulativeThresholds' => $cumulativeThresholds,
                    'totalStats' => $totalStats
                ]),
                'houses_credits_performance_analysis.xlsx'
            );
        }

        return view('assessment.senior.credits-analysis-house-senior', [
            'type' => $type,
            'test' => $test,
            'sequence' => $sequenceId,
            'houseData' => $houseData,
            'creditCategories' => $creditCategories,
            'cumulativeThresholds' => $cumulativeThresholds,
            'totalStats' => $totalStats,
            'school_data' => $school_setup
        ]);
    }

    public function generateJCEHouseGradeDistribution($classId) {
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $school_setup = SchoolSetup::first();

        $klass = Klass::findOrFail($classId);
        $gradeId = $klass->grade_id;
        $grade = Grade::findOrFail($gradeId);

        $gradeCategories = ['Merit', 'A', 'B', 'C', 'D', 'E', 'U'];

        $houses = House::with([
            'students' => function ($query) use ($selectedTermId, $gradeId) {
                $query->wherePivot('term_id', $selectedTermId)
                    ->whereHas('studentTerms', function ($q) use ($selectedTermId) {
                        $q->where('term_id', $selectedTermId)->where('status', 'Current');
                    })
                    ->whereHas('currentClassRelation', function ($q) use ($selectedTermId, $gradeId) {
                        $q->where('klasses.term_id', $selectedTermId)
                            ->where('klasses.grade_id', $gradeId);
                    })
                    ->with([
                        'currentClassRelation' => function ($q) use ($selectedTermId, $gradeId) {
                            $q->select('klasses.id', 'klasses.name', 'klasses.type', 'klasses.grade_id')
                                ->where('klasses.term_id', $selectedTermId)
                                ->where('klasses.grade_id', $gradeId);
                        },
                        'jce',
                    ]);
            },
        ])->where('term_id', $selectedTermId)->get();

        $houseData = [];
        $schoolTotals = [
            'total' => 0,
            'grades' => array_fill_keys($gradeCategories, 0),
        ];

        foreach ($houses as $house) {
            $houseName = $house->name;
            $houseTotals = [
                'total' => 0,
                'grades' => array_fill_keys($gradeCategories, 0),
            ];
            $classesInHouse = [];

            foreach ($house->students as $student) {
                $studentClass = $student->currentClassRelation->first();
                if (!$studentClass) {
                    continue;
                }

                $className = $studentClass->name;

                if (!isset($classesInHouse[$className])) {
                    $classesInHouse[$className] = [
                        'total' => 0,
                        'grades' => array_fill_keys($gradeCategories, 0),
                    ];
                }

                $classesInHouse[$className]['total']++;
                $houseTotals['total']++;
                $schoolTotals['total']++;

                if ($student->jce && $student->jce->overall) {
                    $overall = trim($student->jce->overall);
                    if (in_array($overall, $gradeCategories)) {
                        $classesInHouse[$className]['grades'][$overall]++;
                        $houseTotals['grades'][$overall]++;
                        $schoolTotals['grades'][$overall]++;
                    }
                }
            }

            ksort($classesInHouse);

            $houseData[$houseName] = [
                'classes' => $classesInHouse,
                'totals' => $houseTotals,
            ];
        }

        $term = Term::find($selectedTermId);
        $year = $term ? $term->year : date('Y');

        return view('assessment.senior.jce-house-grade-distribution', [
            'houseData' => $houseData,
            'schoolTotals' => $schoolTotals,
            'gradeCategories' => $gradeCategories,
            'school_data' => $school_setup,
            'gradeName' => $grade->name,
            'year' => $year,
        ]);
    }

    public function generateValueAdditionReport($classId) {
        $service = app(ValueAdditionService::class);
        $data = $service->generateReport($classId);

        return view('assessment.senior.value-addition-report', $data);
    }

    public function exportValueAdditionReport($classId) {
        $service = app(ValueAdditionService::class);
        $data = $service->generateReport($classId);

        return Excel::download(
            new ValueAdditionExport($data),
            'value_addition_report_' . $data['gradeName'] . '_' . $data['year'] . '.xlsx'
        );
    }

    public function generateHouse6CTrackingReport(Request $request, $classId) {
        $klass = Klass::findOrFail($classId);
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $selectedTerm = Term::find($selectedTermId) ?? $currentTerm;
        $school_setup = SchoolSetup::first();
        $gradeD = Grade::findOrFail($klass->grade_id);

        // Determine cohort start level and year
        $currentLevel = (int) $gradeD->level;
        if ($currentLevel === 5) {
            $startLevel = 4;
            $startYear = $selectedTerm->year - 1;
        } else {
            $startLevel = $currentLevel;
            $startYear = $selectedTerm->year;
        }

        // Pre-load JCE subject mappings
        $jceMappings = ValueAdditionSubjectMapping::where('exam_type', 'JCE')
            ->where('is_active', true)
            ->pluck('source_key', 'subject_id');

        $jceSubjectColumns = [
            'mathematics', 'english', 'science', 'setswana',
            'design_and_technology', 'home_economics', 'agriculture',
            'social_studies', 'moral_education', 'religious_education',
            'music', 'physical_education', 'art', 'office_procedures',
            'accounting', 'french',
        ];

        // Discover all test periods from startYear onward
        $terms = Term::where('year', '>=', $startYear)
            ->where('year', '<=', $selectedTerm->year)
            ->orderBy('year', 'asc')
            ->orderBy('term', 'asc')
            ->get();

        $testPeriods = [];
        foreach ($terms as $term) {
            $expectedLevel = $startLevel + ($term->year - $startYear);
            $grade = Grade::where('level', $expectedLevel)
                ->where('term_id', $term->id)
                ->first();

            if (!$grade) {
                continue;
            }

            // Get unique CA test sequences for this grade/term
            $caTests = Test::where('term_id', $term->id)
                ->where('grade_id', $grade->id)
                ->where('type', 'CA')
                ->select('sequence', 'name')
                ->groupBy('sequence', 'name')
                ->orderBy('sequence', 'asc')
                ->get();

            foreach ($caTests as $caTest) {
                $testPeriods[] = [
                    'label' => strtoupper($caTest->name) . ' ' . $term->year,
                    'term_id' => $term->id,
                    'sequence' => $caTest->sequence,
                    'type' => 'CA',
                    'grade_id' => $grade->id,
                ];
            }

            // Check for Exam test
            $examTest = Test::where('term_id', $term->id)
                ->where('grade_id', $grade->id)
                ->where('type', 'Exam')
                ->first();

            if ($examTest) {
                $testPeriods[] = [
                    'label' => 'END OF TERM ' . $term->term . ' ' . $term->year,
                    'term_id' => $term->id,
                    'sequence' => $examTest->sequence,
                    'type' => 'Exam',
                    'grade_id' => $grade->id,
                ];
            }
        }

        // For each test period, calculate house → class stats
        $housesData = [];
        $grandTotal = [];

        foreach ($testPeriods as $tpIndex => $testPeriod) {
            $tpTermId = $testPeriod['term_id'];
            $tpGradeId = $testPeriod['grade_id'];
            $tpType = $testPeriod['type'];
            $tpSequence = $testPeriod['sequence'];

            // Get houses for this term
            $houses = House::with([
                'students' => fn($q) => $q->wherePivot('term_id', $tpTermId),
            ])->where('term_id', $tpTermId)->get();

            // Get klasses for this grade/term
            $klasses = Klass::where('grade_id', $tpGradeId)
                ->where('term_id', $tpTermId)
                ->with(['students.tests.subject', 'students.jce'])
                ->get();

            // Build student → class map and student lookup
            $studentClassMap = [];
            $studentLookup = [];
            foreach ($klasses as $klassItem) {
                foreach ($klassItem->students as $student) {
                    if ($student->pivot->active && $student->pivot->term_id == $tpTermId) {
                        $studentClassMap[$student->id] = $klassItem->name;
                        $studentLookup[$student->id] = [
                            'student' => $student,
                            'klass_id' => $klassItem->id,
                        ];
                    }
                }
            }

            $grandTotalData = [
                'size' => 0, 'noSat' => 0, 'no6c' => 0,
                'jceNo6c' => 0, 'jceSatCount' => 0,
            ];

            foreach ($houses as $house) {
                $houseName = $house->name;
                if (!isset($housesData[$houseName])) {
                    $housesData[$houseName] = [];
                }

                $classStats = [];

                foreach ($house->students as $houseStudent) {
                    if (!isset($studentClassMap[$houseStudent->id])) {
                        continue;
                    }

                    $className = $studentClassMap[$houseStudent->id];
                    $lookupData = $studentLookup[$houseStudent->id];
                    $studentInstance = $lookupData['student'];

                    if (!isset($classStats[$className])) {
                        $classStats[$className] = [
                            'name' => $className,
                            'size' => 0,
                            'noSat' => 0,
                            'no6c' => 0,
                            'jceNo6c' => 0,
                            'jceSatCount' => 0,
                        ];
                    }

                    $classStats[$className]['size']++;

                    // Calculate scores for this test period
                    $scores = $this->calculateStudentScoresSeniorCA(
                        $studentInstance, $tpTermId, $tpType, $tpSequence
                    );

                    if (empty($scores)) {
                        continue;
                    }

                    $classStats[$className]['noSat']++;

                    // Count credits (6C's logic)
                    $creditsCount = 0;
                    $enrolledSubjectIds = [];
                    foreach ($scores as $score) {
                        $grade = trim($score['grade'] ?? '');
                        if (empty($grade)) {
                            continue;
                        }

                        // Track enrolled subjects for JCE mapping
                        if (isset($score['test']) && $score['test']) {
                            $enrolledSubjectIds[] = $score['test']->grade_subject_id;
                        }

                        $isDouble = $score['is_double'] ?? false;
                        if ($isDouble && strlen($grade) == 2) {
                            if (in_array($grade[0], ['A', 'B', 'C'])) $creditsCount++;
                            if (in_array($grade[1], ['A', 'B', 'C'])) $creditsCount++;
                        } elseif (in_array($grade, ['A*', 'A', 'B', 'C'])) {
                            $creditsCount++;
                        }
                    }

                    if ($creditsCount >= 6) {
                        $classStats[$className]['no6c']++;
                    }

                    // JCE 6C's calculation using subject mappings
                    if ($studentInstance->jce) {
                        $jceCredits = 0;
                        $jceHasData = false;

                        if (!empty($enrolledSubjectIds)) {
                            foreach ($enrolledSubjectIds as $subjectId) {
                                $sourceKey = $jceMappings[$subjectId] ?? null;
                                $jceGrade = null;

                                if ($sourceKey && $studentInstance->jce->{$sourceKey} !== null) {
                                    $jceGrade = $studentInstance->jce->{$sourceKey};
                                    $jceHasData = true;
                                }

                                if ($jceGrade && in_array($jceGrade, ['A', 'B', 'C'])) {
                                    $jceCredits++;
                                }
                            }
                        }

                        // Fallback: if no mapped data found, check all JCE columns
                        if (!$jceHasData) {
                            $allNull = true;
                            foreach ($jceSubjectColumns as $col) {
                                if ($studentInstance->jce->{$col} !== null) {
                                    $allNull = false;
                                    $jceGrade = $studentInstance->jce->{$col};
                                    if (in_array($jceGrade, ['A', 'B', 'C'])) {
                                        $jceCredits++;
                                    }
                                }
                            }

                            // If all columns null but overall exists, use overall as fallback
                            if ($allNull && $studentInstance->jce->overall) {
                                if (in_array($studentInstance->jce->overall, ['A', 'B', 'C'])) {
                                    $jceCredits = 6; // Merit baseline
                                }
                            }
                        }

                        $classStats[$className]['jceSatCount']++;
                        if ($jceCredits >= 6) {
                            $classStats[$className]['jceNo6c']++;
                        }
                    }
                }

                // Build class array sorted by name
                ksort($classStats);
                $classesArray = [];
                $houseTotal = [
                    'size' => 0, 'noSat' => 0, 'no6c' => 0,
                    'jceNo6c' => 0, 'jceSatCount' => 0,
                ];

                foreach ($classStats as $cs) {
                    $cs['pct'] = $cs['noSat'] > 0 ? round(($cs['no6c'] / $cs['noSat']) * 100, 2) : 0;
                    $cs['jcePct'] = $cs['noSat'] > 0 ? round(($cs['jceNo6c'] / $cs['noSat']) * 100, 2) : 0;
                    $cs['vaPct'] = round($cs['pct'] - $cs['jcePct'], 2);
                    $classesArray[] = $cs;

                    $houseTotal['size'] += $cs['size'];
                    $houseTotal['noSat'] += $cs['noSat'];
                    $houseTotal['no6c'] += $cs['no6c'];
                    $houseTotal['jceNo6c'] += $cs['jceNo6c'];
                    $houseTotal['jceSatCount'] += $cs['jceSatCount'];
                }

                $houseTotal['pct'] = $houseTotal['noSat'] > 0
                    ? round(($houseTotal['no6c'] / $houseTotal['noSat']) * 100, 2) : 0;
                $houseTotal['jcePct'] = $houseTotal['noSat'] > 0
                    ? round(($houseTotal['jceNo6c'] / $houseTotal['noSat']) * 100, 2) : 0;
                $houseTotal['vaPct'] = round($houseTotal['pct'] - $houseTotal['jcePct'], 2);

                $housesData[$houseName][$tpIndex] = [
                    'classes' => $classesArray,
                    'total' => $houseTotal,
                ];

                // Accumulate grand total
                $grandTotalData['size'] += $houseTotal['size'];
                $grandTotalData['noSat'] += $houseTotal['noSat'];
                $grandTotalData['no6c'] += $houseTotal['no6c'];
                $grandTotalData['jceNo6c'] += $houseTotal['jceNo6c'];
                $grandTotalData['jceSatCount'] += $houseTotal['jceSatCount'];
            }

            $grandTotalData['pct'] = $grandTotalData['noSat'] > 0
                ? round(($grandTotalData['no6c'] / $grandTotalData['noSat']) * 100, 2) : 0;
            $grandTotalData['jcePct'] = $grandTotalData['noSat'] > 0
                ? round(($grandTotalData['jceNo6c'] / $grandTotalData['noSat']) * 100, 2) : 0;
            $grandTotalData['vaPct'] = round($grandTotalData['pct'] - $grandTotalData['jcePct'], 2);

            $grandTotal[$tpIndex] = $grandTotalData;
        }

        $gradeName = $gradeD->name;

        if ($request->query('export') === 'excel') {
            return Excel::download(
                new \App\Exports\House6CTrackingExport([
                    'testPeriods' => $testPeriods,
                    'housesData' => $housesData,
                    'grandTotal' => $grandTotal,
                    'gradeName' => $gradeName,
                    'startYear' => $startYear,
                    'schoolName' => $school_setup->school_name ?? 'School',
                ]),
                'house_6c_tracking_' . $gradeName . '.xlsx'
            );
        }

        return view('assessment.senior.house-6c-tracking', [
            'testPeriods' => $testPeriods,
            'housesData' => $housesData,
            'grandTotal' => $grandTotal,
            'gradeName' => $gradeName,
            'startYear' => $startYear,
            'school_data' => $school_setup,
        ]);
    }
}
