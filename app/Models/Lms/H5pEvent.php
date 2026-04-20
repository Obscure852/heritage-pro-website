<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class H5pEvent extends Model
{
    use HasFactory;

    protected $table = 'lms_h5p_events';

    public $timestamps = false;

    protected $fillable = [
        'h5p_content_id',
        'student_id',
        'verb',
        'object_type',
        'object_id',
        'result',
        'context',
    ];

    protected $casts = [
        'result' => 'array',
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    // Common xAPI verbs
    public const VERBS = [
        'answered' => 'Answered',
        'attempted' => 'Attempted',
        'completed' => 'Completed',
        'experienced' => 'Experienced',
        'interacted' => 'Interacted',
        'passed' => 'Passed',
        'failed' => 'Failed',
        'progressed' => 'Progressed',
        'scored' => 'Scored',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            $event->created_at = now();
        });
    }

    // Relationships
    public function h5pContent(): BelongsTo
    {
        return $this->belongsTo(H5pContent::class, 'h5p_content_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // Accessors
    public function getVerbDisplayAttribute(): string
    {
        return self::VERBS[$this->verb] ?? ucfirst($this->verb);
    }

    public function getScoreAttribute(): ?int
    {
        return $this->result['score']['raw'] ?? null;
    }

    public function getMaxScoreAttribute(): ?int
    {
        return $this->result['score']['max'] ?? null;
    }

    public function getSuccessAttribute(): ?bool
    {
        return $this->result['success'] ?? null;
    }

    public function getResponseAttribute(): ?string
    {
        return $this->result['response'] ?? null;
    }
}
