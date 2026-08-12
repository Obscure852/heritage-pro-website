<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmClientSetupStageProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'stage_key',
        'status',
        'validation_errors',
        'validation_error_details',
        'completed_at',
        'last_saved_at',
    ];

    protected $casts = [
        'validation_errors' => 'array',
        'validation_error_details' => 'array',
        'completed_at' => 'datetime',
        'last_saved_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CrmClientSetupSubmission::class, 'submission_id');
    }
}
