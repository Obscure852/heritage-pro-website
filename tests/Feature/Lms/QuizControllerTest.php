<?php

namespace Tests\Feature\Lms;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lms\QuizAttempt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuizControllerTest extends TestCase {
    use DatabaseTransactions, LmsTestHelper;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    // ==================== QUIZ START ROUTE METHOD ====================

    public function test_start_route_is_post_not_get(): void {
        $setup = $this->createCourseWithQuiz();

        // GET should return 405 Method Not Allowed
        $response = $this->actingAs($setup['teacher'])
            ->get(route('lms.quizzes.start', $setup['quiz']));

        $response->assertStatus(405);
    }

    // ==================== QUIZ SHOW (TEACHER) ====================

    public function test_teacher_can_view_quiz(): void {
        $setup = $this->createCourseWithQuiz();

        $response = $this->actingAs($setup['teacher'])
            ->get(route('lms.quizzes.show', $setup['quiz']));

        // View renders successfully for teacher (no student guard = staff view)
        $response->assertOk();
        $response->assertViewIs('lms.quizzes.show');
    }

    public function test_unauthenticated_user_cannot_view_quiz(): void {
        $setup = $this->createCourseWithQuiz();

        $response = $this->get(route('lms.quizzes.show', $setup['quiz']));

        $response->assertRedirect();
    }

    // ==================== QUIZ UPDATE (TEACHER) ====================

