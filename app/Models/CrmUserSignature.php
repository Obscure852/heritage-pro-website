<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmUserSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uploaded_by_id',
        'label',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function iconClass(): string
    {
        return match (strtolower((string) $this->extension)) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'fas fa-signature',
            'pdf' => 'fas fa-file-pdf',
            default => 'fas fa-file-signature',
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
}
