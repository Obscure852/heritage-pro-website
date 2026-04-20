<?php

namespace App\Models\Invigilation;

use App\Models\GradeSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvigilationSession extends Model
{
    use HasFactory;

    protected $table = 'invigilation_sessions';

    protected $fillable = [
        'series_id',
        'grade_subject_id',
        'paper_label',
        'exam_date',
        'start_time',
        'end_time',
        'day_of_cycle',
        'notes',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'day_of_cycle' => 'integer',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(InvigilationSeries::class, 'series_id');
    }

    public function gradeSubject(): BelongsTo
    {
        return $this->belongsTo(GradeSubject::class, 'grade_subject_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(InvigilationSessionRoom::class, 'session_id')->orderBy('id');
    }

    public function getDisplayNameAttribute(): string
    {
        $subjectName = $this->gradeSubject?->subject?->name ?? 'Subject';
        $paperLabel = trim((string) $this->paper_label);

        return $paperLabel !== '' ? "{$subjectName} - {$paperLabel}" : $subjectName;
    }
}
