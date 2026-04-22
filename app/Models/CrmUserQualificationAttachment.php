<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmUserQualificationAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'qualification_id',
        'uploaded_by_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
    ];

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(CrmUserQualification::class, 'qualification_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function iconClass(): string
    {
        return match (strtolower((string) $this->extension)) {
            'pdf' => 'fas fa-file-pdf',
            'doc', 'docx' => 'fas fa-file-word',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'fas fa-file-image',
            default => 'fas fa-file-alt',
        };
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

    public function extensionLabel(): string
    {
        return strtoupper((string) ($this->extension ?: pathinfo($this->original_name, PATHINFO_EXTENSION) ?: 'file'));
    }
}
