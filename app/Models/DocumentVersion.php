<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for document version history.
 *
 * Each version stores its own file storage reference, allowing any version to be restored.
 * Version numbers follow Major.Minor format (e.g., 1.0, 1.1, 2.0).
 * No updated_at column — versions are immutable once created.
 *
 * @property int $id
 * @property int $document_id
 * @property string $version_number
 * @property string $version_type
 * @property string $storage_disk
 * @property string $storage_path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size_bytes
 * @property string $checksum_sha256
 * @property string|null $version_notes
 * @property int $uploaded_by_user_id
 * @property bool $is_current
 * @property \Carbon\Carbon|null $created_at
 * @property-read Document $document
 * @property-read User $uploadedBy
 */
class DocumentVersion extends Model {
    /**
     * Indicates that the model does not have an updated_at column.
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'version_number',
        'version_type',
        'storage_disk',
        'storage_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'checksum_sha256',
        'version_notes',
        'uploaded_by_user_id',
        'is_current',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_current' => 'boolean',
        'size_bytes' => 'integer',
    ];

    // ==================== VERSION TYPE CONSTANTS ====================

    /** Major version type — significant changes (e.g., 1.0 -> 2.0). */
    const TYPE_MAJOR = 'major';

    /** Minor version type — small changes (e.g., 1.0 -> 1.1). */
    const TYPE_MINOR = 'minor';

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the document this version belongs to.
     */
    public function document(): BelongsTo {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who uploaded this version.
     */
    public function uploadedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    // ==================== VERSION HELPERS ====================

    /**
     * Calculate the next version number based on current version and type.
     *
     * Major: increments major part, resets minor to 0 (e.g., 1.3 -> 2.0)
     * Minor: increments minor part (e.g., 1.3 -> 1.4)
     *
     * @param string $currentVersion The current version string (e.g., '1.3')
     * @param string $type The version type ('major' or 'minor')
     * @return string The next version number
     */
    public static function calculateNextVersion(string $currentVersion, string $type): string {
        $parts = explode('.', $currentVersion);
        $major = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : 1;
        $minor = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : 0;

        if ($type === self::TYPE_MAJOR) {
            return ($major + 1) . '.0';
        }

        return $major . '.' . ($minor + 1);
    }
}
