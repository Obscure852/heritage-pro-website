<?php

namespace App\Services;

use App\Helpers\TermHelper;
use App\Models\GradeSubject;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\SchoolSetup;
use App\Models\Student;
use App\Models\StudentTest;
use App\Models\Test;
use App\Models\Term;
use App\Models\ValueAdditionSubjectMapping;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ValueAdditionService {
    private array $seniorGrades = ['A*', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'U'];
    private array $jceGrades = ['A', 'B', 'C', 'D', 'E', 'U'];

    /**
     * Generate the full value addition report data for a grade.
     */
    public function generateReport(int $classId): array {
        $currentTerm = TermHelper::getCurrentTerm();
        $selectedTermId = session('selected_term_id', $currentTerm->id);
        $schoolSetup = SchoolSetup::first();

        $klass = Klass::findOrFail($classId);
        $grade = Grade::findOrFail($klass->grade_id);

        // Get all terms that have GradeSubjects for this grade, ordered chronologically
        $terms = Term::whereIn('id', function ($query) use ($grade) {
            $query->select('term_id')
                ->from('grade_subject')
                ->where('grade_id', $grade->id)
                ->distinct();
        })->orderBy('year', 'asc')->orderBy('term', 'asc')->get();

        // Get all current students across all classes in this grade
        $students = Student::whereHas('classes', function ($q) use ($grade, $selectedTermId) {
            $q->where('klasses.grade_id', $grade->id)
                ->where('klass_student.term_id', $selectedTermId);
        })->whereHas('studentTerms', function ($q) use ($selectedTermId) {
            $q->where('term_id', $selectedTermId)->where('status', 'Current');
        })->with('jce')->get();

        // Get all GradeSubjects for the current term
        $gradeSubjects = GradeSubject::where('grade_id', $grade->id)
            ->where('term_id', $selectedTermId)
            ->where('active', 1)
            ->with('subject')
            ->orderBy('sequence', 'asc')
            ->get();

        // Load value addition mappings: subject_id => source_key
        $mappings = ValueAdditionSubjectMapping::where('school_type', 'Senior')
            ->where('exam_type', 'JCE')
            ->where('is_active', true)
            ->get()
            ->keyBy('subject_id')
            ->map(fn($m) => $m->source_key)
            ->toArray();

        // Build per-subject data
        $subjects = [];
        foreach ($gradeSubjects as $gs) {
            if (!$gs->subject) {
                continue;
            }

            $subjectData = $this->buildSubjectData($gs, $students, $terms, $mappings, $grade->id);
            if ($subjectData) {
                $subjects[] = $subjectData;
            }
        }

        $term = Term::find($selectedTermId);
        $year = $term ? $term->year : date('Y');

        return [
            'subjects' => $subjects,
            'gradeName' => $grade->name,
            'year' => $year,
            'school_data' => $schoolSetup,
            'classId' => $classId,
        ];
    }

    /**
     * Build data for a single subject.
     * Tests are grouped by term, each group showing "Term X, YYYY" as a header.
     * Within each term, individual tests are listed (e.g. "Jan", "Feb").
     */
    private function buildSubjectData(GradeSubject $gradeSubject, Collection $students, Collection $terms, array $mappings, int $gradeId): ?array {
        $subjectId = $gradeSubject->subject_id;
        $subjectName = $gradeSubject->subject->name;
        $sourceKey = $mappings[$subjectId] ?? null;

        // Get JCE baseline
        $jceBaseline = $this->getJceBaseline($students, $sourceKey);

        // Index terms by id for quick lookup
        $termsById = $terms->keyBy('id');

        // Get all tests for this subject across all terms, ordered chronologically
        $tests = Test::whereIn('grade_subject_id', function ($q) use ($gradeId, $subjectId) {
            $q->select('id')
                ->from('grade_subject')
                ->where('grade_id', $gradeId)
                ->where('subject_id', $subjectId);
        })
            ->whereIn('term_id', $terms->pluck('id'))
            ->whereNull('deleted_at')
            ->orderBy('year', 'asc')
            ->orderBy('end_date', 'asc')
            ->orderBy('sequence', 'asc')
            ->get();

        // Group tests by term_id, preserving chronological order
        $termGroups = [];
        foreach ($tests as $test) {
            $termId = $test->term_id;

            if (!isset($termGroups[$termId])) {
                $term = $termsById->get($termId);
                $termGroups[$termId] = [
                    'termLabel' => 'Term ' . $term->term . ', ' . $term->year,
                    'testRows' => [],
                ];
            }

            $isDouble = (bool) ($gradeSubject->subject->is_double ?? false);
            $distribution = $this->calculateTestGradeDistribution($students, $test, $isDouble);

            if ($distribution['total'] === 0) {
                continue;
            }

            $percentAC = $this->calculatePercentAC($distribution['grades'], $distribution['total']);
            $percentAE = $this->calculatePercentAE($distribution['grades'], $distribution['total']);
            $va = round($percentAC - $jceBaseline['percentAC'], 1);

            // Label: month abbreviation from end_date (e.g. "Jan", "Feb", "Mar")
            $testLabel = $test->end_date
                ? Carbon::parse($test->end_date)->format('M')
                : ($test->name ?? 'Test');

            $termGroups[$termId]['testRows'][] = [
                'testName' => $testLabel,
                'testId' => $test->id,
                'type' => $test->type,
                'grades' => $distribution['grades'],
                'total' => $distribution['total'],
                'percentAC' => $percentAC,
                'percentAE' => $percentAE,
                'jcePercentAC' => $jceBaseline['percentAC'],
                'va' => $va,
            ];
        }

        // Remove term groups that ended up with no test rows
        $termGroups = array_filter($termGroups, fn($g) => count($g['testRows']) > 0);

        return [
            'subjectName' => $subjectName,
            'subjectId' => $subjectId,
            'sourceKey' => $sourceKey,
            'jceInput' => $jceBaseline,
            'termGroups' => array_values($termGroups),
        ];
    }

    /**
     * Get JCE baseline for a subject.
     * If mapped → use specific JCE subject column
     * If NOT mapped → use student's overall JCE grade
     */
    private function getJceBaseline(Collection $students, ?string $sourceKey): array {
        $distribution = array_fill_keys($this->jceGrades, 0);
        $total = 0;

        foreach ($students as $student) {
            if (!$student->jce) {
                continue;
            }

            $column = $sourceKey ?? 'overall';
            $grade = $student->jce->{$column} ?? null;

            if (!$grade) {
                continue;
            }

            $grade = trim($grade);

            // Normalize: "Merit" → "A" for JCE context
            if ($grade === 'Merit') {
                $grade = 'A';
            }

            if (in_array($grade, $this->jceGrades)) {
                $distribution[$grade]++;
                $total++;
            }
        }

        $percentAC = $total > 0
            ? round(($distribution['A'] + $distribution['B'] + $distribution['C']) / $total * 100, 1)
            : 0;

        return [
            'grades' => $distribution,
            'total' => $total,
            'percentAC' => $percentAC,
            'label' => $sourceKey ? strtoupper($sourceKey) : 'OVERALL',
        ];
    }

    /**
     * Calculate grade distribution from StudentTest records for a single test.
     * For double subjects, each student contributes 2 grade entries (one per character).
     */
    private function calculateTestGradeDistribution(Collection $students, Test $test, bool $isDouble = false): array {
        $distribution = array_fill_keys($this->seniorGrades, 0);
        $distribution['X'] = 0; // no score / absent
        $total = 0;

        $studentIds = $students->pluck('id');

        // Get all student test records for this specific test
        $studentTests = StudentTest::where('test_id', $test->id)
            ->whereIn('student_id', $studentIds)
            ->whereNull('deleted_at')
            ->select('student_id', 'grade', 'score')
            ->get()
            ->keyBy('student_id');

        foreach ($students as $student) {
            $record = $studentTests->get($student->id);

            if (!$record || !$record->grade) {
                $slots = $isDouble ? 2 : 1;
                $distribution['X'] += $slots;
                $total += $slots;
                continue;
            }

            $grade = trim($record->grade);

            if ($isDouble && is_string($grade) && strlen($grade) === 2) {
                // Double subject: split into two individual grades
                foreach (str_split($grade) as $g) {
                    $total++;
                    if (array_key_exists($g, $distribution)) {
                        $distribution[$g]++;
                    } else {
                        $distribution['X']++;
                    }
                }
            } else {
                $total++;
                if (array_key_exists($grade, $distribution)) {
                    $distribution[$grade]++;
                } else {
                    $distribution['X']++;
                }
            }
        }

        return [
            'grades' => $distribution,
            'total' => $total,
        ];
    }

    /**
     * Calculate %A-C from a grade distribution array (senior grading).
     */
    private function calculatePercentAC(array $distribution, int $total): float {
        if ($total === 0) {
            return 0;
        }

        $acCount = ($distribution['A*'] ?? 0)
            + ($distribution['A'] ?? 0)
            + ($distribution['B'] ?? 0)
            + ($distribution['C'] ?? 0);

        return round($acCount / $total * 100, 1);
    }

    /**
     * Calculate %A-E from a grade distribution array (senior grading).
     */
    private function calculatePercentAE(array $distribution, int $total): float {
        if ($total === 0) {
            return 0;
        }

        $aeCount = ($distribution['A*'] ?? 0)
            + ($distribution['A'] ?? 0)
            + ($distribution['B'] ?? 0)
            + ($distribution['C'] ?? 0)
            + ($distribution['D'] ?? 0)
            + ($distribution['E'] ?? 0);

        return round($aeCount / $total * 100, 1);
    }
}
