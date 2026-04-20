<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Policies\DocumentPolicy;

/**
 * Core document model for the Document Management System.
 *
 * Represents a single document with metadata, storage references, status workflow,
 * version control, and flags. Uses ULID for public-facing identifiers (DOC-09).
 *
 * @property int $id
 * @property string $ulid
 * @property string $title
 * @property string|null $description
 * @property string $source_type
 * @property string|null $external_url
 * @property string|null $storage_disk
 * @property string|null $storage_path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property string|null $extension
 * @property int|null $size_bytes
 * @property string|null $checksum_sha256
 * @property int|null $folder_id
 * @property int|null $category_id
 * @property int $owner_id
 * @property string $status
 * @property string $visibility
 * @property string $current_version
 * @property int $version_count
 * @property \Carbon\Carbon|null $effective_date
 * @property \Carbon\Carbon|null $expiry_date
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon|null $archived_at
 * @property bool $is_featured
 * @property bool $is_template
 * @property bool $is_locked
 * @property int|null $locked_by_user_id
 * @property \Carbon\Carbon|null $locked_at
 * @property bool $legal_hold
 * @property string|null $legal_hold_reason
 * @property int|null $legal_hold_by_user_id
 * @property \Carbon\Carbon|null $legal_hold_at
 * @property int $download_count
 * @property int $view_count
 * @property \Carbon\Carbon|null $content_indexed_at
 * @property string|null $content_text
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read DocumentFolder|null $folder
 * @property-read DocumentCategory|null $category
 * @property-read User $owner
 * @property-read User|null $lockedBy
 * @property-read User|null $legalHoldBy
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentVersion[] $versions
 * @property-read DocumentVersion|null $currentVersion
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentTag[] $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentShare[] $shares
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentApproval[] $approvals
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentAudit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentComment[] $comments
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $favoritedBy
 */
