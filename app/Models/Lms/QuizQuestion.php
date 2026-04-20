<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $table = 'lms_quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question_bank_id',
        'type',
        'question_text',
        'question_media',
        'points',
        'sequence',
        'feedback_correct',
        'feedback_incorrect',
        'explanation',
        'options',
        'correct_answer',
        'case_sensitive',
        'partial_credit',
    ];

    protected $casts = [
        'question_media' => 'array',
        'options' => 'array',
        'correct_answer' => 'array',
        'points' => 'decimal:2',
        'case_sensitive' => 'boolean',
        'partial_credit' => 'boolean',
    ];

    public const TYPES = [
        'multiple_choice' => 'Multiple Choice',
        'multiple_answer' => 'Multiple Answer',
        'true_false' => 'True/False',
        'matching' => 'Matching',
        'fill_blank' => 'Fill in the Blank',
        'short_answer' => 'Short Answer',
        'essay' => 'Essay',
        'ordering' => 'Ordering',
    ];

    public const AUTO_GRADABLE_TYPES = [
        'multiple_choice',
        'multiple_answer',
        'true_false',
        'matching',
        'fill_blank',
        'short_answer',
        'ordering',
    ];

    // Relationships
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    // Accessors
    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getIsAutoGradableAttribute(): bool
    {
        return in_array($this->type, self::AUTO_GRADABLE_TYPES);
    }

    // Helper Methods
    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    public function isMultipleAnswer(): bool
    {
        return $this->type === 'multiple_answer';
    }

    public function isTrueFalse(): bool
    {
        return $this->type === 'true_false';
    }

    public function isMatching(): bool
    {
        return $this->type === 'matching';
    }

    public function isFillBlank(): bool
    {
        return $this->type === 'fill_blank';
    }

    public function isEssay(): bool
    {
        return $this->type === 'essay';
    }

    public function isOrdering(): bool
    {
        return $this->type === 'ordering';
    }

    public function getShuffledOptions(): ?array
    {
        if (!$this->options) {
            return null;
        }

        $options = $this->options;

        // Don't shuffle if it's true/false or if order matters
        if ($this->isTrueFalse() || $this->isOrdering()) {
            return $options;
        }

        shuffle($options);

        return $options;
    }

    public function gradeResponse($response): array
    {
        if (!$this->is_auto_gradable) {
            return [
                'score' => null,
                'is_correct' => null,
                'needs_manual_grading' => true,
            ];
        }

        $isCorrect = $this->checkAnswer($response);
        $score = $isCorrect ? $this->points : 0;

        // Handle partial credit for multiple answer questions
        if ($this->partial_credit && $this->isMultipleAnswer()) {
            $score = $this->calculatePartialCredit($response);
            $isCorrect = $score > 0;
        }

        return [
            'score' => $score,
            'is_correct' => $isCorrect,
            'needs_manual_grading' => false,
        ];
    }

    protected function checkAnswer($response): bool
    {
        $correctAnswer = $this->correct_answer;

        return match ($this->type) {
            'multiple_choice', 'true_false' => $this->checkSingleAnswer($response, $correctAnswer),
            'multiple_answer' => $this->checkMultipleAnswer($response, $correctAnswer),
            'matching' => $this->checkMatchingAnswer($response, $correctAnswer),
            'fill_blank' => $this->checkFillBlankAnswer($response, $correctAnswer),
            'short_answer' => $this->checkShortAnswer($response, $correctAnswer),
            'ordering' => $this->checkOrderingAnswer($response, $correctAnswer),
            default => false,
        };
    }

    protected function checkSingleAnswer($response, $correct): bool
    {
        if (is_array($correct)) {
            $correct = $correct[0] ?? null;
        }

        if ($this->case_sensitive) {
            return $response === $correct;
        }

        return strtolower((string)$response) === strtolower((string)$correct);
    }

    protected function checkMultipleAnswer($response, $correct): bool
    {
        if (!is_array($response) || !is_array($correct)) {
            return false;
        }

        sort($response);
        sort($correct);

        return $response === $correct;
    }

    protected function checkMatchingAnswer($response, $correct): bool
    {
        if (!is_array($response) || !is_array($correct)) {
            return false;
        }

        foreach ($correct as $key => $value) {
            if (!isset($response[$key]) || $response[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    protected function checkFillBlankAnswer($response, $correct): bool
    {
        if (!is_array($correct)) {
            $correct = [$correct];
        }

        foreach ($correct as $acceptableAnswer) {
            $matches = $this->case_sensitive
                ? $response === $acceptableAnswer
                : strtolower(trim((string)$response)) === strtolower(trim((string)$acceptableAnswer));

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    protected function checkShortAnswer($response, $correct): bool
    {
        if (!is_array($correct)) {
            $correct = [$correct];
        }

        $normalizedResponse = preg_replace('/\s+/', ' ', trim(strtolower((string) $response)));

        foreach ($correct as $acceptableAnswer) {
            $normalizedAnswer = preg_replace('/\s+/', ' ', trim(strtolower((string) $acceptableAnswer)));

            if ($this->case_sensitive) {
                if (preg_replace('/\s+/', ' ', trim((string) $response)) === preg_replace('/\s+/', ' ', trim((string) $acceptableAnswer))) {
                    return true;
                }
            } elseif ($normalizedResponse === $normalizedAnswer) {
                return true;
            }
        }

        return false;
    }

    protected function checkOrderingAnswer($response, $correct): bool
    {
        if (!is_array($response) || !is_array($correct)) {
            return false;
        }

        return $response === $correct;
    }

    protected function calculatePartialCredit($response): float
    {
        if (!is_array($response) || !is_array($this->correct_answer)) {
            return 0;
        }

        $correct = $this->correct_answer;
        $correctCount = 0;
        $totalCorrect = count($correct);

        foreach ($response as $answer) {
            if (in_array($answer, $correct)) {
                $correctCount++;
            } else {
                // Penalty for wrong answers
                $correctCount = max(0, $correctCount - 0.5);
            }
        }

        if ($totalCorrect === 0) {
            return 0;
        }

        return round(($correctCount / $totalCorrect) * $this->points, 2);
    }
}
