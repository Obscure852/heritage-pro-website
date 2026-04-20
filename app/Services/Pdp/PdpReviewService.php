<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanSectionEntry;
use App\Models\Pdp\PdpPlanReview;
use App\Models\Pdp\PdpPlanSignature;
use App\Models\Pdp\PdpTemplateField;
use App\Models\Pdp\PdpTemplateSection;
use App\Models\User;
use Illuminate\Support\Carbon;
use RuntimeException;

class PdpReviewService
{
    public function __construct(
        private readonly PdpAccessService $accessService,
        private readonly PdpScoringService $scoringService
    ) {
    }

    public function openReview(PdpPlan $plan, string $periodKey, User $actor): PdpPlanReview
    {
        $plan = $this->loadPlan($plan);

        if (!$this->canManageReview($plan, $actor)) {
            abort(403);
        }

        $review = $plan->reviews->firstWhere('period_key', $periodKey);
        if (!$review) {
            throw new RuntimeException('Unknown PDP review period.');
        }

        if ($review->status !== PdpPlanReview::STATUS_PENDING) {
            throw new RuntimeException('Only pending PDP reviews can be opened.');
        }

        if ($plan->reviews->contains(fn (PdpPlanReview $existing): bool => $existing->status === PdpPlanReview::STATUS_OPEN)) {
            throw new RuntimeException('Close the currently open PDP review before opening another one.');
        }

        $this->assertPeriodWindowAllows($plan, $periodKey, 'open');

        $review->fill([
            'status' => PdpPlanReview::STATUS_OPEN,
            'opened_at' => now(),
        ])->save();

        $plan->fill([
            'status' => PdpPlan::STATUS_ACTIVE,
            'current_period_key' => $periodKey,
        ])->save();

        return $review->fresh();
    }

    public function closeReview(PdpPlan $plan, string $periodKey, User $actor, ?string $narrativeSummary = null): PdpPlanReview
    {
        $plan = $this->loadPlan($plan);

        if (!$this->canManageReview($plan, $actor)) {
            abort(403);
        }

        $review = $plan->reviews->firstWhere('period_key', $periodKey);
        if (!$review) {
            throw new RuntimeException('Unknown PDP review period.');
        }

        if ($review->status !== PdpPlanReview::STATUS_OPEN) {
            throw new RuntimeException('Only open PDP reviews can be closed.');
        }

        $this->assertPeriodWindowAllows($plan, $periodKey, 'close');

        $scoreSummary = $this->scoringService->calculateReviewSummary($plan, $review);
        $review->fill([
            'status' => PdpPlanReview::STATUS_CLOSED,
            'closed_at' => now(),
            'score_summary_json' => $scoreSummary,
            'narrative_summary' => $narrativeSummary,
        ])->save();

        $planSummary = $this->scoringService->calculatePlanSummary($plan->fresh(['template.periods', 'reviews']));
        $nextPendingReview = $plan->fresh('reviews')->reviews->firstWhere('status', PdpPlanReview::STATUS_PENDING);
        $hasPendingPlanLevelSignatures = $plan->fresh('signatures')->signatures
            ->whereNull('pdp_plan_review_id')
            ->where('status', PdpPlanSignature::STATUS_PENDING)
            ->isNotEmpty();

        $plan->fill([
            'status' => ($nextPendingReview || $hasPendingPlanLevelSignatures) ? PdpPlan::STATUS_ACTIVE : PdpPlan::STATUS_COMPLETED,
            'current_period_key' => $nextPendingReview?->period_key ?? $periodKey,
            'calculated_summary_json' => $planSummary['summary'],
        ])->save();

        return $review->fresh();
    }

