<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CrmImportRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'entity',
        'status',
        'initiated_by_id',
        'disk',
        'path',
        'original_filename',
        'file_checksum',
        'preview_summary',
        'total_count',
        'created_count',
        'updated_count',
        'skipped_count',
        'failed_count',
        'passwords_payload',
        'passwords_downloaded_at',
        'last_error',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'preview_summary' => 'array',
        'passwords_downloaded_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $run) {
            if (! $run->uuid) {
                $run->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(CrmImportRunRow::class, 'import_run_id');
    }

    public function hasPasswordResults(): bool
    {
        return is_string($this->passwords_payload) && $this->passwords_payload !== '' && $this->passwords_downloaded_at === null;
    }

    public function hasFailures(): bool
    {
        return $this->failed_count > 0 || $this->rows()->whereNotNull('validation_errors')->exists();
    }
}
