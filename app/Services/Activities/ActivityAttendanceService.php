<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityAuditLog;
use App\Models\Activities\ActivityEnrollment;
use App\Models\Activities\ActivitySession;
use App\Models\Activities\ActivitySessionAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityAttendanceService
{
    public function eligibleEnrollments(Activity $activity, ActivitySession $session): Collection
    {
        $this->assertSessionBelongsToActivity($activity, $session);

        $sessionMoment = $session->start_datetime instanceof Carbon
            ? $session->start_datetime->copy()
            : Carbon::parse($session->session_date)->endOfDay();

        return $activity->enrollments()
            ->with($this->attendanceEnrollmentRelations())
            ->where(function ($query) use ($sessionMoment) {
                $query->whereNull('joined_at')
                    ->orWhere('joined_at', '<=', $sessionMoment);
            })
            ->where(function ($query) use ($sessionMoment) {
                $query->whereNull('left_at')
                    ->orWhere('left_at', '>=', $sessionMoment);
            })
            ->get()
            ->sortBy(fn (ActivityEnrollment $enrollment) => strtolower((string) ($enrollment->student?->full_name ?? '')))
            ->values();
    }

    public function attendanceMap(ActivitySession $session): Collection
    {
        return $session->attendances()
            ->with(['markedBy:id,firstname,lastname'])
            ->get()
            ->keyBy('activity_enrollment_id');
    }

    public function saveAttendance(Activity $activity, ActivitySession $session, array $data, User $actor): int
    {
        $currentActivity = $activity->fresh() ?? $activity;
        $currentSession = $session->fresh() ?? $session;

        $this->assertSessionBelongsToActivity($currentActivity, $currentSession);
        $this->assertAttendanceWritable($currentActivity, $currentSession);

        return DB::transaction(function () use ($activity, $session, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);
            $lockedSession = ActivitySession::query()->lockForUpdate()->findOrFail($session->id);

            $this->assertSessionBelongsToActivity($lockedActivity, $lockedSession);
            $this->assertAttendanceWritable($lockedActivity, $lockedSession);

            $eligibleEnrollments = $this->eligibleEnrollments($lockedActivity, $lockedSession)->keyBy('id');
            $attendanceRows = collect($data['attendance'] ?? []);

            if ($attendanceRows->isEmpty()) {
                throw ValidationException::withMessages([
                    'attendance' => 'Add attendance statuses before saving this session.',
                ]);
            }

            $timestamp = now();

            foreach ($attendanceRows as $enrollmentId => $row) {
                $enrollmentId = (int) $enrollmentId;

                if (!$eligibleEnrollments->has($enrollmentId)) {
                    throw ValidationException::withMessages([
                        'attendance' => 'Attendance can only be recorded for students enrolled on the session date.',
                    ]);
                }

                /** @var \App\Models\Activities\ActivityEnrollment $enrollment */
                $enrollment = $eligibleEnrollments->get($enrollmentId);

                ActivitySessionAttendance::query()->updateOrCreate(
                    [
                        'activity_session_id' => $lockedSession->id,
                        'student_id' => $enrollment->student_id,
                    ],
                    [
                        'activity_enrollment_id' => $enrollment->id,
                        'status' => $row['status'],
                        'remarks' => $row['remarks'] ?? null,
                        'marked_by' => $actor->id,
                        'marked_at' => $timestamp,
                    ]
                );
            }

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'attendance_saved',
                null,
                [
                    'session_id' => $lockedSession->id,
                    'saved_count' => $attendanceRows->count(),
                ],
                'Activity session attendance saved.'
            );

            return $attendanceRows->count();
        });
    }

    public function finalizeAttendance(Activity $activity, ActivitySession $session, User $actor): ActivitySession
    {
        $currentActivity = $activity->fresh() ?? $activity;
        $currentSession = $session->fresh() ?? $session;

        $this->assertSessionBelongsToActivity($currentActivity, $currentSession);
        $this->assertAttendanceWritable($currentActivity, $currentSession);

        return DB::transaction(function () use ($activity, $session, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);
            $lockedSession = ActivitySession::query()->lockForUpdate()->findOrFail($session->id);

            $this->assertSessionBelongsToActivity($lockedActivity, $lockedSession);
            $this->assertAttendanceWritable($lockedActivity, $lockedSession);

            $eligibleEnrollmentIds = $this->eligibleEnrollments($lockedActivity, $lockedSession)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $recordedCount = ActivitySessionAttendance::query()
                ->where('activity_session_id', $lockedSession->id)
                ->whereIn('activity_enrollment_id', $eligibleEnrollmentIds)
                ->count();

            if (count($eligibleEnrollmentIds) !== $recordedCount) {
                throw ValidationException::withMessages([
                    'attendance' => 'Mark attendance for every eligible student before finalizing this session.',
                ]);
            }

            $before = $this->sessionSnapshot($lockedSession);

            $lockedSession->attendance_locked = true;

            if ($lockedSession->status === ActivitySession::STATUS_PLANNED) {
                $lockedSession->status = ActivitySession::STATUS_COMPLETED;
            }

            $lockedSession->save();

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'attendance_finalized',
                $before,
                $this->sessionSnapshot($lockedSession->fresh()),
                'Activity session attendance finalized.'
            );

            return $lockedSession->fresh(['schedule', 'creator']);
        });
    }

    public function reopenAttendance(Activity $activity, ActivitySession $session, User $actor): ActivitySession
    {
        $currentActivity = $activity->fresh() ?? $activity;
        $currentSession = $session->fresh() ?? $session;

        $this->assertSessionBelongsToActivity($currentActivity, $currentSession);

        if (!$currentSession->attendance_locked) {
            throw ValidationException::withMessages([
                'attendance' => 'This session attendance is already open for editing.',
            ]);
        }

        return DB::transaction(function () use ($activity, $session, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);
            $lockedSession = ActivitySession::query()->lockForUpdate()->findOrFail($session->id);

            $this->assertSessionBelongsToActivity($lockedActivity, $lockedSession);

            if (!$lockedSession->attendance_locked) {
                throw ValidationException::withMessages([
                    'attendance' => 'This session attendance is already open for editing.',
                ]);
            }

            $before = $this->sessionSnapshot($lockedSession);
            $lockedSession->attendance_locked = false;
            $lockedSession->save();

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'attendance_reopened',
                $before,
                $this->sessionSnapshot($lockedSession->fresh()),
                'Activity session attendance reopened.'
            );

            return $lockedSession->fresh(['schedule', 'creator']);
        });
    }

    public function attendanceSummary(ActivitySession $session): array
    {
        $counts = $session->attendances()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'marked_count' => (int) $counts->sum(),
            'status_counts' => ActivitySessionAttendance::statuses(),
            'present_count' => (int) ($counts[ActivitySessionAttendance::STATUS_PRESENT] ?? 0),
            'absent_count' => (int) ($counts[ActivitySessionAttendance::STATUS_ABSENT] ?? 0),
            'excused_count' => (int) ($counts[ActivitySessionAttendance::STATUS_EXCUSED] ?? 0),
            'late_count' => (int) ($counts[ActivitySessionAttendance::STATUS_LATE] ?? 0),
            'injured_count' => (int) ($counts[ActivitySessionAttendance::STATUS_INJURED] ?? 0),
        ];
    }

    private function attendanceEnrollmentRelations(): array
    {
        return [
            'student:id,first_name,last_name,status',
            'gradeSnapshot:id,name',
            'klassSnapshot:id,name,grade_id',
            'houseSnapshot:id,name',
        ];
    }

    private function assertAttendanceWritable(Activity $activity, ActivitySession $session): void
    {
        if ($activity->status === Activity::STATUS_ARCHIVED) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance cannot be changed for archived activities.',
            ]);
        }

        if ($session->status === ActivitySession::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance cannot be recorded for a cancelled session.',
            ]);
        }

        if ($session->attendance_locked) {
            throw ValidationException::withMessages([
                'attendance' => 'Attendance is locked for this session. Reopen it before making corrections.',
            ]);
        }
    }

    private function assertSessionBelongsToActivity(Activity $activity, ActivitySession $session): void
    {
        if ($session->activity_id !== $activity->id) {
            throw ValidationException::withMessages([
                'session' => 'The selected session does not belong to this activity.',
            ]);
        }
    }

    private function sessionSnapshot(ActivitySession $session): array
    {
        return [
            'id' => $session->id,
            'session_date' => optional($session->session_date)->toDateString(),
            'status' => $session->status,
            'attendance_locked' => (bool) $session->attendance_locked,
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
