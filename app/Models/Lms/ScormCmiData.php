<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormCmiData extends Model
{
    use HasFactory;

    protected $table = 'lms_scorm_cmi_data';

    protected $fillable = [
        'attempt_id',
        'element',
        'value',
    ];

    // Relationships
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ScormAttempt::class, 'attempt_id');
    }
}