    public function signSignature(PdpPlan $plan, PdpPlanSignature $signature, User $actor, ?string $comment = null): PdpPlanSignature
    {
        $plan = $this->loadPlan($plan);
        $signature = $plan->signatures->firstWhere('id', $signature->id);

        if (!$signature) {
            throw new RuntimeException('Unknown PDP signature step.');
        }

        if (!$this->accessService->matchesRoleType($plan, $actor, $signature->role_type)) {
            abort(403);
        }

        if ($signature->status !== PdpPlanSignature::STATUS_PENDING) {
            throw new RuntimeException('This PDP signature step has already been resolved.');
        }

        if ($signature->review && $signature->review->status !== PdpPlanReview::STATUS_CLOSED) {
            throw new RuntimeException('The related PDP review must be closed before signing.');
        }

        if ($signature->pdp_plan_review_id === null && $plan->reviews->contains(fn (PdpPlanReview $review): bool => $review->status !== PdpPlanReview::STATUS_CLOSED)) {
            throw new RuntimeException('All PDP reviews must be closed before plan-level sign-off can begin.');
        }

        $step = $plan->template->approvalSteps->firstWhere('key', $signature->approval_step_key);
        if (!$step) {
            throw new RuntimeException('Unknown PDP approval step configuration.');
        }

        if ($step->comment_required && blank($comment)) {
            throw new RuntimeException('A comment is required before this PDP step can be signed.');
        }

        $this->assertPriorRequiredStepsAreSigned($plan, $signature, $step->sequence);

        $signature->fill([
            'signer_user_id' => $actor->id,
            'signed_at' => now(),
            'comment' => $comment,
            'status' => PdpPlanSignature::STATUS_SIGNED,
        ])->save();

        if ($signature->pdp_plan_review_id === null) {
            $remainingRequiredPlanLevelSteps = $plan->fresh('signatures')->signatures
                ->whereNull('pdp_plan_review_id')
                ->filter(function (PdpPlanSignature $candidate) use ($plan): bool {
                    $step = $plan->template->approvalSteps->firstWhere('key', $candidate->approval_step_key);

                    return ($step?->required ?? false) && $candidate->status !== PdpPlanSignature::STATUS_SIGNED;
                });

            if ($remainingRequiredPlanLevelSteps->isEmpty()) {
                $plan->update(['status' => PdpPlan::STATUS_COMPLETED]);
            }
        }

        return $signature->fresh('signer');
    }

    public function canManageReview(PdpPlan $plan, User $actor): bool
    {
        return $this->accessService->canManageReview($plan, $actor);
    }

    public function canSignSignature(PdpPlan $plan, PdpPlanSignature $signature, User $actor): bool
    {
        if ($signature->status !== PdpPlanSignature::STATUS_PENDING) {
            return false;
        }

        if (!$this->accessService->matchesRoleType($plan, $actor, $signature->role_type)) {
            return false;
        }

        if ($signature->review && $signature->review->status !== PdpPlanReview::STATUS_CLOSED) {
            return false;
        }

        if ($signature->pdp_plan_review_id === null && $plan->reviews->contains(fn (PdpPlanReview $review): bool => $review->status !== PdpPlanReview::STATUS_CLOSED)) {
            return false;
        }

        $step = $plan->template->approvalSteps->firstWhere('key', $signature->approval_step_key);
        if (!$step) {
            return false;
        }

        $priorRequiredStepKeys = $plan->template->approvalSteps
            ->where('sequence', '<', $step->sequence)
            ->where('required', true)
            ->pluck('key')
            ->all();

        return !$plan->signatures->contains(function (PdpPlanSignature $candidate) use ($signature, $priorRequiredStepKeys): bool {
            return $candidate->id !== $signature->id
                && in_array($candidate->approval_step_key, $priorRequiredStepKeys, true)
                && $candidate->pdp_plan_review_id === $signature->pdp_plan_review_id
                && $candidate->status !== PdpPlanSignature::STATUS_SIGNED;
        });
    }

    public function editableFieldKeys(PdpPlan $plan, PdpTemplateSection $section, User $actor): array
    {
        return $this->editableFieldKeysForEntry($plan, $section, null, $actor);
    }

    public function editableFieldKeysForEntry(
        PdpPlan $plan,
        PdpTemplateSection $section,
        ?PdpPlanSectionEntry $entry,
        User $actor
    ): array
    {
        $plan = $this->loadPlan($plan);

        return $section->fields
            ->whereNull('parent_field_id')
            ->filter(fn (PdpTemplateField $field): bool => $this->accessService->canEditField(
                $plan,
                $field,
                $actor,
                $field->period_scope ? $plan->reviews->firstWhere('period_key', $field->period_scope) : null,
                $entry
            ))
            ->pluck('key')
            ->all();
    }

