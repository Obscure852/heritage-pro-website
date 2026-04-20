<?php

namespace App\Services\Documents;

use App\Models\DocumentAudit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AuditService {
    /**
     * Action categories grouping related audit actions.
     */
    const ACTION_CATEGORIES = [
        'file_actions' => [
            DocumentAudit::ACTION_CREATED,
            DocumentAudit::ACTION_UPDATED,
            DocumentAudit::ACTION_TRASHED,
            DocumentAudit::ACTION_DELETED,
            DocumentAudit::ACTION_VERSIONED,
            DocumentAudit::ACTION_RENAMED,
            DocumentAudit::ACTION_COPIED,
        ],
        'access' => [
            DocumentAudit::ACTION_VIEWED,
            DocumentAudit::ACTION_DOWNLOADED,
            DocumentAudit::ACTION_PREVIEWED,
            DocumentAudit::ACTION_PUBLIC_ACCESS,
        ],
        'sharing' => [
            DocumentAudit::ACTION_SHARED,
            DocumentAudit::ACTION_UNSHARED,
        ],
        'workflow' => [
            DocumentAudit::ACTION_SUBMITTED,
            DocumentAudit::ACTION_APPROVED,
            DocumentAudit::ACTION_REJECTED,
            DocumentAudit::ACTION_REVISION_REQUESTED,
            DocumentAudit::ACTION_PUBLISHED,
            DocumentAudit::ACTION_ARCHIVED,
            DocumentAudit::ACTION_RESTORED,
        ],
        'organization' => [
            DocumentAudit::ACTION_MOVED,
            DocumentAudit::ACTION_TAG_ADDED,
            DocumentAudit::ACTION_TAG_REMOVED,
            DocumentAudit::ACTION_COMMENT_ADDED,
            DocumentAudit::ACTION_COMMENT_RESOLVED,
        ],
        'security' => [
            DocumentAudit::ACTION_LOCKED,
            DocumentAudit::ACTION_UNLOCKED,
            DocumentAudit::ACTION_LEGAL_HOLD_PLACED,
            DocumentAudit::ACTION_LEGAL_HOLD_REMOVED,
        ],
    ];

    /**
     * Human-readable labels for each category.
     */
    const CATEGORY_LABELS = [
        'file_actions' => 'File Actions',
        'access' => 'Access',
        'sharing' => 'Sharing',
        'workflow' => 'Workflow',
        'organization' => 'Organization',
        'security' => 'Security',
    ];

    /**
     * Human-readable labels for each action.
     */
    const ACTION_LABELS = [
        DocumentAudit::ACTION_CREATED => 'Created',
        DocumentAudit::ACTION_VIEWED => 'Viewed',
        DocumentAudit::ACTION_DOWNLOADED => 'Downloaded',
        DocumentAudit::ACTION_PREVIEWED => 'Previewed',
        DocumentAudit::ACTION_UPDATED => 'Updated',
        DocumentAudit::ACTION_VERSIONED => 'New Version',
        DocumentAudit::ACTION_RENAMED => 'Renamed',
        DocumentAudit::ACTION_MOVED => 'Moved',
        DocumentAudit::ACTION_COPIED => 'Copied',
        DocumentAudit::ACTION_SHARED => 'Shared',
        DocumentAudit::ACTION_UNSHARED => 'Unshared',
        DocumentAudit::ACTION_SUBMITTED => 'Submitted',
        DocumentAudit::ACTION_APPROVED => 'Approved',
        DocumentAudit::ACTION_REJECTED => 'Rejected',
        DocumentAudit::ACTION_REVISION_REQUESTED => 'Revision Requested',
        DocumentAudit::ACTION_PUBLISHED => 'Published',
        DocumentAudit::ACTION_ARCHIVED => 'Archived',
        DocumentAudit::ACTION_RESTORED => 'Restored',
        DocumentAudit::ACTION_TRASHED => 'Trashed',
        DocumentAudit::ACTION_DELETED => 'Deleted',
        DocumentAudit::ACTION_LOCKED => 'Locked',
        DocumentAudit::ACTION_UNLOCKED => 'Unlocked',
        DocumentAudit::ACTION_LEGAL_HOLD_PLACED => 'Legal Hold Placed',
        DocumentAudit::ACTION_LEGAL_HOLD_REMOVED => 'Legal Hold Removed',
        DocumentAudit::ACTION_TAG_ADDED => 'Tag Added',
        DocumentAudit::ACTION_TAG_REMOVED => 'Tag Removed',
        DocumentAudit::ACTION_COMMENT_ADDED => 'Comment Added',
        DocumentAudit::ACTION_COMMENT_RESOLVED => 'Comment Resolved',
        DocumentAudit::ACTION_PUBLIC_ACCESS => 'Public Access',
        DocumentAudit::ACTION_VERSION_RESTORED => 'Version Restored',
    ];

    /**
     * Bootstrap badge color for each action.
     */
    const ACTION_COLORS = [
        DocumentAudit::ACTION_CREATED => 'success',
        DocumentAudit::ACTION_VIEWED => 'info',
        DocumentAudit::ACTION_DOWNLOADED => 'info',
        DocumentAudit::ACTION_PREVIEWED => 'info',
        DocumentAudit::ACTION_UPDATED => 'primary',
        DocumentAudit::ACTION_VERSIONED => 'primary',
        DocumentAudit::ACTION_RENAMED => 'primary',
        DocumentAudit::ACTION_MOVED => 'secondary',
        DocumentAudit::ACTION_COPIED => 'secondary',
        DocumentAudit::ACTION_SHARED => 'warning',
        DocumentAudit::ACTION_UNSHARED => 'warning',
        DocumentAudit::ACTION_SUBMITTED => 'primary',
        DocumentAudit::ACTION_APPROVED => 'success',
        DocumentAudit::ACTION_REJECTED => 'danger',
        DocumentAudit::ACTION_REVISION_REQUESTED => 'warning',
        DocumentAudit::ACTION_PUBLISHED => 'success',
        DocumentAudit::ACTION_ARCHIVED => 'secondary',
        DocumentAudit::ACTION_RESTORED => 'success',
        DocumentAudit::ACTION_TRASHED => 'danger',
        DocumentAudit::ACTION_DELETED => 'danger',
        DocumentAudit::ACTION_LOCKED => 'dark',
        DocumentAudit::ACTION_UNLOCKED => 'dark',
        DocumentAudit::ACTION_LEGAL_HOLD_PLACED => 'danger',
        DocumentAudit::ACTION_LEGAL_HOLD_REMOVED => 'danger',
        DocumentAudit::ACTION_TAG_ADDED => 'secondary',
        DocumentAudit::ACTION_TAG_REMOVED => 'secondary',
        DocumentAudit::ACTION_COMMENT_ADDED => 'info',
        DocumentAudit::ACTION_COMMENT_RESOLVED => 'success',
        DocumentAudit::ACTION_PUBLIC_ACCESS => 'warning',
        DocumentAudit::ACTION_VERSION_RESTORED => 'primary',
    ];

    /**
     * Build a filtered query for audit logs.
     */
    public function buildQuery(array $filters): Builder {
        $query = DocumentAudit::with(['user:id,firstname,lastname', 'document:id,ulid,title'])
            ->select([
                'id',
                'document_id',
                'user_id',
                'action',
                'ip_address',
                'user_agent',
                'metadata',
                'created_at',
            ]);

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['document_id'])) {
            $query->where('document_id', $filters['document_id']);
        }

        if (!empty($filters['category']) && isset(self::ACTION_CATEGORIES[$filters['category']])) {
            $query->whereIn('action', self::ACTION_CATEGORIES[$filters['category']]);
        }

        if (!empty($filters['document_search'])) {
            $query->whereHas('document', function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['document_search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get a human-readable label for an action.
     */
    public function getActionLabel(string $action): string {
        return self::ACTION_LABELS[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }

    /**
     * Get the Bootstrap badge color class for an action.
     */
    public function getActionColor(string $action): string {
        return self::ACTION_COLORS[$action] ?? 'secondary';
    }
}
