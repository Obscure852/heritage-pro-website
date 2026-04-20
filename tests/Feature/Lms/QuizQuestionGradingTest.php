<?php

namespace Tests\Feature\Lms;

use App\Models\Lms\QuizQuestion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuizQuestionGradingTest extends TestCase {
    use DatabaseTransactions, LmsTestHelper;

    // ==================== SHORT ANSWER AUTO-GRADING ====================

    public function test_short_answer_is_auto_gradable(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addShortAnswerQuestion($setup['quiz']);

        $this->assertTrue($question->is_auto_gradable);
    }

    public function test_short_answer_grades_exact_match(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addShortAnswerQuestion($setup['quiz'], [
            'correct_answer' => ['Paris'],
        ]);

        $result = $question->gradeResponse('Paris');

        $this->assertTrue($result['is_correct']);
        $this->assertEquals($question->points, $result['score']);
        $this->assertFalse($result['needs_manual_grading']);
    }

    public function test_short_answer_case_insensitive_by_default(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addShortAnswerQuestion($setup['quiz'], [
            'correct_answer' => ['Paris'],
            'case_sensitive' => false,
        ]);

        $result = $question->gradeResponse('paris');
        $this->assertTrue($result['is_correct']);

        $result = $question->gradeResponse('PARIS');
        $this->assertTrue($result['is_correct']);

        $result = $question->gradeResponse('pArIs');
        $this->assertTrue($result['is_correct']);
    }

    public function test_short_answer_case_sensitive_when_enabled(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addShortAnswerQuestion($setup['quiz'], [
            'correct_answer' => ['Paris'],
            'case_sensitive' => true,
        ]);

        $result = $question->gradeResponse('Paris');
        $this->assertTrue($result['is_correct']);

        $result = $question->gradeResponse('paris');
        $this->assertFalse($result['is_correct']);
    }

    public function test_short_answer_normalizes_whitespace(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addShortAnswerQuestion($setup['quiz'], [
            'correct_answer' => ['New York'],
        ]);

        // Extra spaces between words
        $result = $question->gradeResponse('New  York');
        $this->assertTrue($result['is_correct']);

        // Leading/trailing whitespace
        $result = $question->gradeResponse('  New York  ');
        $this->assertTrue($result['is_correct']);

        // Tab and multiple spaces
        $result = $question->gradeResponse("New\tYork");
        $this->assertTrue($result['is_correct']);
    }

    public function test_short_answer_supports_multiple_acceptable_answers(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addShortAnswerQuestion($setup['quiz'], [
            'correct_answer' => ['USA', 'United States', 'United States of America'],
        ]);

        $result = $question->gradeResponse('USA');
        $this->assertTrue($result['is_correct']);

        $result = $question->gradeResponse('united states');
        $this->assertTrue($result['is_correct']);

        $result = $question->gradeResponse('United States of America');
        $this->assertTrue($result['is_correct']);

        $result = $question->gradeResponse('Canada');
        $this->assertFalse($result['is_correct']);
    }

    public function test_short_answer_wrong_answer_scores_zero(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addShortAnswerQuestion($setup['quiz'], [
            'correct_answer' => ['Paris'],
            'points' => 10,
        ]);

        $result = $question->gradeResponse('London');

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']);
    }

    public function test_short_answer_null_response_scores_zero(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addShortAnswerQuestion($setup['quiz']);

        $result = $question->gradeResponse(null);

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']);
    }

    // ==================== ESSAY (MANUAL GRADING) ====================

    public function test_essay_requires_manual_grading(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addEssayQuestion($setup['quiz']);

        $this->assertFalse($question->is_auto_gradable);

        $result = $question->gradeResponse('Some essay text');

        $this->assertNull($result['score']);
        $this->assertNull($result['is_correct']);
        $this->assertTrue($result['needs_manual_grading']);
    }

    // ==================== MULTIPLE CHOICE ====================

    public function test_multiple_choice_correct_answer(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addMultipleChoiceQuestion($setup['quiz']);

        // correct_answer is [1], response is 1
        $result = $question->gradeResponse(1);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals($question->points, $result['score']);
    }

    public function test_multiple_choice_wrong_answer(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addMultipleChoiceQuestion($setup['quiz']);

        $result = $question->gradeResponse(0);

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']);
    }

    // ==================== TRUE/FALSE ====================

    public function test_true_false_correct_answer(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addTrueFalseQuestion($setup['quiz']);

        // correct_answer is [0] (True)
        $result = $question->gradeResponse(0);

        $this->assertTrue($result['is_correct']);
        $this->assertEquals($question->points, $result['score']);
    }

    public function test_true_false_wrong_answer(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addTrueFalseQuestion($setup['quiz']);

        $result = $question->gradeResponse(1);

        $this->assertFalse($result['is_correct']);
        $this->assertEquals(0, $result['score']);
    }

    // ==================== FILL IN THE BLANK ====================

    public function test_fill_blank_correct_answer(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addFillBlankQuestion($setup['quiz']);

        $result = $question->gradeResponse('oxygen');

        $this->assertTrue($result['is_correct']);
    }

    public function test_fill_blank_case_insensitive(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addFillBlankQuestion($setup['quiz']);

        $result = $question->gradeResponse('OXYGEN');

        $this->assertTrue($result['is_correct']);
    }

    // ==================== EXPLANATION FIELD ====================

    public function test_explanation_field_is_fillable(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addMultipleChoiceQuestion($setup['quiz'], [
            'explanation' => 'Because 2 + 2 equals 4.',
        ]);

        $this->assertEquals('Because 2 + 2 equals 4.', $question->fresh()->explanation);
    }

    public function test_explanation_field_is_nullable(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addMultipleChoiceQuestion($setup['quiz']);

        $this->assertNull($question->fresh()->explanation);
    }
}
