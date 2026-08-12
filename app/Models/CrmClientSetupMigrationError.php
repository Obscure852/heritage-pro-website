<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmClientSetupMigrationError extends Model
{
    use HasFactory;

    protected $fillable = [
        'migration_upload_id',
        'row_number',
        'messages',
    ];

    protected $casts = [
        'row_number' => 'integer',
        'messages' => 'array',
    ];

    public function migrationUpload(): BelongsTo
    {
        return $this->belongsTo(CrmClientSetupMigrationUpload::class, 'migration_upload_id');
    }
}
