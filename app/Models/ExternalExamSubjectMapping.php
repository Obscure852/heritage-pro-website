<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalExamSubjectMapping extends Model{
    use HasFactory;

    protected $fillable = [
        'school_type',
        'exam_type',
        'source_key',
        'source_code',
        'source_label',
        'subject_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subject(): BelongsTo{
        return $this->belongsTo(Subject::class);
    }
}
