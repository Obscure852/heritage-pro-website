<?php

namespace App\Services\Documents;

use App\Mail\Documents\DocumentApprovedMail;
use App\Mail\Documents\DocumentPublishedMail;
use App\Mail\Documents\DocumentRejectedMail;
use App\Mail\Documents\DocumentRevisionRequestedMail;
use App\Mail\Documents\DocumentSubmittedMail;
use App\Mail\Documents\ReviewDeadlineReminderMail;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Orchestrates both in-app and email notifications for document workflow events.
 *
 * Each method creates a DocumentNotification record (in-app) and queues
 * an email via the appropriate Mailable. Email failures are caught and
 * logged so they never break the workflow.
 */
class NotificationService {
    /**
     * Notify reviewers when a document is submitted for review.
     *
     * Creates in-app notifications and sends emails to each assigned reviewer.
     *
     * @param Document $document The submitted document
     * @param Collection $reviewers Collection of User models assigned as reviewers
     * @param User $submitter The user who submitted the document
     */
    public function notifySubmittedForReview(Document $document, Collection $reviewers, User $submitter): void {
        foreach ($reviewers as $reviewer) {
            DocumentNotification::create([
                'user_id' => $reviewer->id,
                'type' => DocumentNotification::TYPE_SUBMITTED,
                'title' => 'Document Submitted for Review',
                'message' => "{$submitter->full_name} submitted '{$document->title}' for your review.",
                'url' => route('documents.show', $document),
                'data' => [
                    'document_id' => $document->id,
                    'submitter_id' => $submitter->id,
                    'icon' => 'bx-file',
                    'color' => 'info',
                ],
            ]);

            try {
                Mail::to($reviewer)->queue(new DocumentSubmittedMail($document, $submitter));
            } catch (\Throwable $e) {
                Log::error('Failed to send document submitted email', [
                    'document_id' => $document->id,
                    'reviewer_id' => $reviewer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Notify the document owner when their document is approved.
     *
     * @param Document $document The approved document
     * @param User $reviewer The reviewer who approved it
     */
    public function notifyApproved(Document $document, User $reviewer): void {
        DocumentNotification::create([
            'user_id' => $document->owner_id,
            'type' => DocumentNotification::TYPE_APPROVED,
            'title' => 'Document Approved',
            'message' => "{$reviewer->full_name} approved '{$document->title}'.",
            'url' => route('documents.show', $document),
            'data' => [
                'document_id' => $document->id,
                'reviewer_id' => $reviewer->id,
                'icon' => 'bx-check-circle',
                'color' => 'success',
            ],
        ]);

        try {
            Mail::to($document->owner)->queue(new DocumentApprovedMail($document, $reviewer));
        } catch (\Throwable $e) {
            Log::error('Failed to send document approved email', [
                'document_id' => $document->id,
                'owner_id' => $document->owner_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify the document owner when their document is rejected.
     *
     * @param Document $document The rejected document
     * @param User $reviewer The reviewer who rejected it
     * @param string $comments Rejection comments from the reviewer
     */
    public function notifyRejected(Document $document, User $reviewer, string $comments): void {
        DocumentNotification::create([
            'user_id' => $document->owner_id,
            'type' => DocumentNotification::TYPE_REJECTED,
            'title' => 'Document Rejected',
            'message' => "{$reviewer->full_name} rejected '{$document->title}'.",
            'url' => route('documents.show', $document),
            'data' => [
                'document_id' => $document->id,
                'reviewer_id' => $reviewer->id,
                'comments' => $comments,
                'icon' => 'bx-x-circle',
                'color' => 'danger',
            ],
        ]);

        try {
            Mail::to($document->owner)->queue(new DocumentRejectedMail($document, $reviewer, $comments));
        } catch (\Throwable $e) {
            Log::error('Failed to send document rejected email', [
                'document_id' => $document->id,
                'owner_id' => $document->owner_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify the document owner when revisions are requested.
     *
     * @param Document $document The document requiring revisions
     * @param User $reviewer The reviewer requesting revisions
     * @param string $comments Revision comments from the reviewer
     */
    public function notifyRevisionRequested(Document $document, User $reviewer, string $comments): void {
        DocumentNotification::create([
            'user_id' => $document->owner_id,
            'type' => DocumentNotification::TYPE_REVISION_REQUESTED,
            'title' => 'Revision Requested',
            'message' => "{$reviewer->full_name} requested revisions on '{$document->title}'.",
            'url' => route('documents.show', $document),
            'data' => [
                'document_id' => $document->id,
                'reviewer_id' => $reviewer->id,
                'comments' => $comments,
                'icon' => 'bx-revision',
                'color' => 'warning',
            ],
        ]);

        try {
            Mail::to($document->owner)->queue(new DocumentRevisionRequestedMail($document, $reviewer, $comments));
        } catch (\Throwable $e) {
            Log::error('Failed to send document revision requested email', [
                'document_id' => $document->id,
                'owner_id' => $document->owner_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify relevant users when a document is published.
     *
     * In-app notifications go to staff based on visibility:
     * - 'internal' or 'public': all current staff users
     * - 'roles': users with matching roles (future implementation)
     *
     * Email notification only to the document owner confirming publication.
     *
     * @param Document $document The published document
     * @param User $publisher The user who published it
     */
    public function notifyPublished(Document $document, User $publisher): void {
        $recipientQuery = User::query()
            ->where('status', 'Current')
            ->whereNull('deleted_at')
            ->where('id', '!=', $publisher->id)
            ->select('id');

        $sendInApp = true;
        if ($document->visibility === Document::VISIBILITY_ROLES) {
            $roleValues = collect($document->published_roles ?? [])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->values();

            $roleIds = $roleValues
                ->filter(fn ($value) => is_numeric($value))
                ->map(fn ($value) => (int) $value)
                ->unique()
                ->values()
                ->all();
            $roleNames = $roleValues
                ->filter(fn ($value) => !is_numeric($value))
                ->map(fn ($value) => (string) $value)
                ->unique()
                ->values()
                ->all();

            if (empty($roleIds) && empty($roleNames)) {
                $sendInApp = false;
            } else {
                $recipientQuery->whereHas('roles', function ($roleQuery) use ($roleIds, $roleNames) {
                    $roleQuery->where(function ($match) use ($roleIds, $roleNames) {
                        if (!empty($roleIds)) {
                            $match->whereIn('roles.id', $roleIds);
                        }
                        if (!empty($roleNames)) {
                            $method = !empty($roleIds) ? 'orWhereIn' : 'whereIn';
                            $match->{$method}('roles.name', $roleNames);
                        }
                    });
                });
            }
        } elseif ($document->visibility === Document::VISIBILITY_PRIVATE) {
            $sendInApp = false;
        }

        if ($sendInApp) {
            $recipientQuery->chunkById(100, function ($users) use ($document, $publisher) {
                $notifications = [];
                $now = now();

                foreach ($users as $user) {
                    $notifications[] = [
                        'user_id' => $user->id,
                        'type' => DocumentNotification::TYPE_PUBLISHED,
                        'title' => 'Document Published',
                        'message' => "{$publisher->full_name} published '{$document->title}'.",
                        'url' => route('documents.show', $document),
                        'data' => json_encode([
                            'document_id' => $document->id,
                            'publisher_id' => $publisher->id,
                            'icon' => 'bx-globe',
                            'color' => 'primary',
                        ]),
                        'created_at' => $now,
                    ];
                }

                if (!empty($notifications)) {
                    DocumentNotification::insert($notifications);
                }
            });
        }

        // Email only to the document owner confirming publication
        try {
            Mail::to($document->owner)->queue(new DocumentPublishedMail($document, $publisher));
        } catch (\Throwable $e) {
            Log::error('Failed to send document published email', [
                'document_id' => $document->id,
                'owner_id' => $document->owner_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify a reviewer when their review deadline is approaching.
     *
     * Also updates the approval record's reminder_sent_at timestamp.
     *
     * @param DocumentApproval $approval The approval with an approaching deadline
     */
    public function notifyDeadlineApproaching(DocumentApproval $approval): void {
        $approval->loadMissing(['document', 'reviewer', 'submittedBy']);

        DocumentNotification::create([
            'user_id' => $approval->reviewer_id,
            'type' => DocumentNotification::TYPE_DEADLINE_APPROACHING,
            'title' => 'Review Deadline Approaching',
            'message' => "Your review of '{$approval->document->title}' is due on {$approval->due_date->format('d M Y')}.",
            'url' => route('documents.show', $approval->document),
            'data' => [
                'document_id' => $approval->document_id,
                'approval_id' => $approval->id,
                'due_date' => $approval->due_date->toDateString(),
                'icon' => 'bx-time-five',
                'color' => 'warning',
            ],
        ]);

        try {
            Mail::to($approval->reviewer)->queue(new ReviewDeadlineReminderMail($approval));
        } catch (\Throwable $e) {
            Log::error('Failed to send review deadline reminder email', [
                'approval_id' => $approval->id,
                'reviewer_id' => $approval->reviewer_id,
                'error' => $e->getMessage(),
            ]);
        }

        $approval->update(['reminder_sent_at' => now()]);
    }
}
