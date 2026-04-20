<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VideoChunk extends Model
{
    use HasFactory;

    protected $table = 'lms_video_chunks';

    protected $fillable = [
        'video_id',
        'quality_id',
        'sequence',
        'duration',
        'file_path',
        'file_size',
    ];

    protected $casts = [
        'duration' => 'decimal:3',
    ];

    // Relationships
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function quality(): BelongsTo
    {
        return $this->belongsTo(VideoQuality::class, 'quality_id');
    }

    // Accessors
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    // Methods
    public function deleteFile(): void
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
    }
}
