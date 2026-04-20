<?php

namespace Tests\Feature\Lms;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lms\QuizAttempt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuizDeletionGuardTest extends TestCase {
    use DatabaseTransactions, LmsTestHelper;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_cannot_delete_quiz_with_in_progress_attempt(): void {
        $setup = $this->createCourseWithQuiz();
        $student = $this->createStudent();
        $this->enrollStudent($student, $setup['course']);

        // Create an in-progress attempt (no submitted_at)
        $this->createAttempt($setup['quiz'], $student);

        $response = $this->actingAs($setup['teacher'])
            ->delete(route('lms.content.destroy', $setup['contentItem']));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Quiz should still exist
        $this->assertDatabaseHas('lms_quizzes', [
            'id' => $setup['quiz']->id,
        ]);
    }

    public function test_can_delete_quiz_with_only_submitted_attempts(): void {
        $setup = $this->createCourseWithQuiz();
        $student = $this->createStudent();
        $this->enrollStudent($student, $setup['course']);

        // Create a submitted attempt
        $this->createAttempt($setup['quiz'], $student, [
            'submitted_at' => now(),
            'grading_status' => 'finalized',
        ]);

        $response = $this->actingAs($setup['teacher'])
            ->delete(route('lms.content.destroy', $setup['contentItem']));

        $response->assertRedirect();
        $response->assertSessionMissing('error');
    }

    public function test_can_delete_quiz_with_no_attempts(): void {
        $setup = $this->createCourseWithQuiz();

        $response = $this->actingAs($setup['teacher'])
            ->delete(route('lms.content.destroy', $setup['contentItem']));

        $response->assertRedirect();
        $response->assertSessionMissing('error');
    }
}
