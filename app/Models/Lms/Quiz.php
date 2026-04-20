<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_quizzes';

    protected $fillable = [
        'content_item_id',
        'title',
        'instructions',
        'time_limit_minutes',
        'max_attempts',
        'shuffle_questions',
        'shuffle_answers',
        'show_correct_answers',
        'show_correct_answers_after',
        'passing_score',
        'allow_review',
        'one_question_per_page',
        'lock_after_attempt',
        'require_access_code',
        'access_code',
    ];

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'shuffle_answers' => 'boolean',
        'show_correct_answers' => 'boolean',
        'show_correct_answers_after' => 'datetime',
        'passing_score' => 'decimal:2',
        'allow_review' => 'boolean',
        'one_question_per_page' => 'boolean',
        'lock_after_attempt' => 'boolean',
        'require_access_code' => 'boolean',
    ];

    // Relationships
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sequence');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // Accessors
    public function getQuestionCountAttribute(): int
    {
        return $this->questions()->count();
    }

    public function getTotalPointsAttribute(): float
    {
        return $this->questions()->sum('points');
    }

    // Helper Methods
    public function hasTimeLimit(): bool
    {
        return $this->time_limit_minutes !== null && $this->time_limit_minutes > 0;
    }

    public function hasAttemptLimit(): bool
    {
        return $this->max_attempts !== null && $this->max_attempts > 0;
    }

    public function canShowCorrectAnswers(): bool
    {
        if (!$this->show_correct_answers) {
            return false;
        }

        if ($this->show_correct_answers_after && now()->lt($this->show_correct_answers_after)) {
            return false;
        }

        return true;
    }

    public function getAttemptsForStudent($studentId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->orderBy('attempt_number', 'desc')
            ->get();
    }

    public function getAttemptCountForStudent($studentId): int
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->count();
    }

    public function canStudentAttempt($studentId): bool
    {
        if (!$this->hasAttemptLimit()) {
            return true;
        }

        $attemptCount = $this->getAttemptCountForStudent($studentId);

        return $attemptCount < $this->max_attempts;
    }

    public function getRemainingAttempts($studentId): ?int
    {
        if (!$this->hasAttemptLimit()) {
            return null;
        }

        $attemptCount = $this->getAttemptCountForStudent($studentId);

        return max(0, $this->max_attempts - $attemptCount);
    }

    public function getBestAttemptForStudent($studentId): ?QuizAttempt
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->whereNotNull('score')
            ->orderBy('score', 'desc')
            ->first();
    }

    public function getLatestAttemptForStudent($studentId): ?QuizAttempt
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->latest()
            ->first();
    }

    public function validateAccessCode(string $code): bool
    {
        if (!$this->require_access_code) {
            return true;
        }

        return $this->access_code === $code;
    }

    public function getQuestionsForAttempt(): \Illuminate\Database\Eloquent\Collection
    {
        $questions = $this->questions;

        if ($this->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        return $questions;
    }

    /**
     * Update total points (recalculates from questions)
     * This is called after adding/removing questions
     */
    public function updateTotalPoints(): float
    {
        return $this->questions()->sum('points');
    }
}