    public function test_teacher_can_update_quiz_settings(): void {
        $setup = $this->createCourseWithQuiz();

        $response = $this->actingAs($setup['teacher'])
            ->put(route('lms.quizzes.update', $setup['quiz']), [
                'title' => 'Updated Quiz Title',
                'passing_score' => 75,
                'time_limit_minutes' => 30,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $setup['quiz']->refresh();
        $this->assertEquals('Updated Quiz Title', $setup['quiz']->title);
        $this->assertEquals(75, (float) $setup['quiz']->passing_score);
    }

    public function test_non_teacher_cannot_update_quiz(): void {
        $setup = $this->createCourseWithQuiz();
        $regularUser = \App\Models\User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->put(route('lms.quizzes.update', $setup['quiz']), [
                'title' => 'Hacked Title',
                'passing_score' => 0,
            ]);

        $response->assertForbidden();
    }

    // ==================== QUESTION MANAGEMENT ====================

    public function test_teacher_can_add_question(): void {
        $setup = $this->createCourseWithQuiz();

        $response = $this->actingAs($setup['teacher'])
            ->post(route('lms.quizzes.questions.store', $setup['quiz']), [
                'type' => 'multiple_choice',
                'question_text' => 'What is 1 + 1?',
                'points' => 5,
                'options' => ['1', '2', '3', '4'],
                'correct_answer' => '1',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lms_quiz_questions', [
            'quiz_id' => $setup['quiz']->id,
            'question_text' => 'What is 1 + 1?',
        ]);
    }

    public function test_teacher_can_add_question_with_explanation(): void {
        $setup = $this->createCourseWithQuiz();

        $question = $this->addMultipleChoiceQuestion($setup['quiz'], [
            'explanation' => 'Because 2 + 2 is always 4.',
            'feedback_correct' => 'Great job!',
            'feedback_incorrect' => 'Think again.',
        ]);

        $this->assertDatabaseHas('lms_quiz_questions', [
            'id' => $question->id,
            'explanation' => 'Because 2 + 2 is always 4.',
        ]);
    }

    public function test_teacher_can_update_question(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addMultipleChoiceQuestion($setup['quiz']);

        $response = $this->actingAs($setup['teacher'])
            ->put(route('lms.quizzes.questions.update', $question), [
                'question_text' => 'Updated question text?',
                'points' => 15,
                'options' => ['A', 'B', 'C', 'D'],
                'correct_answer' => '2',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $question->refresh();
        $this->assertEquals('Updated question text?', $question->question_text);
        $this->assertEquals(15, (float) $question->points);
    }

    public function test_teacher_can_delete_question(): void {
        $setup = $this->createCourseWithQuiz();
        $question = $this->addMultipleChoiceQuestion($setup['quiz']);

        $response = $this->actingAs($setup['teacher'])
            ->delete(route('lms.quizzes.questions.destroy', $question));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('lms_quiz_questions', [
            'id' => $question->id,
        ]);
    }

    // ==================== QUIZ ATTEMPT MODEL LOGIC ====================

    public function test_attempt_submit_calculates_time_spent(): void {
        $setup = $this->createCourseWithQuiz();
        $student = $this->createStudent();
        $this->addMultipleChoiceQuestion($setup['quiz']);

        $attempt = $this->createAttempt($setup['quiz'], $student, [
            'started_at' => now()->subMinutes(5),
        ]);

        $attempt->submit();

        $this->assertNotNull($attempt->submitted_at);
        $this->assertNotNull($attempt->time_spent_seconds);
        $this->assertGreaterThanOrEqual(300, $attempt->time_spent_seconds);
    }

    public function test_attempt_auto_grades_on_submit(): void {
        $setup = $this->createCourseWithQuiz();
        $student = $this->createStudent();

        $q1 = $this->addMultipleChoiceQuestion($setup['quiz'], ['points' => 10]);
        $q2 = $this->addTrueFalseQuestion($setup['quiz'], ['points' => 5]);

        $attempt = $this->createAttempt($setup['quiz'], $student, [
            'started_at' => now()->subMinutes(5),
            'answers' => [
                $q1->id => ['response' => 1],  // correct (index 1)
                $q2->id => ['response' => 0],  // correct (True = index 0)
            ],
        ]);

        $attempt->submit();

        $this->assertEquals('finalized', $attempt->grading_status);
        $this->assertEquals(15, (float) $attempt->score);
        $this->assertEquals(15, (float) $attempt->max_score);
        $this->assertEquals(100, (float) $attempt->percentage);
        $this->assertTrue($attempt->passed);
    }

    public function test_attempt_auto_grades_short_answer_correctly(): void {
        $setup = $this->createCourseWithQuiz();
        $student = $this->createStudent();

        $q = $this->addShortAnswerQuestion($setup['quiz'], [
            'points' => 10,
            'correct_answer' => ['Paris', 'paris'],
        ]);

        $attempt = $this->createAttempt($setup['quiz'], $student, [
            'started_at' => now()->subMinutes(1),
            'answers' => [
                $q->id => ['response' => 'paris'],
            ],
        ]);

        $attempt->submit();

        $this->assertEquals('finalized', $attempt->grading_status);
        $this->assertEquals(10, (float) $attempt->score);
        $this->assertTrue($attempt->passed);
    }

    public function test_attempt_with_essay_gets_auto_graded_status(): void {
        $setup = $this->createCourseWithQuiz();
        $student = $this->createStudent();

        $q1 = $this->addMultipleChoiceQuestion($setup['quiz'], ['points' => 10]);
        $q2 = $this->addEssayQuestion($setup['quiz'], ['points' => 20]);

        $attempt = $this->createAttempt($setup['quiz'], $student, [
            'started_at' => now()->subMinutes(10),
            'answers' => [
                $q1->id => ['response' => 1],  // correct
                $q2->id => ['response' => 'Some essay answer.'],
            ],
        ]);

        $attempt->submit();

        // Should be auto_graded because essay needs manual grading
        $this->assertEquals('auto_graded', $attempt->grading_status);
        $this->assertEquals(10, (float) $attempt->score);
    }

    public function test_attempt_ip_address_is_stored(): void {
        $setup = $this->createCourseWithQuiz();
        $student = $this->createStudent();

        $attempt = $this->createAttempt($setup['quiz'], $student, [
            'ip_address' => '192.168.1.100',
        ]);

        $this->assertEquals('192.168.1.100', $attempt->fresh()->ip_address);
    }

    public function test_quiz_can_student_attempt_respects_max_attempts(): void {
        $setup = $this->createCourseWithQuiz(['max_attempts' => 1]);
        $student = $this->createStudent();

        $this->assertTrue($setup['quiz']->canStudentAttempt($student->id));

        // Create a submitted attempt
        $this->createAttempt($setup['quiz'], $student, [
            'submitted_at' => now(),
            'grading_status' => 'finalized',
        ]);

        $this->assertFalse($setup['quiz']->canStudentAttempt($student->id));
    }

    public function test_quiz_unlimited_attempts_when_null(): void {
        $setup = $this->createCourseWithQuiz(['max_attempts' => null]);
        $student = $this->createStudent();

        // Create several submitted attempts
        for ($i = 0; $i < 5; $i++) {
            $this->createAttempt($setup['quiz'], $student, [
                'submitted_at' => now(),
                'grading_status' => 'finalized',
            ]);
        }

        $this->assertTrue($setup['quiz']->canStudentAttempt($student->id));
    }
}
