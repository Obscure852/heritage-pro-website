<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for granular folder access control.
 *
 * Uses polymorphic permissionable_type/permissionable_id to grant permissions
 * to users, roles, or departments on specific folders.
 *
 * @property int $id
 * @property int $folder_id
 * @property string $permissionable_type
 * @property string $permissionable_id
 * @property string $permission_level
 * @property int $granted_by_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read DocumentFolder $folder
 * @property-read User $grantedBy
 */
class DocumentFolderPermission extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'folder_id',
        'permissionable_type',
        'permissionable_id',
        'permission_level',
        'granted_by_user_id',
    ];

    // ==================== PERMISSION LEVEL CONSTANTS ====================

    /** View permission — can view folder contents. */
    const PERMISSION_VIEW = 'view';

    /** Upload permission — can upload documents to the folder. */
    const PERMISSION_UPLOAD = 'upload';

    /** Edit permission — can modify documents in the folder. */
    const PERMISSION_EDIT = 'edit';

    /** Manage permission — full control including permissions and deletion. */
    const PERMISSION_MANAGE = 'manage';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the folder this permission is for.
     */
    public function folder(): BelongsTo {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    /**
     * Get the user who granted this permission.
     */
    public function grantedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter permissions relevant to a specific user.
     *
     * Matches user-type (direct user ID), role-type (user's role names),
     * and department-type (user's department) permissions.
     */
    public function scopeForUser(Builder $query, User $user): Builder {
        $roleIdentifiers = array_values(array_unique(array_map('strval', array_merge(
            $user->roles()->pluck('roles.id')->toArray(),
            $user->roles()->pluck('roles.name')->toArray()
        ))));

        return $query->where(function (Builder $q) use ($user, $roleIdentifiers) {
            // Direct user permission
            $q->where(function (Builder $sub) use ($user) {
                $sub->where('permissionable_type', 'user')
                    ->where('permissionable_id', (string) $user->id);
            });

            // Role-based permission
            if (!empty($roleIdentifiers)) {
                $q->orWhere(function (Builder $sub) use ($roleIdentifiers) {
                    $sub->where('permissionable_type', 'role')
                        ->whereIn('permissionable_id', $roleIdentifiers);
                });
            }

            // Department-based permission
            if (!empty($user->department)) {
                $q->orWhere(function (Builder $sub) use ($user) {
                    $sub->where('permissionable_type', 'department')
                        ->where('permissionable_id', (string) $user->department);
                });
            }
        });
    }

    // ==================== ACCESSORS ====================

    /**
     * Whether this permission is inherited (always false for stored permissions).
     *
     * Inheritance is resolved at the service level via ancestor chain walk,
     * not stored in the database. Stored permissions are always direct.
     */
    public function getIsInheritedAttribute(): bool {
        return false;
    }
}
