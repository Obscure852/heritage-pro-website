<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityAuditLog;
use App\Models\Activities\ActivityEnrollment;
use App\Models\Activities\ActivityEvent;
use App\Models\Activities\ActivityResult;
use App\Models\House;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityResultService
{
    public function __construct(private readonly ActivitySettingsService $activitySettingsService)
    {
    }

    public function eligibleStudentParticipants(Activity $activity, ActivityEvent $event): Collection
    {
        $this->assertEventBelongsToActivity($activity, $event);

        $eventMoment = $event->start_datetime instanceof Carbon
            ? $event->start_datetime->copy()
            : Carbon::parse($event->start_datetime);

        return $activity->enrollments()
            ->with([
                'student:id,first_name,last_name,status',
                'gradeSnapshot:id,name',
                'klassSnapshot:id,name',
                'houseSnapshot:id,name',
            ])
            ->where(function ($query) use ($eventMoment) {
                $query->whereNull('joined_at')
                    ->orWhere('joined_at', '<=', $eventMoment);
            })
            ->where(function ($query) use ($eventMoment) {
                $query->whereNull('left_at')
                    ->orWhere('left_at', '>=', $eventMoment);
            })
            ->get()
            ->sortBy(fn (ActivityEnrollment $enrollment) => strtolower((string) ($enrollment->student?->full_name ?? '')))
            ->values();
    }

    public function availableHouses(Activity $activity): Collection
    {
        return House::query()
            ->where('term_id', $activity->term_id)
            ->where('year', $activity->year)
            ->orderBy('name')
            ->get();
    }

    public function existingResultsMap(ActivityEvent $event, string $scope): Collection
    {
        return $event->results()
            ->where('participant_type', $scope)
            ->get()
            ->keyBy('participant_id');
    }

    public function resultsSummary(ActivityEvent $event): array
    {
        $results = $event->relationLoaded('results')
            ? $event->results
            : $event->results()->get();

        return [
            'total_results' => $results->count(),
            'student_results' => $results->where('participant_type', ActivityResult::PARTICIPANT_STUDENT)->count(),
            'house_results' => $results->where('participant_type', ActivityResult::PARTICIPANT_HOUSE)->count(),
            'award_count' => $results->filter(fn (ActivityResult $result) => filled($result->award_name))->count(),
            'points_total' => (int) $results->sum(fn (ActivityResult $result) => (int) ($result->points ?? 0)),
            'placed_count' => $results->filter(fn (ActivityResult $result) => !is_null($result->placement))->count(),
        ];
    }

    public function activityOutputsSummary(Activity $activity): array
    {
        $resultRows = ActivityResult::query()
            ->join('activity_events', 'activity_events.id', '=', 'activity_results.activity_event_id')
            ->where('activity_events.activity_id', $activity->id);

        return [
            'total_results' => (clone $resultRows)->count(),
            'award_count' => (clone $resultRows)->whereNotNull('activity_results.award_name')->count(),
            'points_total' => (int) ((clone $resultRows)->sum('activity_results.points') ?: 0),
            'house_results' => (clone $resultRows)->where('activity_results.participant_type', ActivityResult::PARTICIPANT_HOUSE)->count(),
        ];
    }

    public function groupedResults(ActivityEvent $event): array
    {
        $results = $event->relationLoaded('results')
            ? $event->results->loadMissing('recordedBy')
            : $event->results()->with('recordedBy')->get();

        $studentMap = Student::query()
            ->whereIn('id', $results->where('participant_type', ActivityResult::PARTICIPANT_STUDENT)->pluck('participant_id')->all())
            ->get(['id', 'first_name', 'last_name'])
            ->keyBy('id');

        $houseMap = House::query()
            ->whereIn('id', $results->where('participant_type', ActivityResult::PARTICIPANT_HOUSE)->pluck('participant_id')->all())
            ->get(['id', 'name'])
            ->keyBy('id');

        $decorate = function (Collection $collection, string $scope) use ($studentMap, $houseMap) {
            return $collection
                ->sort(function (ActivityResult $left, ActivityResult $right) {
                    $leftPlacement = $left->placement ?? PHP_INT_MAX;
                    $rightPlacement = $right->placement ?? PHP_INT_MAX;

                    if ($leftPlacement !== $rightPlacement) {
                        return $leftPlacement <=> $rightPlacement;
                    }

                    $leftPoints = (int) ($left->points ?? 0);
                    $rightPoints = (int) ($right->points ?? 0);

                    if ($leftPoints !== $rightPoints) {
                        return $rightPoints <=> $leftPoints;
                    }

                    return strcasecmp((string) ($left->result_label ?? ''), (string) ($right->result_label ?? ''));
                })
                ->values()
                ->map(function (ActivityResult $result) use ($scope, $studentMap, $houseMap) {
                    $name = $scope === ActivityResult::PARTICIPANT_STUDENT
                        ? $studentMap->get($result->participant_id)?->full_name
                        : $houseMap->get($result->participant_id)?->name;

                    $result->setAttribute('participant_name', $name ?: 'Unknown participant');

                    return $result;
                });
        };

        return [
            ActivityResult::PARTICIPANT_STUDENT => $decorate($results->where('participant_type', ActivityResult::PARTICIPANT_STUDENT), ActivityResult::PARTICIPANT_STUDENT),
            ActivityResult::PARTICIPANT_HOUSE => $decorate($results->where('participant_type', ActivityResult::PARTICIPANT_HOUSE), ActivityResult::PARTICIPANT_HOUSE),
        ];
    }

    public function syncResults(Activity $activity, ActivityEvent $event, array $data, User $actor): array
    {
        $currentActivity = $activity->fresh() ?? $activity;
        $currentEvent = $event->fresh() ?? $event;

        $this->assertEventBelongsToActivity($currentActivity, $currentEvent);
        $this->assertResultsWritable($currentActivity, $currentEvent);

        if (
            ($data['scope'] ?? null) === ActivityResult::PARTICIPANT_HOUSE
            && (!$currentActivity->allow_house_linkage || !$currentEvent->house_linked)
        ) {
            throw ValidationException::withMessages([
                'scope' => 'House results are only available for house-linked events under activities that allow house-linked reporting.',
            ]);
        }

        return DB::transaction(function () use ($activity, $event, $data, $actor) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);
            $lockedEvent = ActivityEvent::query()->lockForUpdate()->findOrFail($event->id);

            $this->assertEventBelongsToActivity($lockedActivity, $lockedEvent);
            $this->assertResultsWritable($lockedActivity, $lockedEvent);

            $scope = $data['scope'];
            $validParticipants = $scope === ActivityResult::PARTICIPANT_STUDENT
                ? $this->eligibleStudentParticipants($lockedActivity, $lockedEvent)->keyBy('student_id')
                : $this->availableHouses($lockedActivity)->keyBy('id');

            if (
                $scope === ActivityResult::PARTICIPANT_HOUSE
                && (!$lockedActivity->allow_house_linkage || !$lockedEvent->house_linked)
            ) {
                throw ValidationException::withMessages([
                    'scope' => 'House results are only available for house-linked events under activities that allow house-linked reporting.',
                ]);
            }

            $rows = collect($data['results'] ?? []);
            $selectedParticipantIds = [];

            foreach ($rows as $participantId => $row) {
                $participantId = (int) $participantId;

                if (!$this->rowSelected($row)) {
                    continue;
                }

                if (!$validParticipants->has($participantId)) {
                    throw ValidationException::withMessages([
                        'results' => 'Results can only be recorded for valid participants attached to this activity and event.',
                    ]);
                }

                if (!$this->hasResultContent($row)) {
                    throw ValidationException::withMessages([
                        'results' => 'Select at least one outcome field before saving a checked result row.',
                    ]);
                }

                $selectedParticipantIds[] = $participantId;

                ActivityResult::query()->updateOrCreate(
                    [
                        'activity_event_id' => $lockedEvent->id,
                        'participant_type' => $scope,
                        'participant_id' => $participantId,
                    ],
                    [
                        'metric_type' => $this->resolveMetricType($row),
                        'score_value' => $this->nullableDecimal($row['score_value'] ?? null),
                        'placement' => $this->nullableInteger($row['placement'] ?? null),
                        'points' => $this->nullableInteger($row['points'] ?? null),
                        'award_name' => $this->nullableString($row['award_name'] ?? null),
                        'result_label' => $this->nullableString($row['result_label'] ?? null),
                        'notes' => $this->nullableString($row['notes'] ?? null),
                        'recorded_by' => $actor->id,
                    ]
                );
            }

            $deleteQuery = ActivityResult::query()
                ->where('activity_event_id', $lockedEvent->id)
                ->where('participant_type', $scope);

            if (!empty($selectedParticipantIds)) {
                $deleteQuery->whereNotIn('participant_id', $selectedParticipantIds);
            }

            $deletedCount = $deleteQuery->delete();

            $this->recordAudit(
                $actor,
                $lockedActivity,
                'results_synced',
                null,
                [
                    'event_id' => $lockedEvent->id,
                    'scope' => $scope,
                    'saved_count' => count($selectedParticipantIds),
                    'deleted_count' => $deletedCount,
                ],
                'Activity results synced.'
            );

            return [
                'scope' => $scope,
                'saved_count' => count($selectedParticipantIds),
                'deleted_count' => $deletedCount,
            ];
        });
    }

    private function assertEventBelongsToActivity(Activity $activity, ActivityEvent $event): void
    {
        if ($event->activity_id !== $activity->id) {
            throw ValidationException::withMessages([
                'event' => 'The selected event does not belong to this activity.',
            ]);
        }
    }

    private function assertResultsWritable(Activity $activity, ActivityEvent $event): void
    {
        if ($activity->status === Activity::STATUS_ARCHIVED) {
            throw ValidationException::withMessages([
                'activity' => 'Results cannot be changed for archived activities.',
            ]);
        }

        if (!$this->activitySettingsService->resultModeAllowsResults($activity->result_mode)) {
            throw ValidationException::withMessages([
                'result_mode' => 'This activity is configured for attendance-only tracking and cannot accept event results.',
            ]);
        }

        if ($event->status !== ActivityEvent::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'event' => 'Mark the event as completed before recording results.',
            ]);
        }
    }

    private function rowSelected(array $row): bool
    {
        return (bool) ($row['selected'] ?? false);
    }

    private function hasResultContent(array $row): bool
    {
        return filled($row['result_label'] ?? null)
            || filled($row['placement'] ?? null)
            || filled($row['points'] ?? null)
            || filled($row['award_name'] ?? null)
            || filled($row['score_value'] ?? null);
    }

    private function resolveMetricType(array $row): string
    {
        $filledMetrics = collect([
            ActivityResult::METRIC_LABEL => filled($row['result_label'] ?? null),
            ActivityResult::METRIC_PLACEMENT => filled($row['placement'] ?? null),
            ActivityResult::METRIC_POINTS => filled($row['points'] ?? null),
            ActivityResult::METRIC_AWARD => filled($row['award_name'] ?? null),
            ActivityResult::METRIC_SCORE => filled($row['score_value'] ?? null),
        ])->filter();

        if ($filledMetrics->count() > 1) {
            return ActivityResult::METRIC_MIXED;
        }

        return $filledMetrics->keys()->first() ?? ActivityResult::METRIC_LABEL;
    }

    private function nullableInteger(mixed $value): ?int
    {
        return filled($value) ? (int) $value : null;
    }

    private function nullableDecimal(mixed $value): ?float
    {
        return filled($value) ? (float) $value : null;
    }

    private function nullableString(mixed $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
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
