<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionMessageAttachment extends Model
{
    use HasFactory;

    protected $table = 'crm_discussion_message_attachments';

    protected $fillable = [
        'message_id',
        'uploaded_by_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(DiscussionMessage::class, 'message_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function isImage(): bool
    {
        return in_array(strtolower((string) $this->extension), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    public function isPdf(): bool
    {
        return strtolower((string) $this->extension) === 'pdf';
    }

    public function isDocx(): bool
    {
        return strtolower((string) $this->extension) === 'docx';
    }

    public function formattedSize(): string
    {
        $size = (float) $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        $precision = $unitIndex === 0 ? 0 : 1;

        return number_format($size, $precision) . ' ' . $units[$unitIndex];
    }

    public function iconClass(): string
    {
        return match (strtolower((string) $this->extension)) {
            'jpg', 'jpeg', 'png', 'webp', 'gif' => 'fas fa-file-image',
            'pdf' => 'fas fa-file-pdf',
            'doc', 'docx' => 'fas fa-file-word',
            default => 'fas fa-file-alt',
        };
    }
}
