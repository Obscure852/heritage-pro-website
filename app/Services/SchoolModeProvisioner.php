<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\SchoolSetup;
use App\Models\Subject;
use App\Models\Term;
use App\Models\Test;
use App\Support\AcademicStructureRegistry;
use App\Support\SyllabusSeedRegistry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SchoolModeProvisioner
{
    public function __construct(
        private readonly SchoolModeResolver $modeResolver
    ) {
    }

    /**
     * @return array<string, int|string>
     */
    public function provisionMode(string $mode, ?Term $term = null): array
    {
        $resolvedMode = SchoolSetup::normalizeType($mode);
        $term ??= Term::query()->find(session('selected_term_id')) ?? \App\Helpers\TermHelper::getCurrentTerm();

        if (!$term) {
            throw new RuntimeException('A current term is required before provisioning school mode data.');
        }

        return DB::transaction(function () use ($resolvedMode, $term) {
            $departments = $this->provisionDepartments($resolvedMode);
            $grades = $this->provisionGrades($resolvedMode, $term);
            $subjects = $this->provisionSubjects($resolvedMode);
            $gradeSubjects = $this->provisionGradeSubjects($resolvedMode, $term, $grades, $subjects);
            $components = $this->provisionComponents($gradeSubjects, $term);
            $tests = $this->provisionDefaultTests($resolvedMode, $term);

            SchoolSetup::query()->latest('id')->first()?->update(['type' => $resolvedMode]);
            Cache::forget('school_type');
            Cache::forget('school_type_threshold');

            return [
                'mode' => $resolvedMode,
                'departments' => $departments,
                'grades' => $grades->count(),
                'subjects' => $subjects->count(),
                'grade_subjects' => $gradeSubjects->count(),
                'components' => $components,
                'tests' => $tests,
            ];
        });
    }

    private function provisionDepartments(string $mode): int
    {
        $created = 0;

        foreach (AcademicStructureRegistry::departmentNamesForMode($mode) as $departmentName) {
            $department = Department::query()->firstOrCreate(['name' => $departmentName]);
            if ($department->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Grade>
     */
    private function provisionGrades(string $mode, Term $term)
    {
        $grades = collect();

        foreach (AcademicStructureRegistry::gradeDefinitionsForMode($mode) as $definition) {
            $grade = Grade::query()->withTrashed()->firstOrNew([
                'term_id' => $term->id,
                'year' => $term->year,
                'name' => $definition['name'],
            ]);

            $grade->fill([
                'sequence' => $definition['sequence'],
                'promotion' => $definition['promotion'],
                'description' => $definition['description'],
                'level' => $definition['level'],
                'active' => true,
            ]);

            if ($grade->trashed()) {
                $grade->restore();
            }

            $grade->save();
            $grades->push($grade);
        }

        return $grades->keyBy(fn (Grade $grade) => "{$grade->name}|{$grade->level}");
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Subject>
     */
    private function provisionSubjects(string $mode)
    {
        $subjects = collect();

        foreach (AcademicStructureRegistry::subjectDefinitionsForMode($mode) as $definition) {
            $subject = Subject::query()->withTrashed()->firstOrNew([
                'level' => $definition['level'],
                'canonical_key' => $definition['canonical_key'],
            ]);

            $subject->fill([
                'abbrev' => $definition['abbrev'],
                'name' => $definition['name'],
                'components' => $definition['components'],
                'description' => $definition['description'],
                'department' => $definition['department'],
                'syllabus_url' => SyllabusSeedRegistry::urlFor($definition['level'], $definition['abbrev'], $definition['name']),
            ]);

            if ($subject->trashed()) {
                $subject->restore();
            }

            $subject->save();
            $subjects->push($subject);
        }

        return $subjects->keyBy(fn (Subject $subject) => "{$subject->level}|{$subject->canonical_key}");
    }

    /**
     * @param  \Illuminate\Support\Collection<string, \App\Models\Grade>  $grades
     * @param  \Illuminate\Support\Collection<string, \App\Models\Subject>  $subjects
     * @return \Illuminate\Support\Collection<int, \App\Models\GradeSubject>
     */
    private function provisionGradeSubjects(string $mode, Term $term, $grades, $subjects)
    {
        $gradeSubjects = collect();
        $departments = Department::query()->pluck('id', 'name');
        $fallbackDepartmentId = $departments->get('Administration') ?? Department::query()->value('id');

        foreach (AcademicStructureRegistry::gradeSubjectDefinitionsForMode($mode) as $definition) {
            $gradeKey = "{$definition['grade_name']}|{$definition['grade_level']}";
            $subjectKey = "{$definition['subject_level']}|{$definition['canonical_key']}";

            $grade = $grades->get($gradeKey);
            $subject = $subjects->get($subjectKey);

            if (!$grade || !$subject) {
                continue;
            }

            $gradeSubject = GradeSubject::query()->withTrashed()->firstOrNew([
                'term_id' => $term->id,
                'year' => $term->year,
                'grade_id' => $grade->id,
                'subject_id' => $subject->id,
            ]);

            $gradeSubject->fill([
                'sequence' => $definition['sequence'],
                'department_id' => $departments->get($subject->department) ?? $fallbackDepartmentId,
                'type' => $definition['type'],
                'mandatory' => $definition['mandatory'],
                'active' => true,
            ]);

            if ($gradeSubject->trashed()) {
                $gradeSubject->restore();
            }

            $gradeSubject->save();
            $gradeSubjects->push($gradeSubject->loadMissing('grade', 'subject'));
        }

        return $gradeSubjects;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\GradeSubject>  $gradeSubjects
     */
    private function provisionComponents($gradeSubjects, Term $term): int
    {
        $created = 0;

        foreach ($gradeSubjects as $gradeSubject) {
            $componentNames = AcademicStructureRegistry::preschoolComponents()[$gradeSubject->subject->canonical_key] ?? null;
            if (!$componentNames) {
                continue;
            }

            foreach ($componentNames as $name) {
                $exists = DB::table('components')
                    ->where('term_id', $term->id)
                    ->where('grade_subject_id', $gradeSubject->id)
                    ->where('grade_id', $gradeSubject->grade_id)
                    ->where('name', $name)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('components')->insert([
                    'term_id' => $term->id,
                    'grade_subject_id' => $gradeSubject->id,
                    'grade_id' => $gradeSubject->grade_id,
                    'name' => $name,
                    'description' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $created++;
            }
        }

        return $created;
    }

    private function provisionDefaultTests(string $mode, Term $term): int
    {
        $created = 0;
        $supportedLevels = AcademicStructureRegistry::supportedTestLevelsForMode($mode);

        $gradeSubjects = GradeSubject::query()
            ->with(['subject', 'grade'])
            ->where('term_id', $term->id)
            ->whereHas('subject', function ($query) use ($supportedLevels) {
                $query->whereIn('level', array_map(
                    fn (string $level) => $this->modeResolver->subjectLevelForLevel($level) ?? $level,
                    $supportedLevels
                ))->where('components', false);
            })
            ->get();

        $startDate = Carbon::parse($term->start_date);
        $endDate = Carbon::parse($term->end_date);
        $testDates = collect();
        $cursor = $startDate->copy();

        while ($cursor->lessThanOrEqualTo($endDate) && $testDates->count() < 3) {
            $testDate = $cursor->copy()->endOfMonth();
            if ($testDate->greaterThan($endDate)) {
                break;
            }

            $testDates->push($testDate);
            $cursor->addMonth();
        }

        foreach ($gradeSubjects as $gradeSubject) {
            foreach ($testDates as $sequence => $testDate) {
                $monthName = $testDate->format('F');
                $abbrev = $testDate->format('M');

                $test = Test::query()->firstOrCreate([
                    'grade_subject_id' => $gradeSubject->id,
                    'term_id' => $term->id,
                    'grade_id' => $gradeSubject->grade_id,
                    'type' => 'CA',
                    'sequence' => $sequence + 1,
                ], [
                    'name' => $monthName,
                    'abbrev' => $abbrev,
                    'out_of' => 100,
                    'year' => $term->year,
                    'assessment' => 1,
                    'start_date' => $testDate->copy()->startOfMonth()->toDateString(),
                    'end_date' => $testDate->copy()->endOfMonth()->toDateString(),
                ]);

                if ($test->wasRecentlyCreated) {
                    $created++;
                }
            }

            $examDate = $endDate->copy();
            $exam = Test::query()->firstOrCreate([
                'grade_subject_id' => $gradeSubject->id,
                'term_id' => $term->id,
                'grade_id' => $gradeSubject->grade_id,
                'type' => 'Exam',
                'sequence' => 1,
            ], [
                'name' => $examDate->format('F') . ' Exam',
                'abbrev' => 'Exam',
                'out_of' => 100,
                'year' => $term->year,
                'assessment' => 1,
                'start_date' => $examDate->copy()->startOfMonth()->toDateString(),
                'end_date' => $examDate->copy()->endOfMonth()->toDateString(),
            ]);

            if ($exam->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}
