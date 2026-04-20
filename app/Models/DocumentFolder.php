<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Model for document folder organization with hierarchical structure.
 *
 * Supports four repository types (institutional, personal, shared, department)
 * per FLD-06. Uses materialized path and depth columns for efficient hierarchy queries.
 *
 * @property int $id
 * @property string $ulid
 * @property string $name
 * @property string|null $description
 * @property int|null $parent_id
 * @property int $owner_id
 * @property string $repository_type
 * @property int|null $department_id
 * @property string $visibility
 * @property bool $inherit_permissions
 * @property int $sort_order
 * @property string|null $path
 * @property int $depth
 * @property int $document_count
 * @property int $total_size_bytes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read DocumentFolder|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentFolder[] $children
 * @property-read User $owner
 * @property-read Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection|Document[] $documents
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentFolderPermission[] $permissions
 */
class DocumentFolder extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ulid',
        'name',
        'description',
        'parent_id',
        'owner_id',
        'repository_type',
        'department_id',
        'visibility',
        'inherit_permissions',
        'sort_order',
        'path',
        'depth',
        'document_count',
        'total_size_bytes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'inherit_permissions' => 'boolean',
        'sort_order' => 'integer',
        'depth' => 'integer',
        'document_count' => 'integer',
        'total_size_bytes' => 'integer',
    ];

    // ==================== REPOSITORY TYPE CONSTANTS (FLD-06) ====================

    /** Institutional repository — official school documents managed by administrators. */
    const REPOSITORY_INSTITUTIONAL = 'institutional';

    /** Personal repository — private document space for each staff member. */
    const REPOSITORY_PERSONAL = 'personal';

    /** Shared repository — collaborative document space with configurable access. */
    const REPOSITORY_SHARED = 'shared';

    /** Department repository — department-level shared folders. */
    const REPOSITORY_DEPARTMENT = 'department';

    // ==================== VISIBILITY CONSTANTS ====================

    /** Private visibility — only owner and explicitly shared users. */
    const VISIBILITY_PRIVATE = 'private';

    /** Internal visibility — visible to all authenticated users. */
    const VISIBILITY_INTERNAL = 'internal';

    /** Public visibility — accessible without authentication. */
    const VISIBILITY_PUBLIC = 'public';

    // ==================== BOOT ====================

    /**
     * Bootstrap the model and its traits.
     *
     * Automatically generates a ULID when creating a new folder.
     */
    protected static function boot(): void {
        parent::boot();

        static::creating(function ($model) {
            $model->ulid = $model->ulid ?: (string) Str::ulid();
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the parent folder.
     */
    public function parent(): BelongsTo {
        return $this->belongsTo(DocumentFolder::class, 'parent_id');
    }

    /**
     * Get the child folders.
     */
    public function children(): HasMany {
        return $this->hasMany(DocumentFolder::class, 'parent_id');
    }

    /**
     * Get the user who owns this folder.
     */
    public function owner(): BelongsTo {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the department this folder belongs to.
     */
    public function department(): BelongsTo {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the documents in this folder.
     */
    public function documents(): HasMany {
        return $this->hasMany(Document::class, 'folder_id');
    }

    /**
     * Get the permissions for this folder.
     */
    public function permissions(): HasMany {
        return $this->hasMany(DocumentFolderPermission::class, 'folder_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter folders by repository type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRepository($query, string $type) {
        return $query->where('repository_type', $type);
    }

    /**
     * Scope to filter folders by owner.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOwner($query, int $userId) {
        return $query->where('owner_id', $userId);
    }

    /**
     * Scope to filter root-level folders (no parent).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRoot($query) {
        return $query->whereNull('parent_id');
    }
}
