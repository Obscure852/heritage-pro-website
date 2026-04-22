<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmImportRunRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_run_id',
        'row_number',
        'normalized_key',
        'action',
        'payload',
        'validation_errors',
        'record_id',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'validation_errors' => 'array',
        'processed_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(CrmImportRun::class, 'import_run_id');
    }
}
