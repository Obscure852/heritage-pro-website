<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable audit trail model for document actions.
 *
 * Records all document actions including views, downloads, edits, shares, and workflow changes.
 * Audit logs are immutable — no updated_at, no SoftDeletes (AUD-03 foundation).
 * Metadata JSON field captures action-specific context.
 *
 * @property int $id
 * @property int $document_id
 * @property int|null $version_id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $session_id
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property-read Document $document
 * @property-read DocumentVersion|null $version
 * @property-read User|null $user
 */
class DocumentAudit extends Model {
    /**
     * Indicates that the model does not have an updated_at column.
     * Audit logs are immutable.
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'version_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'session_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    // ==================== ACTION CONSTANTS ====================

    /** Document was created/uploaded. */
    const ACTION_CREATED = 'created';

    /** Document was viewed. */
    const ACTION_VIEWED = 'viewed';

    /** Document was downloaded. */
    const ACTION_DOWNLOADED = 'downloaded';

    /** Document was previewed (inline viewer). */
    const ACTION_PREVIEWED = 'previewed';

    /** Document metadata was updated. */
    const ACTION_UPDATED = 'updated';

    /** New version was uploaded. */
    const ACTION_VERSIONED = 'versioned';

    /** Document was renamed. */
    const ACTION_RENAMED = 'renamed';

    /** Document was moved to a different folder. */
    const ACTION_MOVED = 'moved';

    /** Document was copied. */
    const ACTION_COPIED = 'copied';

    /** Document was shared. */
    const ACTION_SHARED = 'shared';

    /** Share was revoked. */
    const ACTION_UNSHARED = 'unshared';

    /** Document was submitted for approval. */
    const ACTION_SUBMITTED = 'submitted';

    /** Document was approved. */
    const ACTION_APPROVED = 'approved';

    /** Document was rejected. */
    const ACTION_REJECTED = 'rejected';

    /** Revision was requested. */
    const ACTION_REVISION_REQUESTED = 'revision_requested';

    /** Document was published. */
    const ACTION_PUBLISHED = 'published';

    /** Document was archived. */
    const ACTION_ARCHIVED = 'archived';

    /** Document was restored from archive. */
    const ACTION_RESTORED = 'restored';

    /** Document was soft-deleted (trashed). */
    const ACTION_TRASHED = 'trashed';

    /** Document was permanently deleted. */
    const ACTION_DELETED = 'deleted';

    /** Document was locked for editing. */
    const ACTION_LOCKED = 'locked';

    /** Document lock was released. */
    const ACTION_UNLOCKED = 'unlocked';

    /** Legal hold was placed on document. */
    const ACTION_LEGAL_HOLD_PLACED = 'legal_hold_placed';

    /** Legal hold was removed from document. */
    const ACTION_LEGAL_HOLD_REMOVED = 'legal_hold_removed';

    /** Tag was added to document. */
    const ACTION_TAG_ADDED = 'tag_added';

    /** Tag was removed from document. */
    const ACTION_TAG_REMOVED = 'tag_removed';

    /** Comment was added to document. */
    const ACTION_COMMENT_ADDED = 'comment_added';

    /** Comment was resolved. */
    const ACTION_COMMENT_RESOLVED = 'comment_resolved';

    /** Document was accessed via public link. */
    const ACTION_PUBLIC_ACCESS = 'public_access';

    /** Version was restored as current. */
    const ACTION_VERSION_RESTORED = 'version_restored';

    /** Document submission was withdrawn by author. */
    const ACTION_WITHDRAWN = 'withdrawn';

    /** Document was unpublished (reverted to draft). */
    const ACTION_UNPUBLISHED = 'unpublished';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the document this audit entry is for.
     */
    public function document(): BelongsTo {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the document version this audit entry is for (if applicable).
     */
    public function version(): BelongsTo {
        return $this->belongsTo(DocumentVersion::class, 'version_id');
    }

    /**
     * Get the user who performed this action (null for anonymous/public access).
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
