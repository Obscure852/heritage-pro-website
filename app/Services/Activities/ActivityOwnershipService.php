<?php

namespace App\Services\Activities;

use App\Models\Activities\Activity;
use App\Models\Activities\ActivityAuditLog;
use App\Models\Activities\ActivityEligibilityTarget;
use App\Models\Activities\ActivityStaffAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityOwnershipService
{
    public function assignStaff(Activity $activity, array $data, User $actor): ActivityStaffAssignment
    {
        return DB::transaction(function () use ($activity, $data, $actor) {
            $this->assertPrimaryAssignmentIsValid($data);
            $this->assertNoDuplicateActiveAssignment($activity, $data);

            $before = $this->staffAssignmentsSnapshot($activity);

            if (!empty($data['is_primary'])) {
                $activity->staffAssignments()
                    ->active()
                    ->where('is_primary', true)
                    ->update(['is_primary' => false, 'updated_at' => now()]);
            }

            $assignment = $activity->staffAssignments()->create([
                'user_id' => $data['user_id'],
                'role' => $data['role'],
                'is_primary' => (bool) ($data['is_primary'] ?? false),
                'active' => true,
                'assigned_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->recordAudit(
                $actor,
                $activity,
                'staff_assigned',
                $before,
                $this->staffAssignmentsSnapshot($activity->fresh()),
                'Activity staff assignment added.'
            );

            return $assignment;
        });
    }

    public function retireStaffAssignment(Activity $activity, ActivityStaffAssignment $assignment, User $actor): ActivityStaffAssignment
    {
        return DB::transaction(function () use ($activity, $assignment, $actor) {
            if ($assignment->activity_id !== $activity->id) {
                throw ValidationException::withMessages([
                    'assignment' => 'The selected staff assignment does not belong to this activity.',
                ]);
            }

            if (!$assignment->active) {
                throw ValidationException::withMessages([
                    'assignment' => 'That staff assignment is already inactive.',
                ]);
            }

            if (
                $assignment->is_primary
                && $activity->status === Activity::STATUS_ACTIVE
                && !$activity->staffAssignments()
                    ->active()
                    ->where('is_primary', true)
                    ->whereKeyNot($assignment->id)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'assignment' => 'Assign a replacement primary coordinator before removing the current one from an active activity.',
                ]);
            }

            $before = $this->staffAssignmentsSnapshot($activity);

            $assignment->forceFill([
                'active' => false,
                'is_primary' => false,
                'removed_at' => now(),
            ])->save();

            $this->recordAudit(
                $actor,
                $activity,
                'staff_removed',
                $before,
                $this->staffAssignmentsSnapshot($activity->fresh()),
                'Activity staff assignment retired.'
            );

            return $assignment->fresh();
        });
    }

    public function syncEligibilityTargets(Activity $activity, array $data, User $actor): void
    {
        DB::transaction(function () use ($activity, $data, $actor) {
            $before = $this->eligibilityTargetsSnapshot($activity);

            $activity->eligibilityTargets()->delete();

            $payload = collect([
                ActivityEligibilityTarget::TARGET_GRADE => $data['grades'] ?? [],
                ActivityEligibilityTarget::TARGET_CLASS => $data['klasses'] ?? [],
                ActivityEligibilityTarget::TARGET_HOUSE => $data['houses'] ?? [],
                ActivityEligibilityTarget::TARGET_STUDENT_FILTER => $data['student_filters'] ?? [],
            ])->flatMap(function (array $targetIds, string $type) use ($activity) {
                return collect($targetIds)
                    ->map(fn (int $targetId) => [
                        'activity_id' => $activity->id,
                        'target_type' => $type,
                        'target_id' => $targetId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            })->values();

            if ($payload->isNotEmpty()) {
                ActivityEligibilityTarget::query()->insert($payload->all());
            }

            $this->recordAudit(
                $actor,
                $activity,
                'eligibility_updated',
                $before,
                $this->eligibilityTargetsSnapshot($activity->fresh()),
                'Activity eligibility targets updated.'
            );
        });
    }

    public function hasPrimaryCoordinator(Activity $activity): bool
    {
        return $activity->staffAssignments()
            ->active()
            ->where('role', ActivityStaffAssignment::ROLE_COORDINATOR)
            ->where('is_primary', true)
            ->exists();
    }

    private function assertNoDuplicateActiveAssignment(Activity $activity, array $data): void
    {
        $duplicateExists = $activity->staffAssignments()
            ->active()
            ->where('user_id', $data['user_id'])
            ->where('role', $data['role'])
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'user_id' => 'That staff member already has the selected active role on this activity.',
            ]);
        }
    }

    private function assertPrimaryAssignmentIsValid(array $data): void
    {
        if (!empty($data['is_primary']) && $data['role'] !== ActivityStaffAssignment::ROLE_COORDINATOR) {
            throw ValidationException::withMessages([
                'is_primary' => 'Only a coordinator can be marked as the primary lead.',
            ]);
        }
    }

    private function staffAssignmentsSnapshot(Activity $activity): array
    {
        return $activity->staffAssignments()
            ->with('user:id,firstname,lastname')
            ->orderByDesc('active')
            ->orderByDesc('is_primary')
            ->orderBy('role')
            ->orderBy('assigned_at')
            ->get()
            ->map(fn (ActivityStaffAssignment $assignment) => [
                'id' => $assignment->id,
                'user_id' => $assignment->user_id,
                'user_name' => $assignment->user?->full_name,
                'role' => $assignment->role,
                'is_primary' => $assignment->is_primary,
                'active' => $assignment->active,
                'assigned_at' => optional($assignment->assigned_at)->toDateTimeString(),
                'removed_at' => optional($assignment->removed_at)->toDateTimeString(),
            ])
            ->all();
    }

    private function eligibilityTargetsSnapshot(Activity $activity): array
    {
        return $activity->eligibilityTargets()
            ->orderBy('target_type')
            ->orderBy('target_id')
            ->get()
            ->map(fn (ActivityEligibilityTarget $target) => [
                'target_type' => $target->target_type,
                'target_id' => $target->target_id,
            ])
            ->all();
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
