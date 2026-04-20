<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * In-app notification model for document workflow events.
 *
 * Tracks notifications sent to users at each document workflow stage
 * (submission, approval, rejection, revision, publication, deadline).
 * Immutable records — only read_at is updated after creation.
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property array|null $data
 * @property string|null $url
 * @property \Carbon\Carbon|null $read_at
 * @property \Carbon\Carbon $created_at
 * @property-read User $user
 */
class DocumentNotification extends Model {
    /**
     * Disable updated_at — notifications are immutable audit-like records.
     */
    const UPDATED_AT = null;

    // ==================== TYPE CONSTANTS ====================

    /** Notification when a document is submitted for review. */
    const TYPE_SUBMITTED = 'document_submitted';

    /** Notification when a document is approved. */
    const TYPE_APPROVED = 'document_approved';

    /** Notification when a document is rejected. */
    const TYPE_REJECTED = 'document_rejected';

    /** Notification when a reviewer requests revisions. */
    const TYPE_REVISION_REQUESTED = 'document_revision_requested';

    /** Notification when a document is published. */
    const TYPE_PUBLISHED = 'document_published';

    /** Notification when a review deadline is approaching. */
    const TYPE_DEADLINE_APPROACHING = 'review_deadline_approaching';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'url',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user this notification belongs to.
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter unread notifications only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query) {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to filter notifications for a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId) {
        return $query->where('user_id', $userId);
    }

    // ==================== METHODS ====================

    /**
     * Mark this notification as read.
     */
    public function markAsRead(): void {
        if (is_null($this->read_at)) {
            $this->read_at = now();
            $this->save();
        }
    }
}
