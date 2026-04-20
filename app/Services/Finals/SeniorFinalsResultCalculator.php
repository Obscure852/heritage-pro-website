<?php

namespace App\Services\Finals;

use App\Models\ExternalExamResult;
use App\Models\FinalStudent;
use App\Models\SchoolSetup;
use Illuminate\Support\Collection;

/**
 * Aggregates senior (BGCSE) finals results for analysis screens.
 *
 * Reads pre-computed grade points from external_exam_subject_results
 * (the import already resolves them via per-subject grading scales),
 * so this service does not redefine BGCSE grading rules.
 */
class SeniorFinalsResultCalculator {
    public const EXAM_TYPE = 'BGCSE';

    /**
     * Senior FinalStudents in a graduation year, eager-loaded with their
     * latest BGCSE result, that result's subject results, and class/grade.
     */
    public function seniorStudentsForGraduationYear(int $graduationYear): Collection {
        return FinalStudent::query()
            ->where('graduation_year', $graduationYear)
            ->whereHas('finalKlasses.grade', function ($query) {
                $query->where('level', SchoolSetup::LEVEL_SENIOR);
            })
            ->with([
                'finalKlasses' => function ($query) use ($graduationYear) {
                    $query->where('graduation_year', $graduationYear)->with('grade');
                },
                'externalExamResults' => function ($query) {
                    $query->whereHas('externalExam', function ($q) {
                        $q->where('exam_type', self::EXAM_TYPE);
                    })
                    ->with(['externalExam', 'subjectResults' => function ($sq) {
                        $sq->orderBy('subject_name');
                    }])
                    ->orderByDesc('created_at')
                    ->orderByDesc('id');
                },
                'graduationGrade',
            ])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * Latest BGCSE result for a student (relationships must already be loaded
     * via seniorStudentsForGraduationYear or equivalent).
     */
    public function latestSeniorResult(FinalStudent $student): ?ExternalExamResult {
        return $student->externalExamResults
            ->first(fn ($result) => optional($result->externalExam)->exam_type === self::EXAM_TYPE);
    }

    /**
     * Build a flat summary row for a single senior student.
     * Returns fields suitable for table rendering and sorting by points.
     */
    public function summarizeStudent(FinalStudent $student): array {
        $result = $this->latestSeniorResult($student);
        $klass = $student->finalKlasses->first();

        $summary = [
            'student_id'      => $student->id,
            'full_name'       => $student->full_name,
            'first_name'      => $student->first_name,
            'last_name'       => $student->last_name,
            'gender'          => $student->gender,
            'gender_full'     => $student->gender === 'M' ? 'Male' : 'Female',
            'exam_number'     => $student->exam_number,
            'class_name'      => $klass ? $klass->name : 'No Class',
            'grade_name'      => $klass && $klass->grade ? $klass->grade->name : 'Unknown',
            'graduation_year' => (int) $student->graduation_year,
            'has_results'     => false,
            'overall_grade'   => null,
            'overall_points'  => null,
            'total_subjects'  => 0,
            'passes'          => 0,
            'pass_percentage' => 0,
            'subjects'        => [],
        ];

        if ($result === null) {
            return $summary;
        }

        $summary['has_results']     = true;
        $summary['overall_grade']   = $result->overall_grade ?: $result->calculated_overall_grade;
        $summary['overall_points']  = $result->overall_points !== null ? (float) $result->overall_points : null;
        $summary['total_subjects']  = (int) ($result->total_subjects ?? $result->subjectResults->count());
        $summary['passes']          = (int) ($result->passes ?? 0);
        $summary['pass_percentage'] = (float) ($result->pass_percentage ?? 0);

        $summary['subjects'] = $result->subjectResults
            ->map(function ($subjectResult) {
                return [
                    'subject_name' => $subjectResult->subject_name,
                    'subject_code' => $subjectResult->subject_code,
                    'grade'        => $subjectResult->grade,
                    'grade_points' => $subjectResult->grade_points !== null ? (float) $subjectResult->grade_points : null,
                    'is_pass'      => (bool) $subjectResult->is_pass,
                    'was_taken'    => (bool) $subjectResult->was_taken,
                ];
            })
            ->values()
            ->all();

        return $summary;
    }

    /**
     * Sort summaries by points descending, students without results last.
     *
     * @param  array<int, array>  $summaries
     * @return array<int, array>
     */
    public function rankByPoints(array $summaries): array {
        usort($summaries, function ($a, $b) {
            if ($a['has_results'] !== $b['has_results']) {
                return $b['has_results'] <=> $a['has_results'];
            }
            return ($b['overall_points'] ?? -1) <=> ($a['overall_points'] ?? -1);
        });

        return $summaries;
    }

    /**
     * Distinct overall grades present in a set of summaries, in descending
     * point order so headers render best→worst regardless of which letters
     * the school's BGCSE grading scale uses.
     *
     * @param  array<int, array>  $summaries
     * @return array<int, string>
     */
    public function distinctOverallGrades(array $summaries): array {
        $byGrade = [];
        foreach ($summaries as $summary) {
            $grade = $summary['overall_grade'] ?? null;
            if ($grade === null || $grade === '') {
                continue;
            }

            // Track the highest points seen for each grade label so we can
            // sort grade columns from best (highest points) to worst.
            $points = $summary['overall_points'] ?? 0;
            $byGrade[$grade] = max($byGrade[$grade] ?? 0, (float) $points);
        }

        arsort($byGrade);

        return array_keys($byGrade);
    }

    /**
     * Count of students per overall grade label.
     *
     * @param  array<int, array>  $summaries
     * @return array<string, int>
     */
    public function gradeDistribution(array $summaries): array {
        $distribution = [];
        foreach ($summaries as $summary) {
            $grade = $summary['overall_grade'] ?? null;
            if ($grade === null || $grade === '') {
                continue;
            }
            $distribution[$grade] = ($distribution[$grade] ?? 0) + 1;
        }

        return $distribution;
    }
}