class Document extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ulid',
        'title',
        'description',
        'source_type',
        'external_url',
        'storage_disk',
        'storage_path',
        'original_name',
        'mime_type',
        'extension',
        'size_bytes',
        'checksum_sha256',
        'folder_id',
        'category_id',
        'owner_id',
        'status',
        'visibility',
        'current_version',
        'version_count',
        'effective_date',
        'expiry_date',
        'published_at',
        'archived_at',
        'is_featured',
        'is_template',
        'is_locked',
        'locked_by_user_id',
        'locked_at',
        'legal_hold',
        'legal_hold_reason',
        'legal_hold_by_user_id',
        'legal_hold_at',
        'expiry_warning_sent_at',
        'grace_period_notification_sent_at',
        'download_count',
        'view_count',
        'content_indexed_at',
        'content_text',
        'published_roles',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
        'locked_at' => 'datetime',
        'legal_hold_at' => 'datetime',
        'expiry_warning_sent_at' => 'datetime',
        'grace_period_notification_sent_at' => 'datetime',
        'content_indexed_at' => 'datetime',
        'published_roles' => 'array',
        'is_featured' => 'boolean',
        'is_template' => 'boolean',
        'is_locked' => 'boolean',
        'legal_hold' => 'boolean',
        'size_bytes' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'version_count' => 'integer',
    ];

    /** Upload-backed document stored on the local document disk. */
    const SOURCE_UPLOAD = 'upload';

    /** URL-backed document resolved by redirecting to a remote source. */
    const SOURCE_EXTERNAL_URL = 'external_url';

    // ==================== STATUS CONSTANTS ====================

    /** Draft status — initial state when a document is created. */
    const STATUS_DRAFT = 'draft';

    /** Pending review status — submitted for approval. */
    const STATUS_PENDING_REVIEW = 'pending_review';

    /** Under review status — reviewer is actively reviewing. */
    const STATUS_UNDER_REVIEW = 'under_review';

    /** Revision required status — reviewer requested changes. */
    const STATUS_REVISION_REQUIRED = 'revision_required';

    /** Approved status — document has been approved. */
    const STATUS_APPROVED = 'approved';

    /** Published status — document is publicly available. */
    const STATUS_PUBLISHED = 'published';

    /** Archived status — document has been archived. */
    const STATUS_ARCHIVED = 'archived';

    // ==================== VISIBILITY CONSTANTS ====================

    /** Private visibility — only owner and explicitly shared users. */
    const VISIBILITY_PRIVATE = 'private';

    /** Internal visibility — visible to all authenticated users. */
    const VISIBILITY_INTERNAL = 'internal';

    /** Public visibility — accessible without authentication. */
    const VISIBILITY_PUBLIC = 'public';

    /** Role visibility — only users in selected roles can access. */
    const VISIBILITY_ROLES = 'roles';

    // ==================== BOOT ====================

    /**
     * Bootstrap the model and its traits.
     *
     * Automatically generates a ULID when creating a new document (DOC-09).
     */
    protected static function boot(): void {
        parent::boot();

        static::creating(function ($model) {
            $model->ulid = $model->ulid ?: (string) Str::ulid();
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the folder this document belongs to.
     */
    public function folder(): BelongsTo {
        return $this->belongsTo(DocumentFolder::class);
    }

    /**
     * Get the category this document belongs to.
     */
    public function category(): BelongsTo {
        return $this->belongsTo(DocumentCategory::class);
    }

    /**
     * Get the user who owns this document.
     */
    public function owner(): BelongsTo {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the user who locked this document.
     */
    public function lockedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'locked_by_user_id');
    }

    /**
     * Get the user who placed the legal hold on this document.
     */
    public function legalHoldBy(): BelongsTo {
        return $this->belongsTo(User::class, 'legal_hold_by_user_id');
    }

    /**
     * Get all versions of this document.
     */
    public function versions(): HasMany {
        return $this->hasMany(DocumentVersion::class);
    }

    /**
     * Get the current version of this document.
     */
    public function currentVersion(): HasOne {
        return $this->hasOne(DocumentVersion::class)->where('is_current', true);
    }

    /**
     * Get the tags assigned to this document.
     */
    public function tags(): BelongsToMany {
        return $this->belongsToMany(DocumentTag::class, 'document_tag', 'document_id', 'tag_id')
            ->withPivot('tagged_by_user_id', 'created_at');
    }

    /**
     * Get the shares for this document.
     */
    public function shares(): HasMany {
        return $this->hasMany(DocumentShare::class);
    }

    /**
     * Get the approval records for this document.
     */
    public function approvals(): HasMany {
        return $this->hasMany(DocumentApproval::class);
    }

    /**
     * Get the audit trail for this document.
     */
    public function audits(): HasMany {
        return $this->hasMany(DocumentAudit::class);
    }

    /**
     * Get the comments on this document.
     */
    public function comments(): HasMany {
        return $this->hasMany(DocumentComment::class);
    }

    /**
     * Get the users who have favorited this document.
     */
    public function favoritedBy(): BelongsToMany {
        return $this->belongsToMany(User::class, 'document_favorites', 'document_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Whether this document is backed by an uploaded local file.
     */
    public function isUploadedFile(): bool {
        return $this->source_type !== self::SOURCE_EXTERNAL_URL;
    }

    /**
     * Whether this document is backed by an external URL.
     */
    public function isExternalUrl(): bool {
        return $this->source_type === self::SOURCE_EXTERNAL_URL;
    }

    /**
     * Whether this document can participate in local versioning.
     */
    public function supportsVersioning(): bool {
        return $this->isUploadedFile();
    }

    /**
     * Whether this document has a local stored file on the document disk.
     */
    public function hasStoredFile(): bool {
        return $this->isUploadedFile() && filled($this->storage_path);
    }

    /**
     * Human-friendly source label for views.
     */
    public function sourceLabel(): string {
        return $this->isExternalUrl() ? 'Remote URL' : 'Uploaded File';
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter documents by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status) {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter documents by owner.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOwner($query, int $userId) {
        return $query->where('owner_id', $userId);
    }

    /**
     * Scope to filter documents by folder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $folderId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFolder($query, int $folderId) {
        return $query->where('folder_id', $folderId);
    }

    /**
     * Scope to filter published documents only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query) {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope to exclude archived documents.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotArchived($query) {
        return $query->where('status', '!=', self::STATUS_ARCHIVED);
    }

    /**
     * Scope to filter documents visible to the given user.
     *
     * Translates DocumentPolicy view logic into SQL WHERE clauses
     * for performant search filtering (avoids post-filtering in PHP).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleTo($query, User $user) {
        if (DocumentPolicy::isAdmin($user)) {
            return $query; // Admin sees all
        }

        $roleIds = $user->roles()->pluck('roles.id')->map(fn ($id) => (int) $id)->toArray();
        $roleNames = $user->roles()->pluck('roles.name')->toArray();
        $shareRoleIdentifiers = array_values(array_unique(array_map('strval', array_merge($roleIds, $roleNames))));

        return $query->where(function ($q) use ($user, $roleIds, $roleNames, $shareRoleIdentifiers) {
            $q->where('documents.owner_id', $user->id)
                ->orWhere(function ($publishedQuery) use ($roleIds, $roleNames) {
                    $publishedQuery
                        ->where('documents.status', self::STATUS_PUBLISHED)
                        ->where(function ($visibilityQuery) use ($roleIds, $roleNames) {
                            $visibilityQuery
                                ->where('documents.visibility', self::VISIBILITY_PUBLIC)
                                ->orWhere('documents.visibility', self::VISIBILITY_INTERNAL);

                            if (!empty($roleIds) || !empty($roleNames)) {
                                $visibilityQuery->orWhere(function ($rolesQuery) use ($roleIds, $roleNames) {
                                    $rolesQuery
                                        ->where('documents.visibility', self::VISIBILITY_ROLES)
                                        ->where(function ($jsonRolesQuery) use ($roleIds, $roleNames) {
                                            foreach ($roleIds as $roleId) {
                                                $jsonRolesQuery->orWhereJsonContains('documents.published_roles', $roleId);
                                            }
                                            foreach ($roleNames as $roleName) {
                                                $jsonRolesQuery->orWhereJsonContains('documents.published_roles', $roleName);
                                            }
                                        });
                                });
                            }
                        });
                })
                ->orWhereExists(function ($shareQuery) use ($user, $shareRoleIdentifiers) {
                    $shareQuery->select(DB::raw(1))
                        ->from('document_shares')
                        ->whereColumn('document_shares.document_id', 'documents.id')
                        ->where('document_shares.is_active', true)
                        ->whereNull('document_shares.revoked_at')
                        ->where(function ($targetQuery) use ($user, $shareRoleIdentifiers) {
                            $targetQuery->where(function ($userTarget) use ($user) {
                                $userTarget
                                    ->where('document_shares.shareable_type', DocumentShare::TYPE_USER)
                                    ->where('document_shares.shareable_id', (string) $user->id);
                            });

                            if (!empty($shareRoleIdentifiers)) {
                                $targetQuery->orWhere(function ($roleTarget) use ($shareRoleIdentifiers) {
                                    $roleTarget
                                        ->where('document_shares.shareable_type', DocumentShare::TYPE_ROLE)
                                        ->whereIn('document_shares.shareable_id', $shareRoleIdentifiers);
                                });
                            }

                            if (!empty($user->department)) {
                                $targetQuery->orWhere(function ($departmentTarget) use ($user) {
                                    $departmentTarget
                                        ->where('document_shares.shareable_type', DocumentShare::TYPE_DEPARTMENT)
                                        ->where('document_shares.shareable_id', (string) $user->department);
                                });
                            }
                        });
                });
        });
    }
}
