<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryItemVersion extends Model {
    protected $table = 'lms_library_item_versions';

    protected $fillable = [
        'item_id',
        'version_number',
        'file_path',
        'file_size',
        'metadata',
        'change_notes',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function item(): BelongsTo {
        return $this->belongsTo(LibraryItem::class, 'item_id');
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedSizeAttribute(): string {
        if (!$this->file_size) return '-';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
