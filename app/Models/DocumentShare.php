<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for document sharing with users, roles, departments, or via public links.
 *
 * Uses polymorphic shareable_type/shareable_id for flexible share targets.
 * Public links use access_token with optional password protection and view limits.
 *
 * @property int $id
 * @property int $document_id
 * @property string $shareable_type
 * @property string|null $shareable_id
 * @property string $permission_level
 * @property int $shared_by_user_id
 * @property string|null $message
 * @property string|null $access_token
 * @property string|null $password_hash
 * @property bool $allow_download
 * @property int|null $max_views
 * @property int $view_count
 * @property \Carbon\Carbon|null $expires_at
 * @property bool $is_active
 * @property \Carbon\Carbon|null $revoked_at
 * @property int|null $revoked_by_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Document $document
 * @property-read User $sharedBy
 * @property-read User|null $revokedBy
 */
class DocumentShare extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'shareable_type',
        'shareable_id',
        'permission_level',
        'shared_by_user_id',
        'message',
        'access_token',
        'password_hash',
        'allow_download',
        'max_views',
        'view_count',
        'expires_at',
        'is_active',
        'revoked_at',
        'revoked_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_active' => 'boolean',
        'allow_download' => 'boolean',
        'view_count' => 'integer',
        'max_views' => 'integer',
    ];

    // ==================== PERMISSION LEVEL CONSTANTS ====================

    /** View permission — can view and preview the document. */
    const PERMISSION_VIEW = 'view';

    /** Comment permission — can view and add comments. */
    const PERMISSION_COMMENT = 'comment';

    /** Edit permission — can view, comment, and upload new versions. */
    const PERMISSION_EDIT = 'edit';

    /** Manage permission — full control including sharing and deletion. */
    const PERMISSION_MANAGE = 'manage';

    // ==================== SHAREABLE TYPE CONSTANTS ====================

    /** Shared with a specific user. */
    const TYPE_USER = 'user';

    /** Shared with a role. */
    const TYPE_ROLE = 'role';

    /** Shared with a department. */
    const TYPE_DEPARTMENT = 'department';

    /** Shared via public link (shareable_id is null). */
    const TYPE_PUBLIC_LINK = 'public_link';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the document that is shared.
     */
    public function document(): BelongsTo {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who created this share.
     */
    public function sharedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    /**
     * Get the user who revoked this share.
     */
    public function revokedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }
}
