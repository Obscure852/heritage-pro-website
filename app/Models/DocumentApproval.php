<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for the document approval workflow.
 *
 * Tracks submission, review, and approval status for each document version.
 * Supports multi-level approval with workflow_step ordering.
 *
 * @property int $id
 * @property int $document_id
 * @property int $version_id
 * @property int $workflow_step
 * @property int $reviewer_id
 * @property string $status
 * @property int $submitted_by_user_id
 * @property string|null $submission_notes
 * @property \Carbon\Carbon $submitted_at
 * @property string|null $review_comments
 * @property \Carbon\Carbon|null $reviewed_at
 * @property \Carbon\Carbon|null $due_date
 * @property \Carbon\Carbon|null $reminder_sent_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Document $document
 * @property-read DocumentVersion $version
 * @property-read User $reviewer
 * @property-read User $submittedBy
 */
class DocumentApproval extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'version_id',
        'workflow_step',
        'reviewer_id',
        'status',
        'submitted_by_user_id',
        'submission_notes',
        'submitted_at',
        'review_comments',
        'reviewed_at',
        'due_date',
        'reminder_sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'due_date' => 'date',
        'workflow_step' => 'integer',
    ];

    // ==================== STATUS CONSTANTS ====================

    /** Pending status — waiting for reviewer to begin review. */
    const STATUS_PENDING = 'pending';

    /** In review status — reviewer is actively reviewing. */
    const STATUS_IN_REVIEW = 'in_review';

    /** Approved status — reviewer approved the document. */
    const STATUS_APPROVED = 'approved';

    /** Rejected status — reviewer rejected the document. */
    const STATUS_REJECTED = 'rejected';

    /** Revision required status — reviewer requested changes. */
    const STATUS_REVISION_REQUIRED = 'revision_required';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the document this approval is for.
     */
    public function document(): BelongsTo {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the document version this approval is for.
     */
    public function version(): BelongsTo {
        return $this->belongsTo(DocumentVersion::class, 'version_id');
    }

    /**
     * Get the reviewer assigned to this approval.
     */
    public function reviewer(): BelongsTo {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the user who submitted this document for review.
     */
    public function submittedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }
}
