<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model {
    protected $table = 'lms_message_attachments';

    protected $fillable = [
        'message_id',
        'filename',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function message(): BelongsTo {
        return $this->belongsTo(DirectMessage::class, 'message_id');
    }

    public function getUrlAttribute(): string {
        return Storage::url($this->file_path);
    }

    public function getFormattedSizeAttribute(): string {
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

    public function isImage(): bool {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool {
        return $this->mime_type === 'application/pdf';
    }

    public function getIconClassAttribute(): string {
        return match (true) {
            $this->isImage() => 'fas fa-image',
            $this->isPdf() => 'fas fa-file-pdf',
            str_contains($this->mime_type, 'word') => 'fas fa-file-word',
            str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet') => 'fas fa-file-excel',
            str_contains($this->mime_type, 'powerpoint') || str_contains($this->mime_type, 'presentation') => 'fas fa-file-powerpoint',
            str_contains($this->mime_type, 'zip') || str_contains($this->mime_type, 'archive') => 'fas fa-file-archive',
            str_contains($this->mime_type, 'video') => 'fas fa-file-video',
            str_contains($this->mime_type, 'audio') => 'fas fa-file-audio',
            default => 'fas fa-file',
        };
    }
}
