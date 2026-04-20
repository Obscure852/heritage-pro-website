<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityAuditLog;
use App\Models\Activities\ActivityEligibilityTarget;
use App\Models\Activities\ActivityEnrollment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ActivityRosterService
{
    public function enrollStudent(Activity $activity, array $data, User $actor): ActivityEnrollment
    {
        $currentActivity = $activity->fresh() ?? $activity;

        $this->assertRosterChangesAllowed($currentActivity);

        $studentSnapshot = $this->findCurrentStudentSnapshot($currentActivity, (int) $data['student_id']);

        if (!$studentSnapshot) {
            throw ValidationException::withMessages([
                'student_id' => 'Select a student who is active in this activity term before enrolling them.',
            ]);
        }

        $this->assertNoDuplicateActiveEnrollment($currentActivity, (int) $studentSnapshot->id);
        $this->assertCapacityAvailable($currentActivity, 1);

        return DB::transaction(function () use ($activity, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);

            $this->assertRosterChangesAllowed($lockedActivity);

            $studentSnapshot = $this->findCurrentStudentSnapshot($lockedActivity, (int) $data['student_id']);

            if (!$studentSnapshot) {
                throw ValidationException::withMessages([
                    'student_id' => 'Select a student who is active in this activity term before enrolling them.',
                ]);
            }

            $this->assertNoDuplicateActiveEnrollment($lockedActivity, (int) $studentSnapshot->id);
            $this->assertCapacityAvailable($lockedActivity, 1);

            $joinedAt = $this->resolveTimestamp($data['joined_at'] ?? null, now());

            $enrollment = $lockedActivity->enrollments()->create([
                'student_id' => $studentSnapshot->id,
                'term_id' => $lockedActivity->term_id,
                'year' => $lockedActivity->year,
                'status' => ActivityEnrollment::STATUS_ACTIVE,
                'joined_at' => $joinedAt,
                'joined_by' => $actor->id,
                'source' => ActivityEnrollment::SOURCE_MANUAL,
                'grade_id_snapshot' => $studentSnapshot->grade_id,
                'klass_id_snapshot' => $studentSnapshot->klass_id,
                'house_id_snapshot' => $studentSnapshot->house_id,
            ]);

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'enrollment_added',
                null,
                $this->enrollmentSnapshot($enrollment->fresh($this->enrollmentRelations())),
                'Activity enrollment added manually.'
            );

            return $enrollment->fresh($this->enrollmentRelations());
        });
    }

    public function bulkEnrollEligibleStudents(Activity $activity, array $data, User $actor): int
    {
        $currentActivity = $activity->fresh() ?? $activity;

        $this->assertRosterChangesAllowed($currentActivity);

        if (!$currentActivity->eligibilityTargets()->exists()) {
            throw ValidationException::withMessages([
                'bulk' => 'Set at least one eligibility target before using bulk enrollment.',
            ]);
        }

        $selectedStudentIds = collect($data['student_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedStudentIds->isEmpty()) {
            throw ValidationException::withMessages([
                'student_ids' => 'Select at least one eligible student before allocating the roster.',
            ]);
        }

        $candidateRows = $this->eligibleStudentsQuery($currentActivity)
            ->whereIn('students.id', $selectedStudentIds->all())
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->get();

        if ($candidateRows->isEmpty()) {
            throw ValidationException::withMessages([
                'student_ids' => 'The selected students are no longer eligible for bulk enrollment.',
            ]);
        }

        if ($candidateRows->count() !== $selectedStudentIds->count()) {
            throw ValidationException::withMessages([
                'student_ids' => 'One or more selected students are no longer eligible or already have an active roster entry.',
            ]);
        }

        $this->assertCapacityAvailable($currentActivity, $candidateRows->count());

        return DB::transaction(function () use ($activity, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);

            $this->assertRosterChangesAllowed($lockedActivity);

            if (!$lockedActivity->eligibilityTargets()->exists()) {
                throw ValidationException::withMessages([
                    'bulk' => 'Set at least one eligibility target before using bulk enrollment.',
                ]);
            }

            $selectedStudentIds = collect($data['student_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if ($selectedStudentIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'student_ids' => 'Select at least one eligible student before allocating the roster.',
                ]);
            }

            $candidateRows = $this->eligibleStudentsQuery($lockedActivity)
                ->whereIn('students.id', $selectedStudentIds->all())
                ->orderBy('students.first_name')
                ->orderBy('students.last_name')
                ->get();

            if ($candidateRows->isEmpty()) {
                throw ValidationException::withMessages([
                    'student_ids' => 'The selected students are no longer eligible for bulk enrollment.',
                ]);
            }

            if ($candidateRows->count() !== $selectedStudentIds->count()) {
                throw ValidationException::withMessages([
                    'student_ids' => 'One or more selected students are no longer eligible or already have an active roster entry.',
                ]);
            }

            $this->assertCapacityAvailable($lockedActivity, $candidateRows->count());

            $joinedAt = $this->resolveTimestamp($data['joined_at'] ?? null, now());
            $timestamp = now();

            $payload = $candidateRows->map(function ($student) use ($lockedActivity, $actor, $joinedAt, $timestamp) {
                return [
                    'activity_id' => $lockedActivity->id,
                    'student_id' => $student->id,
                    'term_id' => $lockedActivity->term_id,
                    'year' => $lockedActivity->year,
                    'status' => ActivityEnrollment::STATUS_ACTIVE,
                    'joined_at' => $joinedAt,
                    'joined_by' => $actor->id,
                    'source' => ActivityEnrollment::SOURCE_BULK_FILTER,
                    'grade_id_snapshot' => $student->grade_id,
                    'klass_id_snapshot' => $student->klass_id,
                    'house_id_snapshot' => $student->house_id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            })->all();

            ActivityEnrollment::query()->insert($payload);

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'bulk_enrollment_added',
                null,
                [
                    'count' => count($payload),
                    'student_ids' => array_values(array_map(fn (object $student) => (int) $student->id, $candidateRows->all())),
                ],
                'Eligible students were bulk-enrolled into the activity.'
            );

            return count($payload);
        });
    }

    public function updateEnrollmentStatus(Activity $activity, ActivityEnrollment $enrollment, array $data, User $actor): ActivityEnrollment
    {
        $currentActivity = $activity->fresh() ?? $activity;
        $currentEnrollment = $enrollment->fresh() ?? $enrollment;

        if ($currentEnrollment->activity_id !== $currentActivity->id) {
            throw ValidationException::withMessages([
                'enrollment' => 'The selected roster record does not belong to this activity.',
            ]);
        }

        if ($currentEnrollment->status !== ActivityEnrollment::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'status' => 'Only active roster entries can be withdrawn, suspended, or completed.',
            ]);
        }

        $targetStatus = (string) $data['status'];

        if (!array_key_exists($targetStatus, ActivityEnrollment::closableStatuses())) {
            throw ValidationException::withMessages([
                'status' => 'Select a valid roster status change.',
            ]);
        }

        return DB::transaction(function () use ($activity, $enrollment, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);
            $lockedEnrollment = ActivityEnrollment::query()->lockForUpdate()->findOrFail($enrollment->id);

            if ($lockedEnrollment->activity_id !== $lockedActivity->id) {
                throw ValidationException::withMessages([
                    'enrollment' => 'The selected roster record does not belong to this activity.',
                ]);
            }

            if ($lockedEnrollment->status !== ActivityEnrollment::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'status' => 'Only active roster entries can be withdrawn, suspended, or completed.',
                ]);
            }

            $targetStatus = (string) $data['status'];

            if (!array_key_exists($targetStatus, ActivityEnrollment::closableStatuses())) {
                throw ValidationException::withMessages([
                    'status' => 'Select a valid roster status change.',
                ]);
            }

            $before = $this->enrollmentSnapshot($lockedEnrollment->fresh($this->enrollmentRelations()));

            $lockedEnrollment->forceFill([
                'status' => $targetStatus,
                'left_at' => $this->resolveTimestamp($data['left_at'] ?? null, now()),
                'left_by' => $actor->id,
                'exit_reason' => $data['exit_reason'],
            ])->save();

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'enrollment_status_changed',
                $before,
                $this->enrollmentSnapshot($lockedEnrollment->fresh($this->enrollmentRelations())),
                sprintf('Activity enrollment marked as %s.', $targetStatus)
            );

            return $lockedEnrollment->fresh($this->enrollmentRelations());
        });
    }

    public function manualEnrollmentCandidates(Activity $activity): Collection
    {
        return $this->excludeActiveEnrollments($this->termStudentQuery($activity), $activity)
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->get();
    }

    public function bulkEligibilityPreview(Activity $activity, int $limit = 8): array
    {
        if (!$activity->eligibilityTargets()->exists()) {
            return [
                'count' => 0,
                'students' => collect(),
            ];
        }

        $query = $this->eligibleStudentsQuery($activity)
            ->orderBy('students.first_name')
            ->orderBy('students.last_name');

        return [
            'count' => (clone $query)->count(),
            'students' => $limit > 0 ? $query->limit($limit)->get() : $query->get(),
        ];
    }

    public function enrollmentRelations(): array
    {
        return [
            'student:id,first_name,last_name,status',
            'joinedBy:id,firstname,lastname',
            'leftBy:id,firstname,lastname',
            'gradeSnapshot:id,name',
            'klassSnapshot:id,name,grade_id',
            'klassSnapshot.grade:id,name',
            'houseSnapshot:id,name',
        ];
    }

    private function assertRosterChangesAllowed(Activity $activity): void
    {
        if (in_array($activity->status, [Activity::STATUS_CLOSED, Activity::STATUS_ARCHIVED], true)) {
            throw ValidationException::withMessages([
                'activity' => 'Roster changes are blocked once an activity is closed or archived.',
            ]);
        }
    }

    private function assertNoDuplicateActiveEnrollment(Activity $activity, int $studentId): void
    {
        $duplicateExists = $activity->enrollments()
            ->active()
            ->where('student_id', $studentId)
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'student_id' => 'That student already has an active enrollment on this activity.',
            ]);
        }
    }

    private function assertCapacityAvailable(Activity $activity, int $incomingCount): void
    {
        if (!$activity->capacity) {
            return;
        }

        $currentActiveCount = $activity->enrollments()->active()->count();

        if (($currentActiveCount + $incomingCount) > $activity->capacity) {
            $remaining = max($activity->capacity - $currentActiveCount, 0);

            throw ValidationException::withMessages([
                'capacity' => $remaining > 0
                    ? sprintf('Only %d roster slot(s) remain for this activity.', $remaining)
                    : 'This activity is already at full capacity.',
            ]);
        }
    }

    private function findCurrentStudentSnapshot(Activity $activity, int $studentId): ?object
    {
        return $this->termStudentQuery($activity)
            ->where('students.id', $studentId)
            ->first();
    }

    private function termStudentQuery(Activity $activity)
    {
        $currentKlassSubquery = DB::table('klass_student')
            ->select('student_id', DB::raw('MAX(klass_id) as klass_id'))
            ->where('term_id', $activity->term_id)
            ->groupBy('student_id');

        $currentHouseSubquery = DB::table('student_house')
            ->select('student_id', DB::raw('MAX(house_id) as house_id'))
            ->where('term_id', $activity->term_id)
            ->groupBy('student_id');

        return DB::table('students')
            ->join('student_term', function ($join) use ($activity) {
                $join->on('student_term.student_id', '=', 'students.id')
                    ->where('student_term.term_id', '=', $activity->term_id)
                    ->where('student_term.year', '=', $activity->year)
                    ->where('student_term.status', '=', Student::STATUS_CURRENT)
                    ->whereNull('student_term.deleted_at');
            })
            ->leftJoinSub($currentKlassSubquery, 'current_klass', function ($join) {
                $join->on('current_klass.student_id', '=', 'students.id');
            })
            ->leftJoin('klasses', 'klasses.id', '=', 'current_klass.klass_id')
            ->leftJoinSub($currentHouseSubquery, 'current_house', function ($join) {
                $join->on('current_house.student_id', '=', 'students.id');
            })
            ->leftJoin('houses', 'houses.id', '=', 'current_house.house_id')
            ->leftJoin('grades', 'grades.id', '=', 'student_term.grade_id')
            ->leftJoin('student_filters', 'student_filters.id', '=', 'students.student_filter_id')
            ->where('students.status', Student::STATUS_CURRENT)
            ->whereNull('students.deleted_at')
            ->select([
                'students.id',
                'students.first_name',
                'students.last_name',
                'students.student_filter_id',
                'student_filters.name as student_filter_name',
                'student_term.grade_id',
                'grades.name as grade_name',
                'current_klass.klass_id',
                'klasses.name as klass_name',
                'current_house.house_id',
                'houses.name as house_name',
            ]);
    }

    private function eligibleStudentsQuery(Activity $activity)
    {
        $targetIds = $activity->eligibilityTargets()
            ->get()
            ->groupBy('target_type')
            ->map(fn (Collection $targets) => $targets->pluck('target_id')->map(fn ($id) => (int) $id)->all());

        $query = $this->excludeActiveEnrollments($this->termStudentQuery($activity), $activity);

        $gradeIds = $this->expandEquivalentEligibilityTargetIds(
            $activity,
            ActivityEligibilityTarget::TARGET_GRADE,
            $targetIds->get(ActivityEligibilityTarget::TARGET_GRADE, [])
        );

        $klassIds = $this->expandEquivalentEligibilityTargetIds(
            $activity,
            ActivityEligibilityTarget::TARGET_CLASS,
            $targetIds->get(ActivityEligibilityTarget::TARGET_CLASS, [])
        );

        $houseIds = $this->expandEquivalentEligibilityTargetIds(
            $activity,
            ActivityEligibilityTarget::TARGET_HOUSE,
            $targetIds->get(ActivityEligibilityTarget::TARGET_HOUSE, [])
        );

        $studentFilterIds = $this->expandEquivalentEligibilityTargetIds(
            $activity,
            ActivityEligibilityTarget::TARGET_STUDENT_FILTER,
            $targetIds->get(ActivityEligibilityTarget::TARGET_STUDENT_FILTER, [])
        );

        if (!empty($gradeIds)) {
            $query->whereIn('student_term.grade_id', $gradeIds);
        }

        if (!empty($klassIds)) {
            $query->whereIn('current_klass.klass_id', $klassIds);
        }

        if (!empty($houseIds)) {
            $query->whereIn('current_house.house_id', $houseIds);
        }

        if (!empty($studentFilterIds)) {
            $query->whereIn('students.student_filter_id', $studentFilterIds);
        }

        return $query;
    }

    private function expandEquivalentEligibilityTargetIds(Activity $activity, string $targetType, array $targetIds): array
    {
        $selectedIds = collect($targetIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            return [];
        }

        $options = match ($targetType) {
            ActivityEligibilityTarget::TARGET_GRADE => DB::table('grades')
                ->where('term_id', $activity->term_id)
                ->where('year', $activity->year)
                ->select('id', 'name')
                ->get(),
            ActivityEligibilityTarget::TARGET_CLASS => DB::table('klasses')
                ->leftJoin('grades', 'grades.id', '=', 'klasses.grade_id')
                ->where('klasses.term_id', $activity->term_id)
                ->where('klasses.year', $activity->year)
                ->select('klasses.id', 'klasses.name', 'grades.name as grade_name')
                ->get(),
            ActivityEligibilityTarget::TARGET_HOUSE => DB::table('houses')
                ->where('term_id', $activity->term_id)
                ->where('year', $activity->year)
                ->select('id', 'name')
                ->get(),
            ActivityEligibilityTarget::TARGET_STUDENT_FILTER => DB::table('student_filters')
                ->select('id', 'name')
                ->get(),
            default => collect(),
        };

        if ($options->isEmpty()) {
            return $selectedIds->all();
        }

        $selectedDisplayKeys = $options
            ->whereIn('id', $selectedIds->all())
            ->map(fn (object $option) => $this->eligibilityDisplayKey($targetType, $option))
            ->filter()
            ->unique()
            ->values();

        if ($selectedDisplayKeys->isEmpty()) {
            return $selectedIds->all();
        }

        return $options
            ->filter(fn (object $option) => $selectedDisplayKeys->contains($this->eligibilityDisplayKey($targetType, $option)))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function eligibilityDisplayKey(string $targetType, object $option): string
    {
        return match ($targetType) {
            ActivityEligibilityTarget::TARGET_GRADE,
            ActivityEligibilityTarget::TARGET_HOUSE,
            ActivityEligibilityTarget::TARGET_STUDENT_FILTER => Str::lower(trim((string) ($option->name ?? ''))),
            ActivityEligibilityTarget::TARGET_CLASS => Str::lower(trim(sprintf(
                '%s|%s',
                (string) ($option->name ?? ''),
                (string) ($option->grade_name ?? '')
            ))),
            default => (string) ($option->id ?? ''),
        };
    }

    private function excludeActiveEnrollments($query, Activity $activity)
    {
        return $query->whereNotExists(function ($subquery) use ($activity) {
            $subquery->select(DB::raw(1))
                ->from('activity_enrollments')
                ->whereColumn('activity_enrollments.student_id', 'students.id')
                ->where('activity_enrollments.activity_id', $activity->id)
                ->where('activity_enrollments.term_id', $activity->term_id)
                ->where('activity_enrollments.status', ActivityEnrollment::STATUS_ACTIVE)
                ->whereNull('activity_enrollments.deleted_at');
        });
    }

    private function resolveTimestamp(mixed $value, Carbon $fallback): Carbon
    {
        if (blank($value)) {
            return $fallback;
        }

        return Carbon::parse((string) $value)->startOfDay();
    }

    private function enrollmentSnapshot(ActivityEnrollment $enrollment): array
    {
        return [
            'id' => $enrollment->id,
            'student_id' => $enrollment->student_id,
            'student_name' => $enrollment->student?->full_name,
            'status' => $enrollment->status,
            'source' => $enrollment->source,
            'joined_at' => optional($enrollment->joined_at)->toDateTimeString(),
            'left_at' => optional($enrollment->left_at)->toDateTimeString(),
            'exit_reason' => $enrollment->exit_reason,
            'grade' => $enrollment->gradeSnapshot?->name,
            'klass' => $enrollment->klassSnapshot?->name,
            'house' => $enrollment->houseSnapshot?->name,
        ];
    }

    private function recordAudit(User $user, Activity $activity, string $action, ?array $oldValues, ?array $newValues, string $notes): void
    {
        ActivityAuditLog::create([
            'user_id' => $user->id,
            'entity_type' => Activity::class,
            'entity_id' => $activity->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'notes' => $notes,
            'ip_address' => request()?->ip(),
            'user_agent' => (string) request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
