<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DiscussionAttachment extends Model {
    protected $table = 'lms_discussion_attachments';

    protected $fillable = [
        'post_id',
        'filename',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function post(): BelongsTo {
        return $this->belongsTo(DiscussionPost::class, 'post_id');
    }

    public function getUrlAttribute(): string {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getFormattedSizeAttribute(): string {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        return number_format($bytes / 1024, 2) . ' KB';
    }

    public function getIsImageAttribute(): bool {
        return str_starts_with($this->mime_type, 'image/');
    }
}
