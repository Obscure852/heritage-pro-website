<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class VideoQuality extends Model
{
    use HasFactory;

    protected $table = 'lms_video_qualities';

    protected $fillable = [
        'video_id',
        'label',
        'width',
        'height',
        'bitrate',
        'codec',
        'container',
        'file_path',
        'file_size',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Relationships
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(VideoChunk::class, 'quality_id');
    }

    // Accessors
    public function getResolutionAttribute(): string
    {
        return $this->width . 'x' . $this->height;
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFormattedBitrateAttribute(): string
    {
        if ($this->bitrate >= 1000) {
            return round($this->bitrate / 1000, 1) . ' Mbps';
        }

        return $this->bitrate . ' kbps';
    }

    // Methods
    public function setAsDefault(): void
    {
        // Remove default from other qualities
        $this->video->qualities()->where('id', '!=', $this->id)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    public function deleteFile(): void
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        // Delete associated chunks
        foreach ($this->chunks as $chunk) {
            $chunk->deleteFile();
            $chunk->delete();
        }
    }
}
