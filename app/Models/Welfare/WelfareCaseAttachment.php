<?php

namespace App\Models\Welfare;

use App\Models\User;
use App\Traits\Welfare\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Welfare case attachment model.
 *
 * Manages file attachments for welfare cases.
 *
 * @property int $id
 * @property int $welfare_case_id
 * @property int $uploaded_by
 * @property string $file_name
 * @property string $original_name
 * @property string $file_path
 * @property string $file_type
 * @property int $file_size
 * @property string|null $description
 * @property string $category
 * @property bool $is_confidential
 */
class WelfareCaseAttachment extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'welfare_case_id',
        'uploaded_by',
        'file_name',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'category',
        'is_confidential',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_confidential' => 'boolean',
    ];

    // Category constants
    public const CATEGORY_DOCUMENT = 'document';
    public const CATEGORY_IMAGE = 'image';
    public const CATEGORY_REPORT = 'report';
    public const CATEGORY_EVIDENCE = 'evidence';
    public const CATEGORY_CONSENT_FORM = 'consent_form';
    public const CATEGORY_MEDICAL = 'medical';
    public const CATEGORY_OTHER = 'other';

    // File type groups for validation
    public const ALLOWED_DOCUMENT_TYPES = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
    public const ALLOWED_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    public const ALLOWED_SPREADSHEET_TYPES = ['xls', 'xlsx', 'csv'];

    // ==================== RELATIONSHIPS ====================

    public function welfareCase()
    {
        return $this->belongsTo(WelfareCase::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ==================== SCOPES ====================

    public function scopeConfidential(Builder $query): Builder
    {
        return $query->where('is_confidential', true);
    }

    public function scopeNonConfidential(Builder $query): Builder
    {
        return $query->where('is_confidential', false);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeDocuments(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_DOCUMENT);
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_IMAGE);
    }

    public function scopeEvidence(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_EVIDENCE);
    }

    public function scopeByUploader(Builder $query, int $userId): Builder
    {
        return $query->where('uploaded_by', $userId);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== HELPER METHODS ====================

    public function isConfidential(): bool
    {
        return $this->is_confidential;
    }

    public function isImage(): bool
    {
        return in_array(strtolower($this->file_type), self::ALLOWED_IMAGE_TYPES);
    }

    public function isDocument(): bool
    {
        return in_array(strtolower($this->file_type), self::ALLOWED_DOCUMENT_TYPES);
    }

    public function isPdf(): bool
    {
        return strtolower($this->file_type) === 'pdf';
    }

    /**
     * Get the file URL.
     */
    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the file download URL.
     */
    public function getDownloadUrl(): string
    {
        return route('welfare.attachments.download', $this);
    }

    /**
     * Check if file exists.
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Delete the physical file.
     */
    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return Storage::delete($this->file_path);
        }

        return true;
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Get file icon based on type for UI.
     */
    public function getFileIconAttribute(): string
    {
        $extension = strtolower($this->file_type);

        return match (true) {
            in_array($extension, ['pdf']) => 'file-text',
            in_array($extension, ['doc', 'docx']) => 'file-text',
            in_array($extension, ['xls', 'xlsx', 'csv']) => 'file-spreadsheet',
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp']) => 'image',
            in_array($extension, ['txt', 'rtf']) => 'file',
            default => 'file',
        };
    }

    /**
     * Get category badge color for UI.
     */
    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_DOCUMENT => 'blue',
            self::CATEGORY_IMAGE => 'purple',
            self::CATEGORY_REPORT => 'green',
            self::CATEGORY_EVIDENCE => 'red',
            self::CATEGORY_CONSENT_FORM => 'yellow',
            self::CATEGORY_MEDICAL => 'orange',
            self::CATEGORY_OTHER => 'gray',
            default => 'gray',
        };
    }

    /**
     * Boot the model to handle file deletion.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Delete physical file when model is force deleted
        static::forceDeleting(function (self $model) {
            $model->deleteFile();
        });
    }
}
