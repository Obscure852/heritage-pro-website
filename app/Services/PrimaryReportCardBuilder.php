<?php

namespace App\Services;

use App\Helpers\TermHelper;
use App\Models\Attendance;
use App\Models\Klass;
use App\Models\OverallGradingMatrix;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\SubjectComment;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PrimaryReportCardBuilder
{
    public function buildStudentReport(int $studentId, int $termId, int $overallGradePrecision = 0): array
    {
        $classId = DB::table('klass_student')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->value('klass_id');

        if (!$classId) {
            throw (new ModelNotFoundException())->setModel(Student::class, [$studentId]);
        }

        $classReport = $this->buildClassReport($classId, $termId, $overallGradePrecision);

        $studentReport = collect($classReport['allStudentData'])
            ->first(fn (array $report) => (int) $report['student']->id === $studentId);

        if (!$studentReport) {
            throw (new ModelNotFoundException())->setModel(Student::class, [$studentId]);
        }

        return $studentReport;
    }

    public function buildClassReport(int $classId, int $termId, int $overallGradePrecision = 1): array
    {
        $selectedTerm = Term::find($termId) ?? TermHelper::getCurrentTerm();

        if (!$selectedTerm) {
            throw new \RuntimeException('No active term found for primary report card generation.');
        }

        $klass = Klass::query()
            ->with(['teacher', 'term'])
            ->findOrFail($classId);

        $schoolSetup = SchoolSetup::first();
        $schoolHead = User::query()->where('position', 'School Head')->first();
        $nextTermStartDate = $this->getNextTermStartDate($selectedTerm);

        $klassSubjects = $klass->subjectClasses()
            ->where('term_id', $selectedTerm->id)
            ->with(['subject.subject'])
            ->get()
            ->sortBy(function ($klassSubject) {
                return [
                    $klassSubject->subject->sequence ?? PHP_INT_MAX,
                    $klassSubject->subject->subject->name ?? '',
                ];
            })
            ->values();

        $gradeSubjectIds = $klassSubjects->pluck('grade_subject_id')->filter()->values();

        $students = $klass->students()
            ->select('students.*')
            ->with([
                'overallComments' => fn ($query) => $query->where('term_id', $selectedTerm->id),
                'manualAttendanceEntries' => fn ($query) => $query->where('term_id', $selectedTerm->id),
                'tests' => function ($query) use ($selectedTerm, $gradeSubjectIds) {
                    $query->where('tests.term_id', $selectedTerm->id)
                        ->where('tests.type', 'Exam');

                    if ($gradeSubjectIds->isNotEmpty()) {
                        $query->whereIn('tests.grade_subject_id', $gradeSubjectIds);
                    }
                },
            ])
            ->get();

        $studentIds = $students->pluck('id')->values();

        $subjectCommentsByStudent = SubjectComment::query()
            ->where('term_id', $selectedTerm->id)
            ->when($studentIds->isNotEmpty(), fn ($query) => $query->whereIn('student_id', $studentIds))
            ->when($gradeSubjectIds->isNotEmpty(), fn ($query) => $query->whereIn('grade_subject_id', $gradeSubjectIds))
            ->get()
            ->groupBy('student_id')
            ->map(fn (Collection $comments) => $comments->keyBy('grade_subject_id'));

        $absentDaysByStudent = $this->absentDaysByStudent($studentIds, $selectedTerm->id);
        $overallGrades = OverallGradingMatrix::query()
            ->where('grade_id', $klass->grade_id)
            ->orderBy('min_score')
            ->get();

        $studentStats = [];
        $studentPercentages = [];

        foreach ($students as $student) {
            $testsByGradeSubject = $this->indexTestsByGradeSubject($student->tests);
            $totalScore = 0;
            $totalOutOf = 0;

            foreach ($klassSubjects as $klassSubject) {
                $gradeSubject = $klassSubject->subject;
                $examTest = $testsByGradeSubject->get($gradeSubject->id);

                if ($examTest) {
                    $totalScore += (float) ($examTest->pivot->score ?? 0);
                    $totalOutOf += (float) ($examTest->out_of ?? 0);
                }
            }

            $averagePercentage = $totalOutOf > 0 ? ($totalScore / $totalOutOf) * 100 : 0;

            $studentStats[$student->id] = [
                'tests' => $testsByGradeSubject,
                'totalScore' => $totalScore,
                'totalOutOf' => $totalOutOf,
                'averagePercentage' => $averagePercentage,
            ];

            $studentPercentages[$student->id] = $averagePercentage;
        }

        $classAverage = count($studentPercentages) > 0
            ? array_sum($studentPercentages) / count($studentPercentages)
            : 0;

        arsort($studentPercentages);
        $ranks = array_flip(array_keys($studentPercentages));

        $classSize = $students->count();
        $teacherName = $klass->teacher?->fullName ?? 'N/A';
        $teacherSignaturePath = $this->resolvePublicPath($klass->teacher?->signature_path);
        $schoolHeadName = $schoolHead?->fullName ?? 'N/A';
        $schoolHeadSignaturePath = $this->resolvePublicPath($schoolHead?->signature_path);
        $schoolLogoPath = $this->resolvePublicPath($schoolSetup?->logo_path);

        $allStudentData = $students->map(function (Student $student) use (
            $klass,
            $klassSubjects,
            $selectedTerm,
            $schoolSetup,
            $schoolHead,
            $nextTermStartDate,
            $subjectCommentsByStudent,
            $absentDaysByStudent,
            $overallGrades,
            $studentStats,
            $ranks,
            $classAverage,
            $classSize,
            $teacherName,
            $teacherSignaturePath,
            $schoolHeadName,
            $schoolHeadSignaturePath,
            $schoolLogoPath,
            $overallGradePrecision
        ) {
            $testsByGradeSubject = $studentStats[$student->id]['tests'];
            $totalScore = $studentStats[$student->id]['totalScore'];
            $totalOutOf = $studentStats[$student->id]['totalOutOf'];
            $averagePercentage = $studentStats[$student->id]['averagePercentage'];
            $overallComment = $student->overallComments->first();
            $manualEntry = $student->manualAttendanceEntries->first();
            $commentsBySubject = $subjectCommentsByStudent->get($student->id, collect());

            $scores = $klassSubjects->map(function ($klassSubject) use ($testsByGradeSubject, $commentsBySubject) {
                $gradeSubject = $klassSubject->subject;
                $subject = $gradeSubject->subject;
                $examTest = $testsByGradeSubject->get($gradeSubject->id);
                $subjectComment = $commentsBySubject->get($gradeSubject->id);

                $score = $examTest ? $examTest->pivot->score : null;
                $outOf = $examTest ? $examTest->out_of : null;
                $percentage = $examTest
                    ? ($examTest->pivot->percentage ?? ($outOf ? (($score / $outOf) * 100) : null))
                    : null;

                return [
                    'subject' => $subject->name ?? 'N/A',
                    'out_of' => $outOf,
                    'score' => $score,
                    'percentage' => $percentage,
                    'grade' => $examTest ? $examTest->pivot->grade : null,
                    'comments' => $subjectComment?->remarks ?? 'N/A',
                ];
            })->all();

            $absentDays = $manualEntry && $manualEntry->days_absent !== null
                ? $manualEntry->days_absent
                : (int) ($absentDaysByStudent->get($student->id, 0));

            $rank = isset($ranks[$student->id]) ? $ranks[$student->id] + 1 : 'N/A';

            return [
                'student' => $student,
                'currentClass' => $klass,
                'school_setup' => $schoolSetup,
                'school_head' => $schoolHead,
                'schoolLogoPath' => $schoolLogoPath,
                'classTeacherRemarks' => $overallComment?->class_teacher_remarks ?? 'No remarks provided.',
                'headTeachersRemarks' => $overallComment?->school_head_remarks ?? 'No remarks provided.',
                'scores' => $scores,
                'totalScore' => $totalScore,
                'totalOutOf' => $totalOutOf,
                'averagePercentage' => $averagePercentage,
                'overallGrade' => $this->resolveOverallGrade($overallGrades, round($averagePercentage, $overallGradePrecision)),
                'nextTermStartDate' => $nextTermStartDate,
                'studentPosition' => $rank,
                'rank' => $rank,
                'classAverage' => $classAverage,
                'absentDays' => $absentDays,
                'schoolFees' => $manualEntry?->school_fees_owing,
                'otherInfo' => $manualEntry?->other_info,
                'classSize' => $classSize,
                'termStart' => $klass->term?->start_date?->toDateString(),
                'termEnd' => $klass->term?->end_date?->toDateString(),
                'teacherName' => $teacherName,
                'teacherSignaturePath' => $teacherSignaturePath,
                'schoolHeadName' => $schoolHeadName,
                'schoolHeadSignaturePath' => $schoolHeadSignaturePath,
            ];
        })->all();

        return [
            'allStudentData' => $allStudentData,
            'school_setup' => $schoolSetup,
            'school_head' => $schoolHead,
            'klass' => $klass,
            'nextTermStartDate' => $nextTermStartDate,
            'schoolLogoPath' => $schoolLogoPath,
            'classSize' => $classSize,
            'teacherName' => $teacherName,
            'teacherSignaturePath' => $teacherSignaturePath,
            'schoolHeadName' => $schoolHeadName,
            'schoolHeadSignaturePath' => $schoolHeadSignaturePath,
        ];
    }

    private function indexTestsByGradeSubject(Collection $tests): Collection
    {
        return $tests
            ->groupBy('grade_subject_id')
            ->map(fn (Collection $gradeSubjectTests) => $gradeSubjectTests->first());
    }

    private function absentDaysByStudent(Collection $studentIds, int $termId): Collection
    {
        if ($studentIds->isEmpty()) {
            return collect();
        }

        $absentCodes = Attendance::getAbsentCodes();

        if (empty($absentCodes)) {
            return collect();
        }

        return Attendance::query()
            ->where('term_id', $termId)
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', $absentCodes)
            ->selectRaw('student_id, COUNT(*) as absent_days')
            ->groupBy('student_id')
            ->pluck('absent_days', 'student_id');
    }

    private function resolveOverallGrade(Collection $overallGrades, float $percentage): ?OverallGradingMatrix
    {
        return $overallGrades->first(function (OverallGradingMatrix $grade) use ($percentage) {
            return $grade->min_score <= $percentage && $grade->max_score >= $percentage;
        });
    }

    private function getNextTermStartDate(Term $currentTerm): ?string
    {
        return Term::query()
            ->where('start_date', '>', $currentTerm->end_date)
            ->orderBy('start_date')
            ->value('start_date');
    }

    private function resolvePublicPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, public_path())) {
            return $path;
        }

        return public_path(ltrim($path, '/'));
    }
}
