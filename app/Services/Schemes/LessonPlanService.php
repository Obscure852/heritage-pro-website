<?php

namespace App\Services\Schemes;

use App\Models\Schemes\LessonPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LessonPlanService {

    /**
     * Teacher submits lesson plan for review.
     */
    public function submitPlan(LessonPlan $plan, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($plan, $actor, $comments): void {
            $fresh = LessonPlan::lockForUpdate()->findOrFail($plan->id);

            $allowed = ['draft', 'revision_required'];
            if (!in_array($fresh->status, $allowed, true)) {
                throw new InvalidArgumentException(
                    "Cannot submit lesson plan with status '{$fresh->status}'."
                );
            }

            $updates = ['status' => 'submitted'];
            if ($fresh->status === 'revision_required') {
                $updates['review_comments'] = null;
                $updates['supervisor_comments'] = null;
            }

            $fresh->update($updates);
        });
    }

    /**
     * Supervisor approves a submitted lesson plan.
     */
    public function supervisorApprove(LessonPlan $plan, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($plan, $actor, $comments): void {
            $fresh = LessonPlan::lockForUpdate()->findOrFail($plan->id);

            if ($fresh->status !== 'submitted') {
                throw new InvalidArgumentException(
                    "Cannot supervisor-approve lesson plan with status '{$fresh->status}'."
                );
            }

            $fresh->update([
                'status' => 'supervisor_reviewed',
                'supervisor_reviewed_by' => $actor->id,
                'supervisor_reviewed_at' => now(),
                'supervisor_comments' => $comments,
            ]);
        });
    }

    /**
     * Supervisor returns a submitted lesson plan for revision.
     */
    public function supervisorReturn(LessonPlan $plan, User $actor, string $comments): void {
        DB::transaction(function () use ($plan, $actor, $comments): void {
            $fresh = LessonPlan::lockForUpdate()->findOrFail($plan->id);

            if ($fresh->status !== 'submitted') {
                throw new InvalidArgumentException(
                    "Cannot return lesson plan for revision with status '{$fresh->status}'."
                );
            }

            $fresh->update([
                'status' => 'revision_required',
                'supervisor_comments' => $comments,
                'supervisor_reviewed_by' => $actor->id,
                'supervisor_reviewed_at' => now(),
            ]);
        });
    }

    /**
     * HOD approves a lesson plan (from submitted or supervisor_reviewed).
     */
    public function approvePlan(LessonPlan $plan, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($plan, $actor, $comments): void {
            $fresh = LessonPlan::lockForUpdate()->findOrFail($plan->id);

            $allowed = ['submitted', 'supervisor_reviewed'];
            if (!in_array($fresh->status, $allowed, true)) {
                throw new InvalidArgumentException(
                    "Cannot approve lesson plan with status '{$fresh->status}'."
                );
            }

            $fresh->update([
                'status' => 'approved',
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'review_comments' => $comments,
            ]);
        });
    }

    /**
     * HOD returns a lesson plan for revision.
     */
    public function returnForRevision(LessonPlan $plan, User $actor, string $comments): void {
        DB::transaction(function () use ($plan, $actor, $comments): void {
            $fresh = LessonPlan::lockForUpdate()->findOrFail($plan->id);

            $allowed = ['submitted', 'supervisor_reviewed'];
            if (!in_array($fresh->status, $allowed, true)) {
                throw new InvalidArgumentException(
                    "Cannot return lesson plan for revision with status '{$fresh->status}'."
                );
            }

            $fresh->update([
                'status' => 'revision_required',
                'review_comments' => $comments,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
            ]);
        });
    }
}
