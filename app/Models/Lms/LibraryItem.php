<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryItem extends Model {
    use SoftDeletes;

    protected $table = 'lms_library_items';

    protected $fillable = [
        'title',
        'description',
        'type',
        'mime_type',
        'file_path',
        'file_name',
        'file_size',
        'external_url',
        'thumbnail_path',
        'duration_seconds',
        'metadata',
        'collection_id',
        'visibility',
        'is_template',
        'usage_count',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_template' => 'boolean',
    ];

    public static array $types = [
        'video' => 'Video',
        'document' => 'Document',
        'image' => 'Image',
        'audio' => 'Audio',
        'scorm' => 'SCORM Package',
        'h5p' => 'H5P Content',
        'quiz_template' => 'Quiz Template',
        'assignment_template' => 'Assignment Template',
        'presentation' => 'Presentation',
        'spreadsheet' => 'Spreadsheet',
        'pdf' => 'PDF',
        'archive' => 'Archive',
        'other' => 'Other',
    ];

    public static array $typeIcons = [
        'video' => 'fas fa-video',
        'document' => 'fas fa-file-word',
        'image' => 'fas fa-image',
        'audio' => 'fas fa-music',
        'scorm' => 'fas fa-cube',
        'h5p' => 'fas fa-puzzle-piece',
        'quiz_template' => 'fas fa-question-circle',
        'assignment_template' => 'fas fa-tasks',
        'presentation' => 'fas fa-file-powerpoint',
        'spreadsheet' => 'fas fa-file-excel',
        'pdf' => 'fas fa-file-pdf',
        'archive' => 'fas fa-file-archive',
        'other' => 'fas fa-file',
    ];

    /**
     * Mapping from library item types to compatible content item types.
     */
    public static array $contentTypeMapping = [
        'video' => ['video_youtube', 'video_upload'],
        'document' => ['document'],
        'pdf' => ['document'],
        'presentation' => ['document'],
        'spreadsheet' => ['document'],
        'image' => ['image'],
        'audio' => ['audio'],
        'scorm' => ['scorm12', 'scorm2004'],
        'h5p' => ['h5p'],
        'quiz_template' => ['quiz'],
        'assignment_template' => ['assignment'],
    ];

    /**
     * Reverse mapping from content type to compatible library item types.
     */
    public static array $reverseTypeMapping = [
        'video_youtube' => ['video'],
        'video_upload' => ['video'],
        'document' => ['document', 'pdf', 'presentation', 'spreadsheet'],
        'image' => ['image'],
        'audio' => ['audio'],
        'scorm12' => ['scorm'],
        'scorm2004' => ['scorm'],
        'h5p' => ['h5p'],
        'quiz' => ['quiz_template'],
        'assignment' => ['assignment_template'],
    ];

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function collection(): BelongsTo {
        return $this->belongsTo(LibraryCollection::class, 'collection_id');
    }

    public function tags(): BelongsToMany {
        return $this->belongsToMany(LibraryTag::class, 'lms_library_item_tag', 'item_id', 'tag_id');
    }

    public function shares(): HasMany {
        return $this->hasMany(LibraryItemShare::class, 'item_id');
    }

    public function versions(): HasMany {
        return $this->hasMany(LibraryItemVersion::class, 'item_id');
    }

    public function usages(): HasMany {
        return $this->hasMany(LibraryItemUsage::class, 'item_id');
    }

    public function favorites(): BelongsToMany {
        return $this->belongsToMany(User::class, 'lms_library_favorites', 'item_id', 'user_id');
    }

    /**
     * Get content items that were created from this library item.
     */
    public function contentItems(): HasMany {
        return $this->hasMany(ContentItem::class, 'library_item_id');
    }

    public function scopeAccessibleBy($query, User $user) {
        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('visibility', 'public')
              ->orWhereHas('shares', function ($sq) use ($user) {
                  $sq->where('shareable_type', User::class)
                    ->where('shareable_id', $user->id);
              });
        });
    }

    public function scopeOfType($query, string $type) {
        return $query->where('type', $type);
    }

    public function scopeTemplates($query) {
        return $query->where('is_template', true);
    }

    /**
     * Scope to filter library items compatible with a given content type.
     */
    public function scopeCompatibleWith($query, string $contentType) {
        $compatibleLibraryTypes = self::$reverseTypeMapping[$contentType] ?? [];
        if (empty($compatibleLibraryTypes)) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }
        return $query->whereIn('type', $compatibleLibraryTypes);
    }

    /**
     * Get the content types this library item can be used as.
     */
    public function getCompatibleContentTypes(): array {
        return self::$contentTypeMapping[$this->type] ?? [];
    }

    /**
     * Get the default content type for this library item.
     */
    public function getDefaultContentType(): ?string {
        $types = $this->getCompatibleContentTypes();
        return $types[0] ?? null;
    }

    public function getIconAttribute(): string {
        return self::$typeIcons[$this->type] ?? 'fas fa-file';
    }

    public function getFormattedSizeAttribute(): string {
        if (!$this->file_size) return '-';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getFormattedDurationAttribute(): ?string {
        if (!$this->duration_seconds) return null;

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function incrementUsage(): void {
        $this->increment('usage_count');
    }
}
