<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssignmentAttachment extends Model
{
    use HasFactory;

    protected $table = 'lms_assignment_attachments';

    public const MAX_ATTACHMENTS = 5;
    public const MAX_FILE_SIZE_MB = 20;
    public const ALLOWED_MIMES = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'rtf', 'odt', 'ods', 'odp',
        'jpg', 'jpeg', 'png', 'gif',
        'zip', 'rar', '7z',
        'mp3', 'mp4', 'wav',
    ];

    protected $fillable = [
        'assignment_id',
        'label',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'sort_order',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (AssignmentAttachment $attachment) {
            if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        });
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
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

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    public function getIconClassAttribute(): string
    {
        return match ($this->extension) {
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc', 'docx' => 'fas fa-file-word text-primary',
            'xls', 'xlsx' => 'fas fa-file-excel text-success',
            'ppt', 'pptx' => 'fas fa-file-powerpoint text-warning',
            'zip', 'rar', '7z' => 'fas fa-file-archive text-secondary',
            'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image text-info',
            'mp4', 'avi', 'mov' => 'fas fa-file-video text-danger',
            'mp3', 'wav' => 'fas fa-file-audio text-purple',
            'txt', 'rtf' => 'fas fa-file-alt text-muted',
            default => 'fas fa-file text-muted',
        };
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->label ?: $this->original_name;
    }
}
