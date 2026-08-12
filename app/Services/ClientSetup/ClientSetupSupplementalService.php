<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupInvitation;
use App\Models\CrmClientSetupSubmission;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClientSetupSupplementalService
{
    public function __construct(
        private readonly ClientSetupAcademicService $academicService,
        private readonly ClientSetupAuditService $auditService
    ) {
    }

    public function summary(CrmClientSetupSubmission $submission): array
    {
        $rows = [];

        foreach (config('client_setup.stages', []) as $stage) {
            if (! $stage['optional']) {
                continue;
            }

            $rows[] = $this->stageSummary($submission, $stage['key'], $stage['label']);
        }

        return [
            'rows' => $rows,
            'complete' => count(array_filter($rows, static fn (array $row): bool => $row['state'] === 'complete')),
            'deferred' => count(array_filter($rows, static fn (array $row): bool => $row['state'] === 'deferred')),
            'in_progress' => count(array_filter($rows, static fn (array $row): bool => $row['state'] === 'in_progress')),
            'not_started' => count(array_filter($rows, static fn (array $row): bool => $row['state'] === 'not_started')),
            'total' => count($rows),
        ];
    }

    public function stageSummary(CrmClientSetupSubmission $submission, string $stageKey, ?string $label = null): array
    {
        $payload = $submission->payloadArray()[$stageKey] ?? [];
        $progress = $submission->stageProgress->firstWhere('stage_key', $stageKey);
        $validation = $this->academicService->validateStage($stageKey, is_array($payload) ? $payload : []);
        $deferred = $this->isExplicitlyDeferred($stageKey, $payload);
        $hasMeaningfulPayload = $this->hasMeaningfulValue($payload);

        $state = 'not_started';

        if ($deferred && $validation['errors'] === []) {
            $state = 'deferred';
        } elseif ($validation['errors'] === [] && $progress?->status === 'complete') {
            $state = 'complete';
        } elseif ($hasMeaningfulPayload || $progress?->status === 'in_progress') {
            $state = 'in_progress';
        }

        $errors = $state === 'not_started' ? [] : $validation['errors'];

        return [
            'key' => $stageKey,
            'label' => $label ?: $stageKey,
            'state' => $state,
            'status' => $progress?->status ?: 'not_started',
            'errors' => $errors,
            'deferred' => $deferred,
        ];
    }

    public function complete(CrmClientSetupSubmission $submission, CrmClientSetupInvitation $invitation): CrmClientSetupSubmission
    {
        return DB::transaction(function () use ($submission, $invitation): CrmClientSetupSubmission {
            $locked = CrmClientSetupSubmission::query()->lockForUpdate()->findOrFail($submission->id);

            if (in_array($locked->status, ['approved', 'archived'], true)) {
                throw new RuntimeException('This setup submission is no longer accepting supplemental changes.');
            }

            if ($locked->status === 'complete_submission') {
                return $locked->fresh();
            }

            $summary = $this->summary($locked->fresh(['stageProgress']));

            if ($summary['not_started'] > 0 || $summary['in_progress'] > 0 || $summary['rows'] === []) {
                throw new RuntimeException('Complete or explicitly defer every optional section before finalizing supplemental setup.');
            }

            $locked->forceFill([
                'status' => 'complete_submission',
                'completed_at' => now(),
                'last_activity_at' => now(),
            ])->save();

            $this->auditService->record($locked, 'supplemental_setup_completed', [
                'invitation' => $invitation,
                'metadata' => [
                    'completed_sections' => $summary['complete'],
                    'deferred_sections' => $summary['deferred'],
                ],
            ]);

            return $locked->fresh();
        });
    }

    private function isExplicitlyDeferred(string $stageKey, array $payload): bool
    {
        if ($stageKey === 'finance') {
            return ($payload['finance_scope_decision'] ?? null) === 'defer';
        }

        if ($stageKey === 'migration') {
            $scope = $payload['migration_scope'] ?? [];
            return is_array($scope) && in_array('no_migration', $scope, true) && count(array_diff($scope, ['no_migration'])) === 0;
        }

        if ($stageKey === 'integrations_access') {
            $scope = $payload['integration_scope'] ?? [];
            return is_array($scope) && in_array('none', $scope, true) && count(array_diff($scope, ['none'])) === 0;
        }

        return false;
    }

    private function hasMeaningfulValue(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->hasMeaningfulValue($item)) {
                    return true;
                }
            }

            return false;
        }

        return $value !== null && $value !== '' && $value !== false;
    }
}
