<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityAuditLog;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityService
{
    public function __construct(private readonly ActivityOwnershipService $activityOwnershipService)
    {
    }

    public function create(array $data, User $user, ?Term $term): Activity
    {
        if (!$term) {
            throw ValidationException::withMessages([
                'term' => 'No selected term was found. Set the active term before creating activities.',
            ]);
        }

        return DB::transaction(function () use ($data, $user, $term) {
            $activity = Activity::create([
                ...$this->extractActivityPayload($data),
                'status' => Activity::STATUS_DRAFT,
                'term_id' => $term->id,
                'year' => $term->year,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->recordAudit(
                $user,
                $activity,
                'created',
                null,
                $activity->fresh()->toArray(),
                'Activity created.'
            );

            return $activity;
        });
    }

    public function update(Activity $activity, array $data, User $user): Activity
    {
        return DB::transaction(function () use ($activity, $data, $user) {
            $before = $activity->fresh()->toArray();

            $activity->fill($this->extractActivityPayload($data));
            $activity->updated_by = $user->id;
            $activity->save();

            $this->recordAudit(
                $user,
                $activity,
                'updated',
                $before,
                $activity->fresh()->toArray(),
                'Activity details updated.'
            );

            return $activity;
        });
    }

    public function transition(Activity $activity, string $targetStatus, User $user): Activity
    {
        $this->assertTransitionAllowed($activity, $targetStatus);

        return DB::transaction(function () use ($activity, $targetStatus, $user) {
            $lockedActivity = Activity::query()->lockForUpdate()->findOrFail($activity->id);

            if ($lockedActivity->status === $targetStatus) {
                return $lockedActivity;
            }

            $before = $lockedActivity->toArray();
            $lockedActivity->status = $targetStatus;
            $lockedActivity->updated_by = $user->id;
            $lockedActivity->save();

            $this->recordAudit(
                $user,
                $lockedActivity,
                'status_changed',
                $before,
                $lockedActivity->fresh()->toArray(),
                sprintf('Activity status changed from %s to %s.', $before['status'], $targetStatus)
            );

            return $lockedActivity;
        });
    }

    private function extractActivityPayload(array $data): array
    {
        return [
            'name' => $data['name'],
            'code' => strtoupper((string) $data['code']),
            'category' => $data['category'],
            'delivery_mode' => $data['delivery_mode'],
            'participation_mode' => $data['participation_mode'],
            'result_mode' => $data['result_mode'],
            'description' => $data['description'] ?? null,
            'default_location' => $data['default_location'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'gender_policy' => $data['gender_policy'] ?? null,
            'attendance_required' => (bool) ($data['attendance_required'] ?? false),
            'allow_house_linkage' => (bool) ($data['allow_house_linkage'] ?? false),
            'fee_type_id' => $data['fee_type_id'] ?? null,
            'default_fee_amount' => $data['default_fee_amount'] ?? null,
        ];
    }

    private function assertTransitionAllowed(Activity $activity, string $targetStatus): void
    {
        $allowedTransitions = [
            Activity::STATUS_DRAFT => [Activity::STATUS_ACTIVE, Activity::STATUS_ARCHIVED],
            Activity::STATUS_ACTIVE => [Activity::STATUS_PAUSED, Activity::STATUS_CLOSED, Activity::STATUS_ARCHIVED],
            Activity::STATUS_PAUSED => [Activity::STATUS_ACTIVE, Activity::STATUS_CLOSED, Activity::STATUS_ARCHIVED],
            Activity::STATUS_CLOSED => [Activity::STATUS_ARCHIVED],
            Activity::STATUS_ARCHIVED => [],
        ];

        $currentStatus = $activity->status;
        $allowedTargets = $allowedTransitions[$currentStatus] ?? [];

        if (!in_array($targetStatus, $allowedTargets, true)) {
            throw ValidationException::withMessages([
                'status' => sprintf('Cannot move activity from %s to %s.', $currentStatus, $targetStatus),
            ]);
        }

        if (
            $targetStatus === Activity::STATUS_ACTIVE
            && !$this->activityOwnershipService->hasPrimaryCoordinator($activity)
        ) {
            throw ValidationException::withMessages([
                'status' => 'Assign a primary coordinator before activating this activity.',
            ]);
        }
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
