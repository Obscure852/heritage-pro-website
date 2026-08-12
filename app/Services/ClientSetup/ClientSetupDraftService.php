<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupInvitation;
use App\Models\CrmClientSetupSubmission;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClientSetupDraftService
{
    public function __construct(
        private readonly ClientSetupAuditService $auditService,
        private readonly ClientSetupWizardService $wizardService
    ) {
    }

    public function saveStage(
        CrmClientSetupInvitation $invitation,
        string $stageKey,
        array $stagePayload,
        string $stageStatus,
        array $validationErrors = [],
        array $validationErrorDetails = []
    ): CrmClientSetupSubmission {
        $this->assertStageKey($stageKey);

        if (! in_array($stageStatus, config('client_setup.stage_statuses', []), true)) {
            throw new RuntimeException('Invalid client setup stage status.');
        }

        return DB::transaction(function () use ($invitation, $stageKey, $stagePayload, $stageStatus, $validationErrors, $validationErrorDetails): CrmClientSetupSubmission {
            $lockedInvitation = CrmClientSetupInvitation::query()
                ->with('submission')
                ->lockForUpdate()
                ->findOrFail($invitation->id);

            if (! $lockedInvitation->isUsable()) {
                throw new RuntimeException('This setup invitation is no longer active.');
            }

            $submission = CrmClientSetupSubmission::query()
                ->lockForUpdate()
                ->findOrFail($lockedInvitation->submission_id);

            if (in_array($submission->status, ['approved', 'archived'], true)) {
                throw new RuntimeException('This setup submission is no longer editable.');
            }

            if (in_array($submission->academic_status, ['submitted', 'approved'], true)
                && $this->wizardService->isAcademicStage($stageKey)) {
                throw new RuntimeException('The academic configuration has been submitted and is locked for review.');
            }

            $payload = $submission->payloadArray();
            $payload[$stageKey] = $stagePayload;

            $completedStages = array_values(array_unique($submission->completed_stages ?? []));

            if ($stageStatus === 'complete') {
                $completedStages[] = $stageKey;
                $completedStages = array_values(array_unique($completedStages));
            } else {
                $completedStages = array_values(array_diff($completedStages, [$stageKey]));
            }

            $now = now();
            $submission->forceFill([
                'payload' => $payload,
                'completed_stages' => $completedStages,
                'last_activity_at' => $now,
                'status' => $this->wizardService->stage($stageKey)['optional']
                    && $submission->status === 'academic_submitted'
                    ? 'supplemental_in_progress'
                    : $submission->status,
            ])->save();

            $stage = $submission->stageProgress()->updateOrCreate(
                ['stage_key' => $stageKey],
                [
                    'status' => $stageStatus,
                    'validation_errors' => $validationErrors === [] ? null : array_values($validationErrors),
                    'validation_error_details' => $validationErrorDetails === [] ? null : array_values($validationErrorDetails),
                    'completed_at' => $stageStatus === 'complete' ? $now : null,
                    'last_saved_at' => $now,
                ]
            );

            $revisionNumber = ((int) $submission->revisions()->max('revision_number')) + 1;
            $revision = $submission->revisions()->create([
                'invitation_id' => $lockedInvitation->id,
                'revision_number' => $revisionNumber,
                'source' => 'client_stage_save',
                'stage_key' => $stageKey,
                'payload' => $payload,
                'changed_keys' => array_keys($stagePayload),
            ]);

            $this->auditService->record($submission, 'stage_saved', [
                'invitation' => $lockedInvitation,
                'stage_key' => $stageKey,
                'metadata' => [
                    'status' => $stage->status,
                    'revision_number' => $revision->revision_number,
                    'validation_error_count' => count($validationErrors),
                ],
            ]);

            return $submission->fresh(['stageProgress']);
        });
    }

    public function forInvitation(CrmClientSetupInvitation $invitation): CrmClientSetupSubmission
    {
        if (! $invitation->isUsable()) {
            throw new RuntimeException('This setup invitation is no longer active.');
        }

        return $invitation->submission()->with(['stageProgress', 'attachments', 'migrationUploads' => fn ($query) => $query->latest()])->firstOrFail();
    }

    private function assertStageKey(string $stageKey): void
    {
        if (! preg_match(config('client_setup.stage_key_pattern'), $stageKey)) {
            throw new RuntimeException('Invalid client setup stage key.');
        }
    }
}
