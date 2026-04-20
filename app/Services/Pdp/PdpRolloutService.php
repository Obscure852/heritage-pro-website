<?php

namespace App\Services\Pdp;

use App\Models\Pdp\PdpPlan;
use App\Models\Pdp\PdpRollout;
use App\Models\Pdp\PdpTemplate;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PdpRolloutService
{
    public function __construct(
        private readonly PdpPlanService $planService,
        private readonly PdpTemplateService $templateService
    ) {
    }

    public function activeRollout(): ?PdpRollout
    {
        $rollout = PdpRollout::query()
            ->where('status', PdpRollout::STATUS_ACTIVE)
            ->latest('id')
            ->first();

        return $rollout ? $this->loadRollout($rollout) : null;
    }

    public function allRollouts(): Collection
    {
        return PdpRollout::query()
            ->with(['template', 'fallbackSupervisor', 'launcher'])
            ->withCount('plans')
            ->orderByDesc('id')
            ->get();
    }

    public function launch(array $attributes, User $actor): PdpRollout
    {
        $template = $this->resolveLaunchTemplate($attributes['pdp_template_id'] ?? null);
        $startDate = $this->normalizeDate($attributes['plan_period_start'] ?? null, 'plan_period_start');
        $endDate = $this->normalizeDate($attributes['plan_period_end'] ?? null, 'plan_period_end');

        if ($endDate->lt($startDate)) {
            throw new InvalidArgumentException('The rollout end date must be on or after the start date.');
        }

        $fallbackSupervisor = $this->resolveFallbackSupervisor((int) ($attributes['fallback_supervisor_user_id'] ?? 0));

        return DB::transaction(function () use ($template, $startDate, $endDate, $attributes, $actor, $fallbackSupervisor): PdpRollout {
            PdpRollout::query()
                ->where('status', PdpRollout::STATUS_ACTIVE)
                ->update([
                    'status' => PdpRollout::STATUS_SUPERSEDED,
                    'closed_at' => now(),
                ]);

            /** @var PdpRollout $rollout */
            $rollout = PdpRollout::query()->create([
                'pdp_template_id' => $template->id,
                'label' => trim((string) ($attributes['label'] ?? '')) ?: 'PDP ' . $startDate->year . ' Cycle',
                'cycle_year' => (int) ($attributes['cycle_year'] ?? $startDate->year),
                'plan_period_start' => $startDate->toDateString(),
                'plan_period_end' => $endDate->toDateString(),
                'status' => PdpRollout::STATUS_ACTIVE,
                'provisioning_status' => PdpRollout::PROVISIONING_RUNNING,
                'auto_provision_new_staff' => (bool) ($attributes['auto_provision_new_staff'] ?? true),
                'fallback_supervisor_user_id' => $fallbackSupervisor->id,
                'launched_by' => $actor->id,
                'launched_at' => now(),
            ]);

            $summary = $this->provisionExistingEligibleStaff($rollout, $actor->id);

            $rollout->fill([
                'provisioning_status' => PdpRollout::PROVISIONING_COMPLETED,
                'provisioned_count' => $summary['counts']['provisioned'],
                'skipped_count' => $summary['counts']['skipped'],
                'summary_json' => $summary['counts'],
                'exceptions_json' => $summary['exceptions'],
            ])->save();

            return $this->loadRollout($rollout);
        });
    }

    public function provisionExistingEligibleStaff(PdpRollout $rollout, ?int $createdBy = null): array
    {
        $summary = [
            'counts' => [
                'eligible' => 0,
                'provisioned' => 0,
                'skipped' => 0,
                'fallback_assigned' => 0,
            ],
            'exceptions' => [],
        ];

        foreach ($this->eligibleStaffQuery()->get() as $user) {
            $summary['counts']['eligible']++;
            $result = $this->attemptProvisionUserForRollout($user, $rollout, $createdBy);

            if ($result['status'] === 'created') {
                $summary['counts']['provisioned']++;
                if ($result['used_fallback']) {
                    $summary['counts']['fallback_assigned']++;
                }
                continue;
            }

            $summary['counts']['skipped']++;
            $summary['exceptions'][] = [
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'reason' => $result['reason'],
            ];
        }

        return $summary;
    }

    public function provisionUserIfEligible(User $user): ?PdpPlan
    {
        $rollout = $this->activeRollout();

        if (!$rollout || !$rollout->auto_provision_new_staff) {
            return null;
        }

        $result = $this->attemptProvisionUserForRollout($user, $rollout, $rollout->launched_by);

        return $result['plan'];
    }

    public function attemptProvisionUserForRollout(User $user, PdpRollout $rollout, ?int $createdBy = null): array
    {
        $rollout = $this->loadRollout($rollout);

        if (!$this->isEligibleStaff($user)) {
            return ['status' => 'skipped', 'reason' => 'Staff member is not currently eligible for PDP auto-provisioning.', 'used_fallback' => false, 'plan' => null];
        }

        if ($rollout->status !== PdpRollout::STATUS_ACTIVE) {
            return ['status' => 'skipped', 'reason' => 'The PDP rollout is no longer active.', 'used_fallback' => false, 'plan' => null];
        }

        if (PdpPlan::query()->where('pdp_rollout_id', $rollout->id)->where('user_id', $user->id)->exists()) {
            return ['status' => 'skipped', 'reason' => 'A PDP plan has already been provisioned for this rollout.', 'used_fallback' => false, 'plan' => null];
        }

        $supervisor = $this->resolveSupervisorForUser($user, $rollout);
        if (!$supervisor) {
            return ['status' => 'skipped', 'reason' => 'No reporting supervisor or fallback supervisor is available.', 'used_fallback' => false, 'plan' => null];
        }

        $usedFallback = (int) $supervisor->id !== (int) $user->reporting_to;

        try {
            $plan = $this->planService->createPlanForRollout($user, $rollout, [
                'supervisor_id' => $supervisor->id,
                'created_by' => $createdBy,
            ]);
        } catch (RuntimeException $exception) {
            return [
                'status' => 'skipped',
                'reason' => $exception->getMessage(),
                'used_fallback' => $usedFallback,
                'plan' => null,
            ];
        }

        return ['status' => 'created', 'reason' => null, 'used_fallback' => $usedFallback, 'plan' => $plan];
    }

    private function resolveLaunchTemplate(?int $templateId): PdpTemplate
    {
        $activeTemplate = $this->templateService->getActiveTemplate();

        if (!$activeTemplate) {
            throw new RuntimeException('Activate a published PDP template before launching a rollout.');
        }

        if ($templateId !== null && $activeTemplate->id !== $templateId) {
            throw new RuntimeException('Rollouts must use the currently active PDP template version.');
        }

        if ($activeTemplate->status !== PdpTemplate::STATUS_PUBLISHED) {
            throw new RuntimeException('Only a published PDP template can be used for rollout.');
        }

        return $activeTemplate;
    }

    private function normalizeDate(mixed $value, string $field): Carbon
    {
        if ($value === null || $value === '') {
            throw new InvalidArgumentException("The {$field} field is required.");
        }

        return Carbon::parse($value)->startOfDay();
    }

    private function resolveFallbackSupervisor(int $userId): User
    {
        $user = User::query()->find($userId);

        if (!$user) {
            throw new InvalidArgumentException('Select a valid fallback supervisor before launching the rollout.');
        }

        return $user;
    }

    private function resolveSupervisorForUser(User $user, PdpRollout $rollout): ?User
    {
        if ($user->reporting_to) {
            $supervisor = User::query()->find($user->reporting_to);
            if ($supervisor) {
                return $supervisor;
            }
        }

        return $rollout->fallbackSupervisor;
    }

    private function eligibleStaffQuery()
    {
        return User::query()
            ->where('status', 'Current')
            ->where('active', true)
            ->orderBy('firstname')
            ->orderBy('lastname');
    }

    private function isEligibleStaff(User $user): bool
    {
        return $user->status === 'Current' && (bool) $user->active;
    }

    private function loadRollout(PdpRollout $rollout): PdpRollout
    {
        return $rollout->fresh([
            'template.sections.fields.childFields',
            'template.sections.rows',
            'template.periods',
            'template.ratingSchemes',
            'template.approvalSteps',
            'fallbackSupervisor',
            'launcher',
            'plans.user',
            'plans.supervisor',
        ]);
    }
}
