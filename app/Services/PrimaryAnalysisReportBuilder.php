<?php

namespace App\Services;

use App\Helpers\TermHelper;
use App\Models\GradeSubject;
use App\Models\Klass;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PrimaryAnalysisReportBuilder
{
    public function buildClassPerformance(int $classId, int $termId, string $type, int $sequenceId): array
    {
        $selectedTerm = $this->resolveTerm($termId);
        $klass = Klass::query()->with(['grade', 'teacher', 'term'])->findOrFail($classId);
        $klassSubjects = $this->klassSubjects($klass, $selectedTerm->id);
        $gradeSubjects = $klassSubjects->pluck('subject')->filter()->values();
        $students = $this->studentsForClass($klass, $selectedTerm->id);
        $testResults = $this->testResultsIndex($students->pluck('id'), $gradeSubjects->pluck('id'), $selectedTerm->id, $type, $sequenceId);
        $gradingMatrix = $this->gradingMatrix($selectedTerm);

        $gradeCounts = $this->emptyGradeCounts();
        $gradeCombinationsCounts = $this->emptyGradeCombinationCounts();
        $allStudentData = $this->studentAnalysisRows(
            $students,
            $gradeSubjects,
            $testResults,
            $gradingMatrix,
            $gradeCounts,
            $gradeCombinationsCounts
        );

        return array_merge($this->schoolContext(), [
            'allStudentData' => $allStudentData,
            'gradeCounts' => $gradeCounts,
            'gradeCombinationsCounts' => $gradeCombinationsCounts,
            'subjects' => $klassSubjects,
            'klass' => $klass,
            'currentTerm' => $selectedTerm,
            'selectedTermId' => $selectedTerm->id,
            'type' => $type,
            'sequenceId' => $sequenceId,
        ]);
    }

    public function buildGradePerformance(int $gradeId, int $termId, string $type, int $sequenceId): array
    {
        $selectedTerm = $this->resolveTerm($termId);
        $gradeSubjects = $this->gradeSubjects($gradeId, $selectedTerm->id);
        $students = $this->studentsForGrade($gradeId, $selectedTerm->id);
        $testResults = $this->testResultsIndex($students->pluck('id'), $gradeSubjects->pluck('id'), $selectedTerm->id, $type, $sequenceId);
        $gradingMatrix = $this->gradingMatrix($selectedTerm);

        $gradeCounts = $this->emptyGradeCounts();
        $gradeCombinationsCounts = $this->emptyGradeCombinationCounts();
        $allStudentData = $this->studentAnalysisRows(
            $students,
            $gradeSubjects,
            $testResults,
            $gradingMatrix,
            $gradeCounts,
            $gradeCombinationsCounts
        );

        return array_merge($this->schoolContext(), [
            'allStudentData' => $allStudentData,
            'subjects' => $gradeSubjects->map(fn (GradeSubject $gradeSubject) => $gradeSubject->subject->name ?? '')->all(),
            'gradeCounts' => $gradeCounts,
            'gradeCombinationsCounts' => $gradeCombinationsCounts,
            'currentTerm' => $selectedTerm,
            'selectedTermId' => $selectedTerm->id,
            'type' => $type,
            'sequenceId' => $sequenceId,
        ]);
    }

    public function buildOverallGradeDistribution(int $gradeId, int $termId, string $type, int $sequenceId): array
    {
        $selectedTerm = $this->resolveTerm($termId);
        $gradeSubjects = $this->gradeSubjects($gradeId, $selectedTerm->id);
        $students = $this->studentsForGrade($gradeId, $selectedTerm->id);
        $testResults = $this->testResultsIndex($students->pluck('id'), $gradeSubjects->pluck('id'), $selectedTerm->id, $type, $sequenceId);

        $gradeDistributions = $this->emptyOverallGradeDistributions();

        foreach ($students as $student) {
            $genderKey = $this->distributionGenderKey($student->gender);
            $studentResults = $testResults->get($student->id, collect());

            foreach ($gradeSubjects as $gradeSubject) {
                $result = $studentResults->get($gradeSubject->id);
                $grade = $result?->grade;

                if ($grade && isset($gradeDistributions[$grade][$genderKey])) {
                    $gradeDistributions[$grade][$genderKey]++;
                    $gradeDistributions['total'][$genderKey]++;
                }
            }
        }

        $gradeDistributions['AB']['M'] = $gradeDistributions['A']['M'] + $gradeDistributions['B']['M'];
        $gradeDistributions['AB']['F'] = $gradeDistributions['A']['F'] + $gradeDistributions['B']['F'];
        $gradeDistributions['ABC']['M'] = $gradeDistributions['AB']['M'] + $gradeDistributions['C']['M'];
        $gradeDistributions['ABC']['F'] = $gradeDistributions['AB']['F'] + $gradeDistributions['C']['F'];
        $gradeDistributions['DE']['M'] = $gradeDistributions['D']['M'] + $gradeDistributions['E']['M'];
        $gradeDistributions['DE']['F'] = $gradeDistributions['D']['F'] + $gradeDistributions['E']['F'];

        foreach (['A', 'B', 'C', 'D', 'E', 'AB', 'ABC', 'DE'] as $grade) {
            $gradeDistributions[$grade . '%'] = [
                'M' => $gradeDistributions['total']['M'] > 0 ? round(($gradeDistributions[$grade]['M'] / $gradeDistributions['total']['M']) * 100, 2) : 0,
                'F' => $gradeDistributions['total']['F'] > 0 ? round(($gradeDistributions[$grade]['F'] / $gradeDistributions['total']['F']) * 100, 2) : 0,
            ];
        }

        return array_merge($this->schoolContext(), [
            'gradeDistributions' => $gradeDistributions,
            'currentTerm' => $selectedTerm,
            'selectedTermId' => $selectedTerm->id,
            'type' => $type,
            'sequenceId' => $sequenceId,
        ]);
    }

    public function buildSubjectGradePerformance(int $gradeId, int $termId, string $type, int $sequenceId): array
    {
        $selectedTerm = $this->resolveTerm($termId);
        $gradeSubjects = $this->gradeSubjects($gradeId, $selectedTerm->id);
        $students = $this->studentsForGrade($gradeId, $selectedTerm->id);
        $testResults = $this->testResultsIndex($students->pluck('id'), $gradeSubjects->pluck('id'), $selectedTerm->id, $type, $sequenceId);

        $subjectPerformance = [];
        foreach ($gradeSubjects as $gradeSubject) {
            $subjectPerformance[$gradeSubject->subject->name] = [
                'A' => ['M' => 0, 'F' => 0],
                'B' => ['M' => 0, 'F' => 0],
                'C' => ['M' => 0, 'F' => 0],
                'D' => ['M' => 0, 'F' => 0],
                'E' => ['M' => 0, 'F' => 0],
                'total' => ['M' => 0, 'F' => 0],
            ];
        }

        foreach ($students as $student) {
            $genderKey = $this->distributionGenderKey($student->gender);
            $studentResults = $testResults->get($student->id, collect());

            foreach ($gradeSubjects as $gradeSubject) {
                $subjectName = $gradeSubject->subject->name;
                $grade = $studentResults->get($gradeSubject->id)?->grade;

                if ($grade && isset($subjectPerformance[$subjectName][$grade][$genderKey])) {
                    $subjectPerformance[$subjectName][$grade][$genderKey]++;
                    $subjectPerformance[$subjectName]['total'][$genderKey]++;
                }
            }
        }

        foreach ($subjectPerformance as &$counts) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $grade) {
                $counts[$grade . '%'] = [
                    'M' => $counts['total']['M'] > 0 ? round(($counts[$grade]['M'] / $counts['total']['M']) * 100, 2) : 0,
                    'F' => $counts['total']['F'] > 0 ? round(($counts[$grade]['F'] / $counts['total']['F']) * 100, 2) : 0,
                ];
            }

            $counts['AB'] = ['M' => $counts['A']['M'] + $counts['B']['M'], 'F' => $counts['A']['F'] + $counts['B']['F']];
            $counts['ABC'] = ['M' => $counts['AB']['M'] + $counts['C']['M'], 'F' => $counts['AB']['F'] + $counts['C']['F']];
            $counts['DE'] = ['M' => $counts['D']['M'] + $counts['E']['M'], 'F' => $counts['D']['F'] + $counts['E']['F']];
            $counts['AB%'] = [
                'M' => $counts['total']['M'] > 0 ? round(($counts['AB']['M'] / $counts['total']['M']) * 100, 2) : 0,
                'F' => $counts['total']['F'] > 0 ? round(($counts['AB']['F'] / $counts['total']['F']) * 100, 2) : 0,
            ];
            $counts['ABC%'] = [
                'M' => $counts['total']['M'] > 0 ? round(($counts['ABC']['M'] / $counts['total']['M']) * 100, 2) : 0,
                'F' => $counts['total']['F'] > 0 ? round(($counts['ABC']['F'] / $counts['total']['F']) * 100, 2) : 0,
            ];
            $counts['DE%'] = [
                'M' => $counts['total']['M'] > 0 ? round(($counts['DE']['M'] / $counts['total']['M']) * 100, 2) : 0,
                'F' => $counts['total']['F'] > 0 ? round(($counts['DE']['F'] / $counts['total']['F']) * 100, 2) : 0,
            ];
        }
        unset($counts);

        return array_merge($this->schoolContext(), [
            'subjectPerformance' => $subjectPerformance,
            'currentTerm' => $selectedTerm,
            'selectedTermId' => $selectedTerm->id,
            'type' => $type,
            'sequenceId' => $sequenceId,
        ]);
    }

    public function buildRegionalExamPerformance(int $gradeId, int $termId): array
    {
        $selectedTerm = $this->resolveTerm($termId);
        $gradeSubjects = $this->gradeSubjects($gradeId, $selectedTerm->id);
        $students = $this->studentsForGrade($gradeId, $selectedTerm->id);
        $testResults = $this->testResultsIndex($students->pluck('id'), $gradeSubjects->pluck('id'), $selectedTerm->id, 'Exam', 1);

        $subjectPerformance = [];
        foreach ($gradeSubjects as $gradeSubject) {
            $subjectPerformance[$gradeSubject->subject->name] = [
                'Candidates' => ['M' => 0, 'F' => 0, 'T' => 0],
                'A' => ['M' => 0, 'F' => 0, 'T' => 0],
                'B' => ['M' => 0, 'F' => 0, 'T' => 0],
                'C' => ['M' => 0, 'F' => 0, 'T' => 0],
                'D' => ['M' => 0, 'F' => 0, 'T' => 0],
                'E' => ['M' => 0, 'F' => 0, 'T' => 0],
                'U' => ['M' => 0, 'F' => 0, 'T' => 0],
                'AB%' => ['M' => 0, 'F' => 0, 'T' => 0],
                'ABC%' => ['M' => 0, 'F' => 0, 'T' => 0],
                'DEU%' => ['M' => 0, 'F' => 0, 'T' => 0],
            ];
        }

        foreach ($students as $student) {
            $genderKey = $this->distributionGenderKey($student->gender);
            $studentResults = $testResults->get($student->id, collect());

            foreach ($gradeSubjects as $gradeSubject) {
                $subjectName = $gradeSubject->subject->name;
                $result = $studentResults->get($gradeSubject->id);

                if ($result) {
                    $subjectPerformance[$subjectName]['Candidates'][$genderKey]++;

                    if ($result->grade && isset($subjectPerformance[$subjectName][$result->grade][$genderKey])) {
                        $subjectPerformance[$subjectName][$result->grade][$genderKey]++;
                    }
                }
            }
        }

        foreach ($subjectPerformance as &$data) {
            foreach (['A', 'B', 'C', 'D', 'E', 'U'] as $grade) {
                $data[$grade]['T'] = $data[$grade]['M'] + $data[$grade]['F'];
            }

            $data['Candidates']['T'] = $data['Candidates']['M'] + $data['Candidates']['F'];
            $data['AB%'] = $this->regionalPercentage($data, ['A', 'B']);
            $data['ABC%'] = $this->regionalPercentage($data, ['A', 'B', 'C']);
            $data['DEU%'] = $this->regionalPercentage($data, ['D', 'E', 'U']);
        }
        unset($data);

        return array_merge($this->schoolContext(), [
            'subjectPerformance' => $subjectPerformance,
            'currentTerm' => $selectedTerm,
            'selectedTermId' => $selectedTerm->id,
        ]);
    }

    private function resolveTerm(int $termId): Term
    {
        return Term::find($termId) ?? TermHelper::getCurrentTerm()
            ?? throw new \RuntimeException('No active term found for primary analysis generation.');
    }

    private function schoolContext(): array
    {
        return [
            'school_data' => SchoolSetup::first(),
            'school_head' => User::query()->where('position', 'School Head')->first(),
        ];
    }

    private function klassSubjects(Klass $klass, int $termId): Collection
    {
        return $klass->subjectClasses()
            ->where('term_id', $termId)
            ->with(['subject.subject'])
            ->get()
            ->sortBy(function ($klassSubject) {
                return [
                    $klassSubject->subject->sequence ?? PHP_INT_MAX,
                    $klassSubject->subject->subject->name ?? '',
                ];
            })
            ->values();
    }

    private function gradeSubjects(int $gradeId, int $termId): Collection
    {
        return GradeSubject::query()
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->with('subject')
            ->get()
            ->sortBy(function (GradeSubject $gradeSubject) {
                return [
                    $gradeSubject->sequence ?? PHP_INT_MAX,
                    $gradeSubject->subject->name ?? '',
                ];
            })
            ->values();
    }

    private function studentsForClass(Klass $klass, int $termId): Collection
    {
        return $klass->students()
            ->select('students.*')
            ->wherePivot('term_id', $termId)
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->get();
    }

    private function studentsForGrade(int $gradeId, int $termId): Collection
    {
        return Student::query()
            ->whereHas('classes', function ($query) use ($gradeId, $termId) {
                $query->where('klasses.term_id', $termId)
                    ->where('klasses.grade_id', $gradeId);
            })
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->get();
    }

    private function testResultsIndex(
        Collection $studentIds,
        Collection $gradeSubjectIds,
        int $termId,
        string $type,
        int $sequenceId
    ): Collection {
        if ($studentIds->isEmpty() || $gradeSubjectIds->isEmpty()) {
            return collect();
        }

        return DB::table('student_tests')
            ->join('tests', 'tests.id', '=', 'student_tests.test_id')
            ->whereIn('student_tests.student_id', $studentIds)
            ->whereIn('tests.grade_subject_id', $gradeSubjectIds)
            ->where('tests.term_id', $termId)
            ->where('tests.type', $type)
            ->where('tests.sequence', $sequenceId)
            ->select([
                'student_tests.student_id',
                'tests.grade_subject_id',
                'student_tests.score',
                'student_tests.grade',
            ])
            ->get()
            ->groupBy('student_id')
            ->map(fn (Collection $rows) => $rows->keyBy('grade_subject_id'));
    }

    private function gradingMatrix(Term $term): Collection
    {
        return DB::table('overall_grading_matrices')
            ->where('term_id', $term->id)
            ->where('year', $term->year)
            ->get();
    }

    private function studentAnalysisRows(
        Collection $students,
        Collection $gradeSubjects,
        Collection $testResults,
        Collection $gradingMatrix,
        array &$gradeCounts,
        array &$gradeCombinationsCounts
    ): array {
        $allStudentData = [];

        foreach ($students as $student) {
            $studentResults = $testResults->get($student->id, collect());
            $scores = [];
            $totalScore = 0;
            $totalSubjectsCounted = 0;

            foreach ($gradeSubjects as $gradeSubject) {
                $result = $studentResults->get($gradeSubject->id);
                $score = $result?->score;
                $grade = $result?->grade;

                if ($result) {
                    $totalScore += (float) ($score ?? 0);
                    $totalSubjectsCounted++;
                }

                if ($grade) {
                    $genderKey = $this->strictGenderKey($student->gender);
                    if ($genderKey && isset($gradeCounts[$grade][$genderKey])) {
                        $gradeCounts[$grade][$genderKey]++;
                    }
                }

                $scores[$gradeSubject->id] = [
                    'score' => $score,
                    'grade' => $grade,
                ];
            }

            $averageScore = $totalSubjectsCounted > 0 ? $totalScore / $totalSubjectsCounted : 0;
            $this->updateGradeCombinationCounts(array_column($scores, 'grade'), $gradeCombinationsCounts, $student->gender);

            $allStudentData[] = [
                'studentName' => $student->fullName ?? '',
                'gender' => $student->gender,
                'scores' => $scores,
                'totalScore' => $totalScore,
                'averageScore' => $averageScore,
                'overallGrade' => $this->deriveGrade(round($averageScore, 0), $gradingMatrix),
            ];
        }

        usort($allStudentData, fn (array $a, array $b) => $b['averageScore'] <=> $a['averageScore']);

        foreach ($allStudentData as $index => &$studentData) {
            $studentData['position'] = $index + 1;
        }
        unset($studentData);

        return $allStudentData;
    }

    private function regionalPercentage(array $data, array $grades): array
    {
        $totals = ['M' => 0, 'F' => 0, 'T' => 0];

        foreach ($grades as $grade) {
            $totals['M'] += $data[$grade]['M'];
            $totals['F'] += $data[$grade]['F'];
            $totals['T'] += $data[$grade]['T'];
        }

        return [
            'M' => $data['Candidates']['M'] > 0 ? round(($totals['M'] / $data['Candidates']['M']) * 100, 2) : 0,
            'F' => $data['Candidates']['F'] > 0 ? round(($totals['F'] / $data['Candidates']['F']) * 100, 2) : 0,
            'T' => $data['Candidates']['T'] > 0 ? round(($totals['T'] / $data['Candidates']['T']) * 100, 2) : 0,
        ];
    }

    private function updateGradeCombinationCounts(array $grades, array &$gradeCombinationsCounts, ?string $gender): void
    {
        $genderKey = $this->strictGenderKey($gender);
        if (!$genderKey) {
            return;
        }

        $grades = array_filter($grades);
        $hasABC = count(array_intersect(['A', 'B', 'C'], $grades)) > 0;
        $hasABCD = count(array_intersect(['A', 'B', 'C', 'D'], $grades)) > 0;

        if ($hasABC) {
            $gradeCombinationsCounts['ABC'][$genderKey]++;
        }

        if ($hasABCD) {
            $gradeCombinationsCounts['ABCD'][$genderKey]++;
        }
    }

    private function deriveGrade(float|int|null $score, Collection $gradingMatrix): ?string
    {
        if ($score === null) {
            return null;
        }

        foreach ($gradingMatrix as $grade) {
            if ($score >= $grade->min_score && $score <= $grade->max_score) {
                return $grade->grade;
            }
        }

        return 'N/A';
    }

    private function strictGenderKey(?string $gender): ?string
    {
        return match (strtolower((string) $gender)) {
            'm' => 'M',
            'f' => 'F',
            default => null,
        };
    }

    private function distributionGenderKey(?string $gender): string
    {
        return strtoupper((string) $gender) === 'M' ? 'M' : 'F';
    }

    private function emptyGradeCounts(): array
    {
        return [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'U' => ['M' => 0, 'F' => 0],
        ];
    }

    private function emptyGradeCombinationCounts(): array
    {
        return [
            'ABC' => ['M' => 0, 'F' => 0],
            'ABCD' => ['M' => 0, 'F' => 0],
        ];
    }

    private function emptyOverallGradeDistributions(): array
    {
        return [
            'A' => ['M' => 0, 'F' => 0],
            'B' => ['M' => 0, 'F' => 0],
            'C' => ['M' => 0, 'F' => 0],
            'D' => ['M' => 0, 'F' => 0],
            'E' => ['M' => 0, 'F' => 0],
            'total' => ['M' => 0, 'F' => 0],
            'AB' => ['M' => 0, 'F' => 0],
            'ABC' => ['M' => 0, 'F' => 0],
            'DE' => ['M' => 0, 'F' => 0],
        ];
    }
}
