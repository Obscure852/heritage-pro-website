<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentAudit;
use App\Models\User;
use App\Policies\DocumentPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WorkflowService {
    protected NotificationService $notificationService;
    protected DocumentSettingService $settingService;

    public function __construct(NotificationService $notificationService, DocumentSettingService $settingService) {
        $this->notificationService = $notificationService;
        $this->settingService = $settingService;
    }
    /**
     * Allowed status transitions.
     *
     * Maps each document status to the list of statuses it may transition to.
     */
    const TRANSITIONS = [
        Document::STATUS_DRAFT => [Document::STATUS_PENDING_REVIEW],
        Document::STATUS_PENDING_REVIEW => [Document::STATUS_UNDER_REVIEW, Document::STATUS_DRAFT],
        Document::STATUS_UNDER_REVIEW => [Document::STATUS_APPROVED, Document::STATUS_REVISION_REQUIRED],
        Document::STATUS_REVISION_REQUIRED => [Document::STATUS_PENDING_REVIEW],
        Document::STATUS_APPROVED => [Document::STATUS_PUBLISHED],
        Document::STATUS_PUBLISHED => [Document::STATUS_ARCHIVED, Document::STATUS_DRAFT],
        Document::STATUS_ARCHIVED => [Document::STATUS_APPROVED],
    ];

    /**
     * Validate that a status transition is allowed.
     *
     * @throws InvalidArgumentException
     */
    public function validateTransition(Document $document, string $targetStatus): void {
        $allowed = self::TRANSITIONS[$document->status] ?? [];

        if (!in_array($targetStatus, $allowed, true)) {
            throw new InvalidArgumentException(
                "Cannot transition from '{$document->status}' to '{$targetStatus}'."
            );
        }
    }

    /**
     * Submit a document for review.
     *
     * Creates DocumentApproval records for each reviewer and locks the document.
     *
     * @throws InvalidArgumentException
     */
    public function submitForReview(Document $document, array $reviewerIds, ?string $notes, ?Carbon $deadline, User $submitter): void {
        $this->validateTransition($document, Document::STATUS_PENDING_REVIEW);

        // WFL-06: Reviewer cannot be the document owner
        if (in_array($document->owner_id, $reviewerIds, true)) {
            throw new InvalidArgumentException('Document owner cannot be assigned as a reviewer.');
        }

        // Validate all reviewers have approver roles
        $validReviewers = User::whereIn('id', $reviewerIds)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', DocumentPolicy::APPROVER_ROLES);
            })
            ->pluck('id')
            ->toArray();

        if (count($validReviewers) !== count($reviewerIds)) {
            throw new InvalidArgumentException('One or more selected reviewers do not have approval permissions.');
        }

        $dueDate = $deadline ?? now()->addDays($this->settingService->get('approval.review_deadline_days', 7));

        DB::transaction(function () use ($document, $reviewerIds, $notes, $dueDate, $submitter) {
            // Update document status and lock
            $document->update([
                'status' => Document::STATUS_PENDING_REVIEW,
                'is_locked' => true,
                'locked_by_user_id' => $submitter->id,
                'locked_at' => now(),
            ]);

            // Create approval records for each reviewer
            foreach ($reviewerIds as $reviewerId) {
                DocumentApproval::create([
                    'document_id' => $document->id,
                    'version_id' => $document->currentVersion?->id,
                    'workflow_step' => 1,
                    'reviewer_id' => $reviewerId,
                    'status' => DocumentApproval::STATUS_PENDING,
                    'submitted_by_user_id' => $submitter->id,
                    'submission_notes' => $notes,
                    'submitted_at' => now(),
                    'due_date' => $dueDate,
                ]);
            }

            // Create audit record
            DocumentAudit::create([
                'document_id' => $document->id,
                'version_id' => $document->currentVersion?->id,
                'user_id' => $submitter->id,
                'action' => DocumentAudit::ACTION_SUBMITTED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'reviewer_ids' => $reviewerIds,
                    'notes' => $notes,
                    'due_date' => $dueDate->toDateString(),
                ],
            ]);
        });

        // Send notifications OUTSIDE transaction so failures don't roll back workflow
        try {
            $reviewers = User::whereIn('id', $reviewerIds)->get();
            $this->notificationService->notifySubmittedForReview($document, $reviewers, $submitter);
        } catch (\Throwable $e) {
            Log::error('Failed to send submit-for-review notifications', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Withdraw a document submission before review begins.
     *
     * Only allowed when status is pending_review (not under_review).
     *
     * @throws InvalidArgumentException
     */
    public function withdrawSubmission(Document $document, User $author): void {
        if ($document->status !== Document::STATUS_PENDING_REVIEW) {
            throw new InvalidArgumentException('Can only withdraw documents that are pending review.');
        }

        if ($author->id !== $document->owner_id) {
            throw new InvalidArgumentException('Only the document owner can withdraw a submission.');
        }

        DB::transaction(function () use ($document, $author) {
            // Update document status and unlock
            $document->update([
                'status' => Document::STATUS_DRAFT,
                'is_locked' => false,
                'locked_by_user_id' => null,
                'locked_at' => null,
            ]);

            // Delete pending approval records
            DocumentApproval::where('document_id', $document->id)
                ->where('status', DocumentApproval::STATUS_PENDING)
                ->delete();

            // Create audit record
            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => $author->id,
                'action' => DocumentAudit::ACTION_WITHDRAWN,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    /**
     * Review a document (approve, reject, or request revision).
     *
     * Uses lockForUpdate on the document to prevent race conditions.
     *
     * @param string $action One of: 'approve', 'reject', 'revision'
     * @throws InvalidArgumentException
     */
    public function reviewDocument(Document $document, User $reviewer, string $action, ?string $comments): void {
        if (!in_array($action, ['approve', 'reject', 'revision'], true)) {
            throw new InvalidArgumentException("Invalid review action: {$action}");
        }

        $finalApproval = false;

        // Find the approval record for this reviewer
        $approval = DocumentApproval::where('document_id', $document->id)
            ->where('reviewer_id', $reviewer->id)
            ->whereIn('status', [DocumentApproval::STATUS_PENDING, DocumentApproval::STATUS_IN_REVIEW])
            ->first();

        if (!$approval) {
            throw new InvalidArgumentException('You are not assigned as a reviewer for this document.');
        }

        DB::transaction(function () use ($document, $reviewer, $action, $comments, $approval, &$finalApproval) {
            // Lock document row to prevent race conditions
            $document = Document::lockForUpdate()->find($document->id);

            if (!$document) {
                throw new InvalidArgumentException('Document could not be found.');
            }

            if (!in_array($document->status, [Document::STATUS_PENDING_REVIEW, Document::STATUS_UNDER_REVIEW], true)) {
                throw new InvalidArgumentException('This document is no longer available for review.');
            }

            // Transition from pending_review to under_review if first reviewer to act
            if ($document->status === Document::STATUS_PENDING_REVIEW) {
                $document->update(['status' => Document::STATUS_UNDER_REVIEW]);
            }

            if ($action === 'approve') {
                $approval->update([
                    'status' => DocumentApproval::STATUS_APPROVED,
                    'review_comments' => $comments,
                    'reviewed_at' => now(),
                ]);

                $hasPendingReviews = DocumentApproval::where('document_id', $document->id)
                    ->whereIn('status', [DocumentApproval::STATUS_PENDING, DocumentApproval::STATUS_IN_REVIEW])
                    ->exists();

                if (!$hasPendingReviews) {
                    $document->update([
                        'status' => Document::STATUS_APPROVED,
                        'is_locked' => false,
                        'locked_by_user_id' => null,
                        'locked_at' => null,
                    ]);
                    $finalApproval = true;
                }

                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $reviewer->id,
                    'action' => DocumentAudit::ACTION_APPROVED,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'metadata' => [
                        'comments' => $comments,
                        'approval_id' => $approval->id,
                    ],
                ]);
            } elseif ($action === 'reject') {
                $approval->update([
                    'status' => DocumentApproval::STATUS_REJECTED,
                    'review_comments' => $comments,
                    'reviewed_at' => now(),
                ]);

                $document->update([
                    'status' => Document::STATUS_REVISION_REQUIRED,
                    'is_locked' => false,
                    'locked_by_user_id' => null,
                    'locked_at' => null,
                ]);

                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $reviewer->id,
                    'action' => DocumentAudit::ACTION_REJECTED,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'metadata' => [
                        'comments' => $comments,
                        'approval_id' => $approval->id,
                    ],
                ]);
            } elseif ($action === 'revision') {
                $approval->update([
                    'status' => DocumentApproval::STATUS_REVISION_REQUIRED,
                    'review_comments' => $comments,
                    'reviewed_at' => now(),
                ]);

                $document->update([
                    'status' => Document::STATUS_REVISION_REQUIRED,
                    'is_locked' => false,
                    'locked_by_user_id' => null,
                    'locked_at' => null,
                ]);

                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $reviewer->id,
                    'action' => DocumentAudit::ACTION_REVISION_REQUESTED,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'metadata' => [
                        'comments' => $comments,
                        'approval_id' => $approval->id,
                    ],
                ]);
            }
        });

        // Refresh to pick up changes made inside the transaction
        $document->refresh();

        // Send notifications OUTSIDE transaction
        try {
            if ($action === 'approve' && $finalApproval) {
                $this->notificationService->notifyApproved($document, $reviewer);
            } elseif ($action === 'reject') {
                $this->notificationService->notifyRejected($document, $reviewer, $comments ?? '');
            } elseif ($action === 'revision') {
                $this->notificationService->notifyRevisionRequested($document, $reviewer, $comments ?? '');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send review notification', [
                'document_id' => $document->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Publish an approved document with specified visibility.
     *
     * When require_approval is true, only approved documents can be published.
     * When false, any non-archived document can be published.
     *
     * @throws InvalidArgumentException
     */
    public function publishDocument(Document $document, string $visibility, ?array $roles, User $publisher): void {
        $requireApproval = $this->settingService->get('approval.require_approval', true);

        if ($requireApproval && $document->status !== Document::STATUS_APPROVED) {
            throw new InvalidArgumentException('Document must be approved before publishing when approval is required.');
        }

        if (!$requireApproval && $document->status === Document::STATUS_ARCHIVED) {
            throw new InvalidArgumentException('Cannot publish an archived document.');
        }

        DB::transaction(function () use ($document, $visibility, $roles, $publisher) {
            $document->update([
                'status' => Document::STATUS_PUBLISHED,
                'published_at' => now(),
                'visibility' => $visibility,
                'published_roles' => $visibility === 'roles' ? $roles : null,
            ]);

            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => $publisher->id,
                'action' => DocumentAudit::ACTION_PUBLISHED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'visibility' => $visibility,
                    'roles' => $visibility === 'roles' ? $roles : null,
                ],
            ]);
        });

        // Send notifications OUTSIDE transaction
        try {
            $this->notificationService->notifyPublished($document, $publisher);
        } catch (\Throwable $e) {
            Log::error('Failed to send publish notification', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Unpublish a document, resetting to draft status.
     *
     * @throws InvalidArgumentException
     */
    public function unpublishDocument(Document $document, User $user): void {
        if ($document->status !== Document::STATUS_PUBLISHED) {
            throw new InvalidArgumentException('Only published documents can be unpublished.');
        }

        DB::transaction(function () use ($document, $user) {
            $document->update([
                'status' => Document::STATUS_DRAFT,
                'published_at' => null,
                'is_featured' => false,
                'published_roles' => null,
            ]);

            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'action' => DocumentAudit::ACTION_UNPUBLISHED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    /**
     * Archive a published document.
     *
     * @throws InvalidArgumentException
     */
    public function archiveDocument(Document $document, User $user): void {
        if ($document->legal_hold) {
            throw new InvalidArgumentException('Document is under legal hold and cannot be archived.');
        }

        if ($document->status !== Document::STATUS_PUBLISHED) {
            throw new InvalidArgumentException('Only published documents can be archived.');
        }

        DB::transaction(function () use ($document, $user) {
            $document->update([
                'status' => Document::STATUS_ARCHIVED,
                'archived_at' => now(),
                'is_featured' => false,
            ]);

            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'action' => DocumentAudit::ACTION_ARCHIVED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }

    /**
     * Unarchive a document, restoring to approved status.
     *
     * @throws InvalidArgumentException
     */
    public function unarchiveDocument(Document $document, User $user): void {
        if ($document->status !== Document::STATUS_ARCHIVED) {
            throw new InvalidArgumentException('Only archived documents can be unarchived.');
        }

        DB::transaction(function () use ($document, $user) {
            $document->update([
                'status' => Document::STATUS_APPROVED,
                'archived_at' => null,
            ]);

            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'action' => DocumentAudit::ACTION_RESTORED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => ['action' => 'unarchived'],
            ]);
        });
    }

    /**
     * Reset document status to draft after content edit when require_approval is true.
     *
     * Called after document content update to enforce re-approval (WFL-10).
     */
    public function handlePostEditStatusReset(Document $document): void {
        if (!$this->settingService->get('approval.require_approval', true)) {
            return;
        }

        if ($document->status !== Document::STATUS_PUBLISHED) {
            return;
        }

        $document->update([
            'status' => Document::STATUS_DRAFT,
            'published_at' => null,
            'is_featured' => false,
            'published_roles' => null,
        ]);

        DocumentAudit::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'action' => DocumentAudit::ACTION_UPDATED,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => ['action' => 'status_reset_after_edit'],
        ]);
    }
}
