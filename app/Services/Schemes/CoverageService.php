<?php

namespace App\Services\Schemes;

use App\Models\Schemes\SchemeOfWork;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CoverageService {
    /**
     * Objective-level coverage for a single scheme.
     *
     * Returns planned/taught/assessed counts via aggregate DB queries.
     * Cached 300 seconds keyed by scheme ID.
     *
     * @return array{planned: int, taught: int, assessed: int}
     */
    public function objectiveCoverage(SchemeOfWork $scheme): array {
        $cacheKey = "coverage_scheme_{$scheme->id}";

        return Cache::remember($cacheKey, 300, function () use ($scheme): array {
            // Fetch entry IDs for the scheme without loading full models
            $entryIds = DB::table('scheme_of_work_entries')
                ->where('scheme_of_work_id', $scheme->id)
                ->whereNull('deleted_at')
                ->pluck('id');

            // Planned: distinct syllabus objectives linked to any entry in this scheme
            $planned = DB::table('scheme_entry_objectives')
                ->whereIn('scheme_of_work_entry_id', $entryIds)
                ->distinct('syllabus_objective_id')
                ->count('syllabus_objective_id');

            // Entry IDs that have at least one lesson plan with status = 'taught'
            $taughtEntryIds = DB::table('lesson_plans')
                ->whereIn('scheme_of_work_entry_id', $entryIds)
                ->where('status', 'taught')
                ->distinct()
                ->pluck('scheme_of_work_entry_id');

            // Taught: distinct objectives on entries that have a taught lesson plan
            $taught = empty($taughtEntryIds->all()) ? 0 : DB::table('scheme_entry_objectives')
                ->whereIn('scheme_of_work_entry_id', $taughtEntryIds)
                ->distinct('syllabus_objective_id')
                ->count('syllabus_objective_id');

            // Assessed: distinct objectives linked to tests via test_syllabus_objectives
            // where test is for the same term and not soft-deleted
            // Must also be an objective that is in this scheme's entry objectives
            $schemeObjectiveIds = DB::table('scheme_entry_objectives')
                ->whereIn('scheme_of_work_entry_id', $entryIds)
                ->distinct()
                ->pluck('syllabus_objective_id');

            $assessed = empty($schemeObjectiveIds->all()) ? 0 : DB::table('test_syllabus_objectives')
                ->join('tests', 'tests.id', '=', 'test_syllabus_objectives.test_id')
                ->whereNull('tests.deleted_at')
                ->where('tests.term_id', $scheme->term_id)
                ->whereIn('test_syllabus_objectives.syllabus_objective_id', $schemeObjectiveIds)
                ->distinct('test_syllabus_objectives.syllabus_objective_id')
                ->count('test_syllabus_objectives.syllabus_objective_id');

            return compact('planned', 'taught', 'assessed');
        });
    }

    /**
     * HOD-level coverage summary: per teacher, per scheme in the HOD's department.
     *
     * Resolves department membership by checking departments.department_head
     * or departments.assistant for the HOD's user ID.
     *
     * Returns a Collection of objects with: teacher_name, teacher_id, subject_name,
     * scheme_id, scheme_status, planned_count, taught_count, assessed_count.
     * Cached 300 seconds keyed by hod_id + term_id.
     */
    public function hodCoverage(User $hod, Term $term): Collection {
        $cacheKey = "hod_coverage_{$hod->id}_{$term->id}";

        return Cache::remember($cacheKey, 300, function () use ($hod, $term): Collection {
            // Find all department IDs where this user is head or assistant
            $departmentIds = DB::table('departments')
                ->where('department_head', $hod->id)
                ->orWhere('assistant', $hod->id)
                ->pluck('id');

            if ($departmentIds->isEmpty()) {
                return collect();
            }

            // Correlated subquery helpers as DB::raw expressions for planned/taught/assessed
            $plannedSubquery = 'SELECT COUNT(DISTINCT seo.syllabus_objective_id)
                FROM scheme_entry_objectives seo
                INNER JOIN scheme_of_work_entries sowe
                    ON sowe.id = seo.scheme_of_work_entry_id
                    AND sowe.scheme_of_work_id = schemes_of_work.id
                    AND sowe.deleted_at IS NULL';

            $taughtSubquery = 'SELECT COUNT(DISTINCT seo2.syllabus_objective_id)
                FROM scheme_entry_objectives seo2
                INNER JOIN scheme_of_work_entries sowe2
                    ON sowe2.id = seo2.scheme_of_work_entry_id
                    AND sowe2.scheme_of_work_id = schemes_of_work.id
                    AND sowe2.deleted_at IS NULL
                INNER JOIN lesson_plans lp
                    ON lp.scheme_of_work_entry_id = sowe2.id
                    AND lp.status = "taught"';

            $assessedSubquery = 'SELECT COUNT(DISTINCT tso.syllabus_objective_id)
                FROM test_syllabus_objectives tso
                INNER JOIN tests t
                    ON t.id = tso.test_id
                    AND t.deleted_at IS NULL
                    AND t.term_id = schemes_of_work.term_id
                INNER JOIN scheme_entry_objectives seo3
                    ON seo3.syllabus_objective_id = tso.syllabus_objective_id
                INNER JOIN scheme_of_work_entries sowe3
                    ON sowe3.id = seo3.scheme_of_work_entry_id
                    AND sowe3.scheme_of_work_id = schemes_of_work.id
                    AND sowe3.deleted_at IS NULL';

            // Query klass_subject schemes in the HOD's departments
            $klassSchemes = DB::table('schemes_of_work')
                ->join('klass_subject as ks', 'ks.id', '=', 'schemes_of_work.klass_subject_id')
                ->join('grade_subject', 'grade_subject.id', '=', 'ks.grade_subject_id')
                ->join('subjects', 'subjects.id', '=', 'grade_subject.subject_id')
                ->join('users', 'users.id', '=', 'schemes_of_work.teacher_id')
                ->whereNull('schemes_of_work.deleted_at')
                ->where('schemes_of_work.term_id', $term->id)
                ->whereIn('grade_subject.department_id', $departmentIds)
                ->selectRaw("
                    users.id AS teacher_id,
                    CONCAT(users.firstname, ' ', users.lastname) AS teacher_name,
                    subjects.name AS subject_name,
                    schemes_of_work.id AS scheme_id,
                    schemes_of_work.status AS scheme_status,
                    ({$plannedSubquery}) AS planned_count,
                    ({$taughtSubquery}) AS taught_count,
                    ({$assessedSubquery}) AS assessed_count
                ")
                ->orderByRaw("CONCAT(users.firstname, ' ', users.lastname)")
                ->orderBy('subjects.name')
                ->get();

            // Query optional_subject schemes in the HOD's departments
            $optionalSchemes = DB::table('schemes_of_work')
                ->join('optional_subjects', 'optional_subjects.id', '=', 'schemes_of_work.optional_subject_id')
                ->join('grade_subject', 'grade_subject.id', '=', 'optional_subjects.grade_subject_id')
                ->join('subjects', 'subjects.id', '=', 'grade_subject.subject_id')
                ->join('users', 'users.id', '=', 'schemes_of_work.teacher_id')
                ->whereNull('schemes_of_work.deleted_at')
                ->where('schemes_of_work.term_id', $term->id)
                ->whereIn('grade_subject.department_id', $departmentIds)
                ->selectRaw("
                    users.id AS teacher_id,
                    CONCAT(users.firstname, ' ', users.lastname) AS teacher_name,
                    subjects.name AS subject_name,
                    schemes_of_work.id AS scheme_id,
                    schemes_of_work.status AS scheme_status,
                    ({$plannedSubquery}) AS planned_count,
                    ({$taughtSubquery}) AS taught_count,
                    ({$assessedSubquery}) AS assessed_count
                ")
                ->orderByRaw("CONCAT(users.firstname, ' ', users.lastname)")
                ->orderBy('subjects.name')
                ->get();

            return $klassSchemes->merge($optionalSchemes)->sortBy('teacher_name')->values();
        });
    }

    /**
     * School-wide scheme completion summary for admin dashboard.
     *
     * Returns an associative array keyed by status with counts.
     * Cached 300 seconds keyed by term_id.
     *
     * @return array<string, int>
     */
    public function schoolCompletion(Term $term): array {
        $cacheKey = "school_completion_{$term->id}";

        return Cache::remember($cacheKey, 300, function () use ($term): array {
            return DB::table('schemes_of_work')
                ->whereNull('deleted_at')
                ->where('term_id', $term->id)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        });
    }

    /**
     * Teachers with klass_subject assignments but no scheme for the given term.
     *
     * Uses a LEFT JOIN to find klass_subject rows with no matching scheme record.
     * Returns a Collection of objects with: teacher_name, subject_name, grade_name, klass_subject_id.
     * Cached 300 seconds keyed by term_id.
     */
    public function missingSchemes(Term $term, ?int $gradeId = null): Collection {
        $cacheKey = "missing_schemes_{$term->id}" . ($gradeId ? "_{$gradeId}" : '');

        return Cache::remember($cacheKey, 300, function () use ($term, $gradeId): Collection {
            $query = DB::table('klass_subject as ks')
                ->join('grade_subject', 'ks.grade_subject_id', '=', 'grade_subject.id')
                ->join('grades', 'grades.id', '=', 'grade_subject.grade_id')
                ->join('subjects', 'subjects.id', '=', 'grade_subject.subject_id')
                ->join('users', 'users.id', '=', 'ks.user_id')
                ->leftJoin('klasses', 'klasses.id', '=', 'ks.klass_id')
                ->leftJoin('schemes_of_work', function ($join) use ($term) {
                    $join->on('schemes_of_work.klass_subject_id', '=', 'ks.id')
                         ->where('schemes_of_work.term_id', '=', $term->id)
                         ->whereNull('schemes_of_work.deleted_at');
                })
                ->where('ks.term_id', $term->id)
                ->whereNull('schemes_of_work.id');

            if ($gradeId) {
                $query->where('grade_subject.grade_id', $gradeId);
            }

            return $query->selectRaw("
                    CONCAT(users.firstname, ' ', users.lastname) as teacher_name,
                    subjects.name as subject_name,
                    grades.name as grade_name,
                    grades.id as grade_id,
                    klasses.name as class_name,
                    ks.id as klass_subject_id
                ")
                ->orderBy('grades.name')
                ->orderByRaw("CONCAT(users.firstname, ' ', users.lastname)")
                ->get();
        });
    }
}
