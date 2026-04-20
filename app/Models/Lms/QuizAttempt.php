<?php

namespace App\Models\Lms;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $table = 'lms_quiz_attempts';

    protected $fillable = [
        'quiz_id',
        'student_id',
        'attempt_number',
        'started_at',
        'submitted_at',
        'time_spent_seconds',
        'score',
        'max_score',
        'percentage',
        'passed',
        'answers',
        'grading_status',
        'graded_by',
        'graded_at',
        'feedback',
        'ip_address',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'passed' => 'boolean',
        'answers' => 'array',
    ];

    // Relationships
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('submitted_at');
    }

    public function scopeNeedsGrading($query)
    {
        return $query->whereIn('grading_status', ['pending', 'auto_graded']);
    }

    // Accessors
    public function getIsSubmittedAttribute(): bool
    {
        return $this->submitted_at !== null;
    }

    public function getIsInProgressAttribute(): bool
    {
        return $this->submitted_at === null;
    }

    public function getTimeSpentFormattedAttribute(): string
    {
        if (!$this->time_spent_seconds) {
            return '0:00';
        }

        $hours = floor($this->time_spent_seconds / 3600);
        $minutes = floor(($this->time_spent_seconds % 3600) / 60);
        $seconds = $this->time_spent_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getRemainingTimeAttribute(): ?int
    {
        if (!$this->quiz->hasTimeLimit() || $this->is_submitted) {
            return null;
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        $remaining = ($this->quiz->time_limit_minutes * 60) - $elapsed;

        return max(0, $remaining);
    }

    public function getIsTimedOutAttribute(): bool
    {
        if (!$this->quiz->hasTimeLimit()) {
            return false;
        }

        $elapsed = now()->diffInSeconds($this->started_at);

        return $elapsed >= ($this->quiz->time_limit_minutes * 60);
    }

    // Helper Methods
    public function submit(): void
    {
        $this->update([
            'submitted_at' => now(),
            'time_spent_seconds' => now()->diffInSeconds($this->started_at),
        ]);

        $this->autoGrade();
    }

    public function autoGrade(): void
    {
        $totalScore = 0;
        $maxScore = 0;
        $hasManualQuestions = false;
        $answers = $this->answers ?? [];

        foreach ($this->quiz->questions as $question) {
            $maxScore += $question->points;

            $answerData = $answers[$question->id] ?? null;
            $response = is_array($answerData) && array_key_exists('response', $answerData)
                ? $answerData['response']
                : $answerData;
            $result = $question->gradeResponse($response);

            if ($result['needs_manual_grading']) {
                $hasManualQuestions = true;
            } elseif ($result['score'] !== null) {
                $totalScore += $result['score'];
            }

            // Store the grading result
            $answers[$question->id] = [
                'response' => $response,
                'score' => $result['score'],
                'is_correct' => $result['is_correct'],
                'needs_manual_grading' => $result['needs_manual_grading'],
            ];
        }

        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
        $passed = $percentage >= $this->quiz->passing_score;

        $this->update([
            'answers' => $answers,
            'score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'passed' => $passed,
            'grading_status' => $hasManualQuestions ? 'auto_graded' : 'finalized',
            'graded_at' => $hasManualQuestions ? null : now(),
        ]);

        // Update content progress if fully graded
        if (!$hasManualQuestions) {
            $this->updateContentProgress();
        }
    }

    public function manualGrade(array $scores, ?string $feedback, int $graderId): void
    {
        $answers = $this->answers ?? [];
        $totalScore = 0;

        foreach ($this->quiz->questions as $question) {
            if (isset($scores[$question->id])) {
                $answers[$question->id]['score'] = $scores[$question->id];
                $answers[$question->id]['needs_manual_grading'] = false;
            }

            $totalScore += $answers[$question->id]['score'] ?? 0;
        }

        $maxScore = $this->max_score ?? $this->quiz->total_points;
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
        $passed = $percentage >= $this->quiz->passing_score;

        $this->update([
            'answers' => $answers,
            'score' => $totalScore,
            'percentage' => $percentage,
            'passed' => $passed,
            'grading_status' => 'finalized',
            'graded_by' => $graderId,
            'graded_at' => now(),
            'feedback' => $feedback,
        ]);

        $this->updateContentProgress();
    }

    protected function updateContentProgress(): void
    {
        $contentItem = $this->quiz->contentItem;
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

        $progress->markAsCompleted($this->score, $this->percentage);
    }

    public function saveAnswer(int $questionId, $response): void
    {
        $answers = $this->answers ?? [];
        $answers[$questionId] = ['response' => $response];
        $this->update(['answers' => $answers]);
    }

    public function getAnswerForQuestion(int $questionId)
    {
        $answers = $this->answers ?? [];

        return $answers[$questionId]['response'] ?? null;
    }
}
