<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScormAttempt extends Model
{
    use HasFactory;

    protected $table = 'lms_scorm_attempts';

    protected $fillable = [
        'package_id',
        'content_item_id',
        'student_id',
        'attempt_number',
        'lesson_status',
        'exit_status',
        'entry',
        'credit',
        'score_raw',
        'score_min',
        'score_max',
        'score_scaled',
        'progress_measure',
        'completion_status',
        'success_status',
        'total_time',
        'session_time',
        'started_at',
        'last_accessed_at',
        'completed_at',
        'suspend_data',
        'location',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'completed_at' => 'datetime',
        'score_raw' => 'decimal:2',
        'score_min' => 'decimal:2',
        'score_max' => 'decimal:2',
        'score_scaled' => 'decimal:4',
    ];

    // SCORM 1.2 Lesson Status values
    public const LESSON_STATUSES_12 = [
        'passed',
        'failed',
        'completed',
        'incomplete',
        'browsed',
        'not attempted',
    ];

    // SCORM 2004 Completion Status values
    public const COMPLETION_STATUSES = [
        'completed',
        'incomplete',
        'not attempted',
        'unknown',
    ];

    // SCORM 2004 Success Status values
    public const SUCCESS_STATUSES = [
        'passed',
        'failed',
        'unknown',
    ];

    // Relationships
    public function package(): BelongsTo
    {
        return $this->belongsTo(ScormPackage::class, 'package_id');
    }

    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function cmiData(): HasMany
    {
        return $this->hasMany(ScormCmiData::class, 'attempt_id');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ScormInteraction::class, 'attempt_id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(ScormObjective::class, 'attempt_id');
    }

    // Accessors
    public function getIsCompleteAttribute(): bool
    {
        if ($this->package->is_scorm_12) {
            return in_array($this->lesson_status, ['passed', 'failed', 'completed']);
        }

        return $this->completion_status === 'completed';
    }

    public function getIsPassedAttribute(): bool
    {
        if ($this->package->is_scorm_12) {
            return $this->lesson_status === 'passed';
        }

        return $this->success_status === 'passed';
    }

    public function getScorePercentageAttribute(): ?float
    {
        if ($this->package->is_scorm_2004 && $this->score_scaled !== null) {
            return round($this->score_scaled * 100, 2);
        }

        if ($this->score_raw !== null && $this->score_max) {
            return round(($this->score_raw / $this->score_max) * 100, 2);
        }

        return null;
    }

    // Methods
    public function setCmiValue(string $element, $value): void
    {
        ScormCmiData::updateOrCreate(
            [
                'attempt_id' => $this->id,
                'element' => $element,
            ],
            ['value' => $value]
        );

        // Update attempt model for key elements
        $this->syncCmiToModel($element, $value);
    }

    public function getCmiValue(string $element)
    {
        $data = $this->cmiData()->where('element', $element)->first();
        return $data?->value;
    }

    protected function syncCmiToModel(string $element, $value): void
    {
        $updates = [];

        // SCORM 1.2 mappings
        $mappings12 = [
            'cmi.core.lesson_status' => 'lesson_status',
            'cmi.core.exit' => 'exit_status',
            'cmi.core.score.raw' => 'score_raw',
            'cmi.core.score.min' => 'score_min',
            'cmi.core.score.max' => 'score_max',
            'cmi.core.session_time' => 'session_time',
            'cmi.core.lesson_location' => 'location',
            'cmi.suspend_data' => 'suspend_data',
        ];

        // SCORM 2004 mappings
        $mappings2004 = [
            'cmi.completion_status' => 'completion_status',
            'cmi.success_status' => 'success_status',
            'cmi.score.raw' => 'score_raw',
            'cmi.score.min' => 'score_min',
            'cmi.score.max' => 'score_max',
            'cmi.score.scaled' => 'score_scaled',
            'cmi.progress_measure' => 'progress_measure',
            'cmi.session_time' => 'session_time',
            'cmi.location' => 'location',
            'cmi.suspend_data' => 'suspend_data',
            'cmi.exit' => 'exit_status',
        ];

        $mappings = $this->package->is_scorm_12 ? $mappings12 : $mappings2004;

        if (isset($mappings[$element])) {
            $updates[$mappings[$element]] = $value;
        }

        if (!empty($updates)) {
            $updates['last_accessed_at'] = now();
            $this->update($updates);
        }
    }

    public function terminate(): void
    {
        // Calculate total time
        if ($this->session_time) {
            $this->addSessionToTotalTime();
        }

        // Mark completed if status indicates completion
        if ($this->is_complete && !$this->completed_at) {
            $this->update(['completed_at' => now()]);
            $this->updateContentProgress();
        }

        // Set entry for next session
        $entry = $this->exit_status === 'suspend' ? 'resume' : '';
        $this->update([
            'entry' => $entry,
            'last_accessed_at' => now(),
        ]);
    }

    protected function addSessionToTotalTime(): void
    {
        // ISO 8601 duration parsing/addition would be implemented here
        // For now, just store the session time
        $this->update(['total_time' => $this->session_time]);
    }

    protected function updateContentProgress(): void
    {
        if (!$this->content_item_id) {
            return;
        }

        $contentItem = $this->contentItem;
        $enrollment = Enrollment::where('course_id', $contentItem->module->course_id)
            ->where('student_id', $this->student_id)
            ->first();

        if (!$enrollment) {
            return;
        }

        $progress = ContentProgress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'content_item_id' => $contentItem->id,
        ]);

        $progress->markAsCompleted($this->score_raw, $this->score_percentage);
    }

    public function getInitialData(): array
    {
        $data = [];

        // Load all CMI data for resume
        foreach ($this->cmiData as $cmi) {
            $data[$cmi->element] = $cmi->value;
        }

        // Set entry mode
        $data[$this->package->is_scorm_12 ? 'cmi.core.entry' : 'cmi.entry'] = $this->entry;

        return $data;
    }
}