    public function canManageSectionEntries(PdpPlan $plan, PdpTemplateSection $section, User $actor): bool
    {
        if (!$section->is_repeatable) {
            return false;
        }

        if (!$section->usesTemplateRows()) {
            return $this->editableFieldKeys($plan, $section, $actor) !== [];
        }

        if (!$section->allowsCustomEntries()) {
            return false;
        }

        return $this->editableStructuralFieldKeys($section, $actor, $plan) !== [];
    }

    public function canDeleteSectionEntry(PdpPlan $plan, PdpTemplateSection $section, PdpPlanSectionEntry $entry, User $actor): bool
    {
        if (!$section->is_repeatable) {
            return false;
        }

        if ($entry->origin_type === PdpPlanSectionEntry::ORIGIN_TEMPLATE_SNAPSHOT) {
            return false;
        }

        if ($section->usesTemplateRows()) {
            return $this->editableStructuralFieldKeys($section, $actor, $plan) !== [];
        }

        return $this->editableFieldKeysForEntry($plan, $section, $entry, $actor) !== [];
    }

    private function editableStructuralFieldKeys(PdpTemplateSection $section, User $actor, PdpPlan $plan): array
    {
        $managedKeys = $section->templateManagedFieldKeys();

        if ($managedKeys === []) {
            return $this->editableFieldKeys($plan, $section, $actor);
        }

        return array_values(array_intersect(
            $managedKeys,
            $this->editableFieldKeys($plan, $section, $actor)
        ));
    }

    private function assertPriorRequiredStepsAreSigned(PdpPlan $plan, PdpPlanSignature $signature, int $currentSequence): void
    {
        $priorRequiredStepKeys = $plan->template->approvalSteps
            ->where('sequence', '<', $currentSequence)
            ->where('required', true)
            ->pluck('key')
            ->all();

        $pendingPriorSignature = $plan->signatures
            ->filter(function (PdpPlanSignature $candidate) use ($signature, $priorRequiredStepKeys): bool {
                return $candidate->id !== $signature->id
                    && $candidate->approval_step_key !== null
                    && in_array($candidate->approval_step_key, $priorRequiredStepKeys, true)
                    && $candidate->pdp_plan_review_id === $signature->pdp_plan_review_id
                    && $candidate->status !== PdpPlanSignature::STATUS_SIGNED;
            })
            ->first();

        if ($pendingPriorSignature) {
            throw new RuntimeException('Complete the prior required PDP sign-off steps first.');
        }
    }

    private function assertPeriodWindowAllows(PdpPlan $plan, string $periodKey, string $action): void
    {
        $period = $plan->template->periods->firstWhere('key', $periodKey);
        $rules = $action === 'open' ? $period?->open_rule_json : $period?->close_rule_json;

        if (!is_array($rules) || $rules === []) {
            return;
        }

        $now = now();
        if (isset($rules['starts_at']) && $now->lt(Carbon::parse($rules['starts_at']))) {
            throw new RuntimeException("This PDP {$action} window has not opened yet.");
        }

        if (isset($rules['ends_at']) && $now->gt(Carbon::parse($rules['ends_at']))) {
            throw new RuntimeException("This PDP {$action} window has already closed.");
        }

        if (isset($rules['start_offset_days'])) {
            $boundary = $plan->plan_period_start->copy()->addDays((int) $rules['start_offset_days']);
            if ($now->lt($boundary)) {
                throw new RuntimeException("This PDP {$action} window has not opened yet.");
            }
        }

        if (isset($rules['end_offset_days'])) {
            $boundary = $plan->plan_period_end->copy()->subDays((int) $rules['end_offset_days']);
            if ($now->gt($boundary)) {
                throw new RuntimeException("This PDP {$action} window has already closed.");
            }
        }
    }

    private function loadPlan(PdpPlan $plan): PdpPlan
    {
        return $plan->fresh([
            'template.sections.fields',
            'template.periods',
            'template.ratingSchemes',
            'template.approvalSteps',
            'reviews',
            'signatures.review',
        ]);
    }
}
