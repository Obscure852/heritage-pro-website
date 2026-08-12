<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupChangeRequest;
use App\Models\CrmClientSetupInvitation;
use App\Models\CrmClientSetupSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClientSetupReviewService
{
    private const TRANSITIONS = [
        'draft' => ['under_review', 'archived'],
        'academic_submitted' => ['supplemental_in_progress', 'complete_submission', 'under_review', 'changes_requested', 'archived'],
        'supplemental_in_progress' => ['complete_submission', 'under_review', 'changes_requested', 'archived'],
        'complete_submission' => ['under_review', 'changes_requested', 'approved', 'archived'],
        'under_review' => ['changes_requested', 'approved', 'archived'],
        'changes_requested' => ['under_review', 'approved', 'archived'],
        'approved' => ['archived'],
        'archived' => [],
    ];

    public function __construct(
        private readonly ClientSetupAuditService $auditService,
        private readonly ClientSetupNotificationService $notificationService
    ) {
    }

    public function changeStatus(CrmClientSetupSubmission $submission, string $status, User $actor): CrmClientSetupSubmission
    {
        $updated = DB::transaction(function () use ($submission, $status, $actor): CrmClientSetupSubmission {
            $locked = CrmClientSetupSubmission::query()->lockForUpdate()->findOrFail($submission->id);
            $allowed = self::TRANSITIONS[$locked->status] ?? [];

            if ($locked->status !== $status && ! in_array($status, $allowed, true)) {
                throw new RuntimeException("A client setup cannot move from {$locked->status} to {$status}.");
            }

            $locked->forceFill([
                'status' => $status,
                'completed_at' => $status === 'approved' ? now() : $locked->completed_at,
                'last_activity_at' => now(),
            ])->save();

            $this->auditService->record($locked, 'review_status_changed', [
                'user' => $actor,
                'actor_type' => 'crm_user',
                'metadata' => ['from' => $submission->status, 'to' => $status],
            ]);

            return $locked->fresh();
        });

        if ($status === 'approved') {
            $this->notificationService->notifySubmission($updated->load(['invitations', 'assignedTo']), 'approved', [
                'audiences' => ['client', 'crm'],
                'context_key' => 'status-approved:' . now()->format('YmdHis'),
            ]);
        }

        return $updated;
    }

    public function assign(CrmClientSetupSubmission $submission, ?int $userId, User $actor): CrmClientSetupSubmission
    {
        return DB::transaction(function () use ($submission, $userId, $actor): CrmClientSetupSubmission {
            $locked = CrmClientSetupSubmission::query()->lockForUpdate()->findOrFail($submission->id);
            $oldId = $locked->assigned_to_id;
            $locked->forceFill(['assigned_to_id' => $userId, 'last_activity_at' => now()])->save();

            $this->auditService->record($locked, 'review_assignment_changed', [
                'user' => $actor,
                'actor_type' => 'crm_user',
                'metadata' => ['from_user_id' => $oldId, 'to_user_id' => $userId],
            ]);

            return $locked->fresh();
        });
    }

    public function addNote(CrmClientSetupSubmission $submission, string $body, User $actor): void
    {
        DB::transaction(function () use ($submission, $body, $actor): void {
            $submission->notes()->create(['user_id' => $actor->id, 'body' => trim($body)]);
            $submission->forceFill(['last_activity_at' => now()])->save();
            $this->auditService->record($submission, 'review_note_added', [
                'user' => $actor,
                'actor_type' => 'crm_user',
            ]);
        });
    }

    public function addChangeRequest(CrmClientSetupSubmission $submission, array $attributes, User $actor): CrmClientSetupChangeRequest
    {
        $changeRequest = DB::transaction(function () use ($submission, $attributes, $actor): CrmClientSetupChangeRequest {
            $changeRequest = $submission->changeRequests()->create([
                'user_id' => $actor->id,
                'stage_key' => $attributes['stage_key'] ?? null,
                'field_key' => $attributes['field_key'] ?? null,
                'body' => trim($attributes['body']),
                'status' => 'open',
            ]);

            $submission->forceFill(['status' => 'changes_requested', 'last_activity_at' => now()])->save();
            $this->auditService->record($submission, 'review_change_requested', [
                'user' => $actor,
                'actor_type' => 'crm_user',
                'stage_key' => $changeRequest->stage_key,
                'metadata' => ['field_key' => $changeRequest->field_key, 'change_request_id' => $changeRequest->id],
            ]);

            return $changeRequest;
        });

        $this->notificationService->notifySubmission(
            $submission->fresh(['invitations', 'assignedTo']),
            'changes_requested',
            [
                'audiences' => ['client', 'crm'],
                'context_key' => 'change-request:' . $changeRequest->uuid,
                'details' => [$changeRequest->body],
            ]
        );

        return $changeRequest;
    }

    public function resolveChangeRequest(CrmClientSetupChangeRequest $changeRequest, User $actor): void
    {
        DB::transaction(function () use ($changeRequest, $actor): void {
            $locked = CrmClientSetupChangeRequest::query()->lockForUpdate()->findOrFail($changeRequest->id);

            if ($locked->status === 'resolved') {
                return;
            }

            $locked->forceFill([
                'status' => 'resolved',
                'resolved_by_id' => $actor->id,
                'resolved_at' => now(),
            ])->save();

            $submission = $locked->submission()->firstOrFail();
            $submission->forceFill(['last_activity_at' => now()])->save();
            $this->auditService->record($submission, 'review_change_request_resolved', [
                'user' => $actor,
                'actor_type' => 'crm_user',
                'stage_key' => $locked->stage_key,
                'metadata' => ['change_request_id' => $locked->id],
            ]);
        });
    }

    public function recordClientResponse(
        CrmClientSetupChangeRequest $changeRequest,
        CrmClientSetupInvitation $invitation,
        string $response
    ): void {
        DB::transaction(function () use ($changeRequest, $invitation, $response): void {
            $locked = CrmClientSetupChangeRequest::query()
                ->with('submission')
                ->lockForUpdate()
                ->findOrFail($changeRequest->id);

            abort_unless($locked->submission_id === $invitation->submission_id, 404);
            abort_unless($locked->status === 'open', 422, 'This change request is already resolved.');

            $locked->forceFill([
                'client_response' => trim($response),
                'responded_at' => now(),
            ])->save();

            $submission = $locked->submission;
            $submission->forceFill(['last_activity_at' => now()])->save();
            $this->auditService->record($submission, 'client_change_response_received', [
                'invitation' => $invitation,
                'metadata' => ['change_request_id' => $locked->id],
            ]);
        });

        $submission = $changeRequest->fresh('submission')->submission;
        $this->notificationService->notifySubmission($submission->load(['invitations', 'assignedTo']), 'client_change_response', [
            'audiences' => ['crm'],
            'context_key' => 'client-response:' . $changeRequest->uuid,
        ]);
    }
}
