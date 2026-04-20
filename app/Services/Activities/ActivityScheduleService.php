<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityAuditLog;
use App\Models\Activities\ActivitySchedule;
use App\Models\Activities\ActivitySession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityScheduleService
{
    public function createSchedule(Activity $activity, array $data, User $actor): ActivitySchedule
    {
        return DB::transaction(function () use ($activity, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);

            $this->assertPlanningChangesAllowed($lockedActivity);

            $schedule = $lockedActivity->schedules()->create([
                ...$this->extractSchedulePayload($data),
                'active' => (bool) ($data['active'] ?? true),
            ]);

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'schedule_created',
                null,
                $this->scheduleSnapshot($schedule),
                'Activity schedule created.'
            );

            return $schedule->fresh()->loadCount('sessions');
        });
    }

    public function updateSchedule(Activity $activity, ActivitySchedule $schedule, array $data, User $actor): ActivitySchedule
    {
        return DB::transaction(function () use ($activity, $schedule, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);
            $lockedSchedule = ActivitySchedule::query()->lockForUpdate()->findOrFail($schedule->id);

            $this->assertPlanningChangesAllowed($lockedActivity);
            $this->assertScheduleBelongsToActivity($lockedActivity, $lockedSchedule);

            $before = $this->scheduleSnapshot($lockedSchedule);

            $lockedSchedule->fill([
                ...$this->extractSchedulePayload($data),
                'active' => array_key_exists('active', $data)
                    ? (bool) $data['active']
                    : $lockedSchedule->active,
            ]);
            $lockedSchedule->save();

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'schedule_updated',
                $before,
                $this->scheduleSnapshot($lockedSchedule->fresh()),
                'Activity schedule updated.'
            );

            return $lockedSchedule->fresh()->loadCount('sessions');
        });
    }

    public function generateSessions(Activity $activity, ActivitySchedule $schedule, array $data, User $actor): array
    {
        return DB::transaction(function () use ($activity, $schedule, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);
            $lockedSchedule = ActivitySchedule::query()->lockForUpdate()->findOrFail($schedule->id);

            $this->assertPlanningChangesAllowed($lockedActivity);
            $this->assertScheduleBelongsToActivity($lockedActivity, $lockedSchedule);

            if (!$lockedSchedule->active) {
                throw ValidationException::withMessages([
                    'schedule' => 'Only active schedules can generate sessions.',
                ]);
            }

            $window = $this->resolveGenerationWindow($lockedSchedule, $data);
            $candidateDates = $this->buildSessionDates($lockedSchedule, $window['from'], $window['to']);

            if (empty($candidateDates)) {
                throw ValidationException::withMessages([
                    'generate_from' => 'No matching session dates exist in the selected generation range.',
                ]);
            }

            $existingDates = ActivitySession::query()
                ->where('activity_id', $lockedActivity->id)
                ->whereIn('session_date', array_map(fn (Carbon $date) => $date->toDateString(), $candidateDates))
                ->pluck('session_date')
                ->map(fn ($value) => Carbon::parse($value)->toDateString())
                ->all();

            $payload = collect($candidateDates)
                ->reject(fn (Carbon $date) => in_array($date->toDateString(), $existingDates, true))
                ->map(fn (Carbon $date) => $this->generatedSessionPayload($lockedActivity, $lockedSchedule, $date, $actor))
                ->values();

            if ($payload->isNotEmpty()) {
                ActivitySession::query()->insert($payload->all());
            }

            $result = [
                'created_count' => $payload->count(),
                'skipped_count' => count($candidateDates) - $payload->count(),
                'window_start' => $window['from']->toDateString(),
                'window_end' => $window['to']->toDateString(),
            ];

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'sessions_generated',
                null,
                [
                    ...$result,
                    'schedule_id' => $lockedSchedule->id,
                ],
                'Activity sessions generated from recurring schedule.'
            );

            return $result;
        });
    }

    public function createSession(Activity $activity, array $data, User $actor): ActivitySession
    {
        return DB::transaction(function () use ($activity, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);

            $this->assertPlanningChangesAllowed($lockedActivity);
            $schedule = $this->resolveSchedule($lockedActivity, $data['activity_schedule_id'] ?? null);

            $session = $lockedActivity->sessions()->create([
                ...$this->extractSessionPayload($lockedActivity, $data),
                'activity_schedule_id' => $schedule?->id,
                'created_by' => $actor->id,
            ]);

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'session_created',
                null,
                $this->sessionSnapshot($session->fresh(['schedule', 'creator'])),
                'Activity session created.'
            );

            return $session->fresh(['schedule', 'creator']);
        });
    }

    public function updateSession(Activity $activity, ActivitySession $session, array $data, User $actor): ActivitySession
    {
        return DB::transaction(function () use ($activity, $session, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);
            $lockedSession = ActivitySession::query()->lockForUpdate()->findOrFail($session->id);

            $this->assertPlanningChangesAllowed($lockedActivity);
            $this->assertSessionBelongsToActivity($lockedActivity, $lockedSession);
            $schedule = $this->resolveSchedule($lockedActivity, $data['activity_schedule_id'] ?? null);

            $before = $this->sessionSnapshot($lockedSession->fresh(['schedule', 'creator']));

            $lockedSession->fill([
                ...$this->extractSessionPayload($lockedActivity, $data),
                'activity_schedule_id' => $schedule?->id,
            ]);
            $lockedSession->save();

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'session_updated',
                $before,
                $this->sessionSnapshot($lockedSession->fresh(['schedule', 'creator'])),
                'Activity session updated.'
            );

            return $lockedSession->fresh(['schedule', 'creator']);
        });
    }

    private function extractSchedulePayload(array $data): array
    {
        return [
            'frequency' => $data['frequency'],
            'day_of_week' => (int) $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'start_date' => Carbon::parse($data['start_date'])->toDateString(),
            'end_date' => !empty($data['end_date']) ? Carbon::parse($data['end_date'])->toDateString() : null,
            'location' => $data['location'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function extractSessionPayload(Activity $activity, array $data): array
    {
        $sessionDate = Carbon::parse($data['session_date'])->toDateString();
        $startDateTime = Carbon::parse($sessionDate . ' ' . $data['start_time']);
        $endDateTime = !empty($data['end_time'])
            ? Carbon::parse($sessionDate . ' ' . $data['end_time'])
            : null;

        return [
            'session_type' => $data['session_type'],
            'session_date' => $sessionDate,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'location' => $data['location'] ?? $activity->default_location,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function resolveGenerationWindow(ActivitySchedule $schedule, array $data): array
    {
        $from = Carbon::parse($data['generate_from'])->startOfDay();
        $to = Carbon::parse($data['generate_to'])->endOfDay();
        $scheduleStart = $schedule->start_date->copy()->startOfDay();
        $scheduleEnd = $schedule->end_date?->copy()->endOfDay();

        if ($from->lt($scheduleStart)) {
            $from = $scheduleStart;
        }

        if ($scheduleEnd && $to->gt($scheduleEnd)) {
            $to = $scheduleEnd;
        }

        if ($to->lt($from)) {
            throw ValidationException::withMessages([
                'generate_to' => 'The generation range falls outside this schedule window.',
            ]);
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @return array<int, \Carbon\Carbon>
     */
    private function buildSessionDates(ActivitySchedule $schedule, Carbon $from, Carbon $to): array
    {
        $intervalWeeks = $schedule->frequency === ActivitySchedule::FREQUENCY_BIWEEKLY ? 2 : 1;
        $cursor = $schedule->start_date->copy()->startOfDay();

        while ((int) $cursor->dayOfWeekIso !== (int) $schedule->day_of_week) {
            $cursor->addDay();
        }

        while ($cursor->lt($from)) {
            $cursor->addWeeks($intervalWeeks);
        }

        $dates = [];

        while ($cursor->lte($to)) {
            $dates[] = $cursor->copy();
            $cursor->addWeeks($intervalWeeks);
        }

        return $dates;
    }

    private function generatedSessionPayload(Activity $activity, ActivitySchedule $schedule, Carbon $date, User $actor): array
    {
        $startDateTime = Carbon::parse($date->toDateString() . ' ' . $schedule->start_time);
        $endDateTime = Carbon::parse($date->toDateString() . ' ' . $schedule->end_time);
        $timestamp = now();

        return [
            'activity_id' => $activity->id,
            'activity_schedule_id' => $schedule->id,
            'session_type' => ActivitySession::TYPE_SCHEDULED,
            'session_date' => $date->toDateString(),
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'location' => $schedule->location ?: $activity->default_location,
            'status' => ActivitySession::STATUS_PLANNED,
            'attendance_locked' => false,
            'notes' => $schedule->notes,
            'created_by' => $actor->id,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    private function resolveSchedule(Activity $activity, mixed $scheduleId): ?ActivitySchedule
    {
        if (empty($scheduleId)) {
            return null;
        }

        $schedule = ActivitySchedule::query()->find($scheduleId);

        if (!$schedule || $schedule->activity_id !== $activity->id) {
            throw ValidationException::withMessages([
                'activity_schedule_id' => 'Select a schedule that belongs to this activity.',
            ]);
        }

        return $schedule;
    }

    private function assertPlanningChangesAllowed(Activity $activity): void
    {
        if (in_array($activity->status, [Activity::STATUS_CLOSED, Activity::STATUS_ARCHIVED], true)) {
            throw ValidationException::withMessages([
                'activity' => 'Schedules and sessions cannot be changed once an activity is closed or archived.',
            ]);
        }
    }

    private function assertScheduleBelongsToActivity(Activity $activity, ActivitySchedule $schedule): void
    {
        if ($schedule->activity_id !== $activity->id) {
            throw ValidationException::withMessages([
                'schedule' => 'The selected schedule does not belong to this activity.',
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

    private function scheduleSnapshot(ActivitySchedule $schedule): array
    {
        return [
            'id' => $schedule->id,
            'frequency' => $schedule->frequency,
            'day_of_week' => $schedule->day_of_week,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'start_date' => optional($schedule->start_date)->toDateString(),
            'end_date' => optional($schedule->end_date)->toDateString(),
            'location' => $schedule->location,
            'active' => (bool) $schedule->active,
        ];
    }

    private function sessionSnapshot(ActivitySession $session): array
    {
        return [
            'id' => $session->id,
            'activity_schedule_id' => $session->activity_schedule_id,
            'session_type' => $session->session_type,
            'session_date' => optional($session->session_date)->toDateString(),
            'start_datetime' => optional($session->start_datetime)->toDateTimeString(),
            'end_datetime' => optional($session->end_datetime)->toDateTimeString(),
            'location' => $session->location,
            'status' => $session->status,
            'attendance_locked' => (bool) $session->attendance_locked,
            'notes' => $session->notes,
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
