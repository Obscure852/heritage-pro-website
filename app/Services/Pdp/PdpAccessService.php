<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpPlanSectionEntry;
use App\Models\Pdp\PdpPlanReview;
use App\Models\Pdp\PdpTemplateField;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PdpAccessService
{
    public function __construct(
        private readonly PdpSettingsService $settingsService
    ) {
    }

    public function canReadPlan(PdpPlan $plan, User $user): bool
    {
        return $this->hasElevatedAccess($user)
            || $plan->user_id === $user->id
            || $plan->supervisor_id === $user->id
            || $plan->created_by === $user->id;
    }

    public function canManagePlan(PdpPlan $plan, User $user): bool
    {
        return $this->canReadPlan($plan, $user);
    }

    public function canAdministerPlan(PdpPlan $plan, User $user): bool
    {
        return $this->hasElevatedAccess($user);
    }

    public function canManageTemplates(User $user): bool
    {
        return $this->hasElevatedAccess($user);
    }

    public function canManageRollouts(User $user): bool
    {
        return $this->hasElevatedAccess($user);
    }

    public function canCreateManualPlans(User $user): bool
    {
        return $this->hasElevatedAccess($user);
    }

    public function canViewReports(User $user): bool
    {
        return $this->hasElevatedAccess($user);
    }

    public function hasElevatedAccess(User $user): bool
    {
        if (in_array($user->position, $this->elevatedPositions(), true)) {
            return true;
        }

        if (!Schema::hasTable('roles') || !Schema::hasTable('role_users')) {
            return false;
        }

        return $user->hasAnyRoles($this->elevatedRoles());
    }

    public function matchesRoleType(PdpPlan $plan, User $user, string $roleType): bool
    {
        return match ($roleType) {
            'employee' => $plan->user_id === $user->id,
            'reporting_officer' => $plan->supervisor_id === $user->id,
            'authorized_official',
            'permanent_secretary',
            'hr_delegate' => $this->hasElevatedAccess($user),
            default => $this->hasElevatedAccess($user),
        };
    }

    public function canManageReview(PdpPlan $plan, User $user): bool
    {
        return $this->matchesRoleType($plan, $user, 'reporting_officer')
            || $this->matchesRoleType($plan, $user, 'authorized_official')
            || $this->hasElevatedAccess($user);
    }

    public function canEditField(
        PdpPlan $plan,
        PdpTemplateField $field,
        User $user,
        ?PdpPlanReview $review = null,
        ?PdpPlanSectionEntry $entry = null
    ): bool
    {
        if ($field->input_mode !== 'manual_entry' || !$this->canManagePlan($plan, $user)) {
            return false;
        }

        $section = $field->relationLoaded('section') ? $field->section : $field->section()->first();
        if (
            $entry
            && $section
            && $entry->origin_type === PdpPlanSectionEntry::ORIGIN_TEMPLATE_SNAPSHOT
            && in_array($field->key, $section->templateManagedFieldKeys(), true)
        ) {
            return false;
        }

        if ($field->period_scope !== null) {
            $review = $review ?: $plan->reviews->firstWhere('period_key', $field->period_scope);

            if (!$review || $review->status !== PdpPlanReview::STATUS_OPEN) {
                return false;
            }
        } else {
            if (in_array($plan->status, [PdpPlan::STATUS_COMPLETED, PdpPlan::STATUS_CANCELLED], true)) {
                return false;
            }

            $hasStartedPlanLevelSignoff = $plan->signatures
                ->whereNull('pdp_plan_review_id')
                ->contains(fn ($signature) => $signature->status === 'signed');

            if ($hasStartedPlanLevelSignoff) {
                return false;
            }
        }

        $allowedRoleTypes = $this->defaultEditableRoleTypes($field);

        return collect($allowedRoleTypes)->contains(fn (string $roleType): bool => $this->matchesRoleType($plan, $user, $roleType));
    }

    public function defaultEditableRoleTypes(PdpTemplateField $field): array
    {
        $fieldDescriptor = Str::lower($field->key . ' ' . $field->label);
        $section = $field->relationLoaded('section') ? $field->section : $field->section()->first();

        if ($section && in_array($field->key, $section->templateManagedFieldKeys(), true)) {
            return ['reporting_officer', 'authorized_official'];
        }

        if (Str::contains($fieldDescriptor, 'supervisor')) {
            return ['reporting_officer', 'authorized_official'];
        }

        if (in_array($field->key, ['score_out_of_10', 'supervisor_comment'], true)) {
            return ['reporting_officer', 'authorized_official'];
        }

        if ($field->key === 'supervisee_comment') {
            return ['employee', 'reporting_officer', 'authorized_official'];
        }

        if (Str::contains($fieldDescriptor, 'authorized')) {
            return ['authorized_official'];
        }

        if (Str::contains($fieldDescriptor, ['employee comment', 'supervisee'])) {
            return ['employee'];
        }

        if (
            $field->rating_scheme_key !== null
            || Str::contains($fieldDescriptor, [' score', ' rating'])
        ) {
            return ['reporting_officer', 'authorized_official'];
        }

        return ['employee', 'reporting_officer', 'authorized_official'];
    }

    public function elevatedPositions(): array
    {
        $settings = $this->settingsService->accessSettings();

        return Arr::wrap($settings['elevated_positions'] ?? PdpSettingsService::DEFAULT_ACCESS_SETTINGS['elevated_positions']);
    }

    public function elevatedRoles(): array
    {
        $settings = $this->settingsService->accessSettings();

        return Arr::wrap($settings['elevated_roles'] ?? PdpSettingsService::DEFAULT_ACCESS_SETTINGS['elevated_roles']);
    }
}
