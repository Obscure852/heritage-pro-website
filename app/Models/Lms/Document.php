<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $table = 'lms_documents';

    protected $fillable = [
        'content_item_id',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size_bytes',
        'document_type',
        'page_count',
        'preview_images',
        'text_content',
        'allow_download',
    ];

    protected $casts = [
        'preview_images' => 'array',
        'allow_download' => 'boolean',
    ];

    public const ALLOWED_TYPES = [
        'pdf' => ['application/pdf'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'txt' => ['text/plain'],
    ];

    // Relationships
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    // Accessors
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size_bytes;

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

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getIconClassAttribute(): string
    {
        return match ($this->document_type) {
            'pdf' => 'mdi-file-pdf-box text-danger',
            'docx' => 'mdi-file-word-box text-primary',
            'pptx' => 'mdi-file-powerpoint-box text-warning',
            'xlsx' => 'mdi-file-excel-box text-success',
            'txt' => 'mdi-file-document-outline text-secondary',
            default => 'mdi-file-outline text-muted',
        };
    }

    // Helper Methods
    public function isPdf(): bool
    {
        return $this->document_type === 'pdf';
    }

    public function isWord(): bool
    {
        return $this->document_type === 'docx';
    }

    public function isPowerPoint(): bool
    {
        return $this->document_type === 'pptx';
    }

    public function isExcel(): bool
    {
        return $this->document_type === 'xlsx';
    }

    public function canBeViewedInBrowser(): bool
    {
        return in_array($this->document_type, ['pdf', 'txt']);
    }

    public static function detectDocumentType(string $mimeType): string
    {
        foreach (self::ALLOWED_TYPES as $type => $mimes) {
            if (in_array($mimeType, $mimes)) {
                return $type;
            }
        }

        return 'other';
    }

    public static function isAllowedMimeType(string $mimeType): bool
    {
        foreach (self::ALLOWED_TYPES as $mimes) {
            if (in_array($mimeType, $mimes)) {
                return true;
            }
        }

        return false;
    }
}
