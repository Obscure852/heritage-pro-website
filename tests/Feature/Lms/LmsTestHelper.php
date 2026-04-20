<?php

namespace Tests\Feature\Lms;

use App\Models\Grade;
use App\Models\Lms\ContentItem;
use App\Models\Lms\Course;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Module;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizAttempt;
use App\Models\Lms\QuizQuestion;
use App\Models\Role;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\User;

trait LmsTestHelper {
    protected function createTeacherUser(): User {
        $role = Role::firstOrCreate(['name' => 'Teacher']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    protected function createAdminUser(): User {
        $role = Role::firstOrCreate(['name' => 'Administrator']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    protected function createStudent(): Student {
        $this->seedGradeAndSponsorIfNeeded();

        return Student::factory()->create();
    }

    protected function seedGradeAndSponsorIfNeeded(): void {
        $gradeData = [
            1 => ['name' => 'F1', 'promotion' => 'F2', 'description' => 'Form 1', 'level' => 'Junior'],
            2 => ['name' => 'F2', 'promotion' => 'F3', 'description' => 'Form 2', 'level' => 'Junior'],
            3 => ['name' => 'F3', 'promotion' => 'Alumni', 'description' => 'Form 3', 'level' => 'Junior'],
            4 => ['name' => 'STD 1', 'promotion' => 'STD 2', 'description' => 'Standard 1', 'level' => 'Primary'],
            9 => ['name' => 'F4', 'promotion' => 'F5', 'description' => 'Form 4', 'level' => 'Senior'],
            10 => ['name' => 'F5', 'promotion' => 'Alumni', 'description' => 'Form 5', 'level' => 'Senior'],
        ];

        $termId = \App\Models\Term::first()?->id ?? 1;

        foreach ($gradeData as $id => $data) {
            if (!Grade::find($id)) {
                \Illuminate\Support\Facades\DB::table('grades')->insert([
                    'id' => $id,
                    'sequence' => $id,
                    'name' => $data['name'],
                    'promotion' => $data['promotion'],
                    'description' => $data['description'],
                    'level' => $data['level'],
                    'term_id' => $termId,
                    'active' => 1,
                    'year' => 2023,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Sponsor::count() === 0) {
            Sponsor::factory()->create();
        }
    }

    protected function createCourseWithQuiz(array $quizOverrides = []): array {
        $this->seedGradeAndSponsorIfNeeded();
        $teacher = $this->createTeacherUser();
        $gradeId = Grade::first()->id;
        $termId = \App\Models\Term::first()?->id ?? 1;

        $course = Course::create([
            'code' => 'TEST-' . uniqid(),
            'title' => 'Test Course',
            'slug' => 'test-course-' . uniqid(),
            'status' => 'published',
            'instructor_id' => $teacher->id,
            'created_by' => $teacher->id,
            'grade_id' => $gradeId,
            'term_id' => $termId,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Test Module',
            'sequence' => 1,
        ]);

        $contentItem = ContentItem::create([
            'module_id' => $module->id,
            'title' => 'Test Quiz Content',
            'content_type' => 'quiz',
            'sequence' => 1,
            'type' => 'quiz',
        ]);

        $quiz = Quiz::create(array_merge([
            'content_item_id' => $contentItem->id,
            'title' => 'Test Quiz',
            'passing_score' => 60,
            'max_attempts' => 3,
        ], $quizOverrides));

        // Link contentable
        $contentItem->update([
            'contentable_id' => $quiz->id,
            'contentable_type' => Quiz::class,
        ]);

        return compact('teacher', 'course', 'module', 'contentItem', 'quiz');
    }

    protected function addMultipleChoiceQuestion(Quiz $quiz, array $overrides = []): QuizQuestion {
        $maxSequence = $quiz->questions()->max('sequence') ?? 0;

        return QuizQuestion::create(array_merge([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice',
            'question_text' => 'What is 2 + 2?',
            'points' => 10,
            'sequence' => $maxSequence + 1,
            'options' => ['3', '4', '5', '6'],
            'correct_answer' => [1], // index 1 = '4'
        ], $overrides));
    }

    protected function addTrueFalseQuestion(Quiz $quiz, array $overrides = []): QuizQuestion {
        $maxSequence = $quiz->questions()->max('sequence') ?? 0;

        return QuizQuestion::create(array_merge([
            'quiz_id' => $quiz->id,
            'type' => 'true_false',
            'question_text' => 'The sky is blue.',
            'points' => 5,
            'sequence' => $maxSequence + 1,
            'options' => ['True', 'False'],
            'correct_answer' => [0], // index 0 = True
        ], $overrides));
    }

    protected function addShortAnswerQuestion(Quiz $quiz, array $overrides = []): QuizQuestion {
        $maxSequence = $quiz->questions()->max('sequence') ?? 0;

        return QuizQuestion::create(array_merge([
            'quiz_id' => $quiz->id,
            'type' => 'short_answer',
            'question_text' => 'What is the capital of France?',
            'points' => 10,
            'sequence' => $maxSequence + 1,
            'correct_answer' => ['Paris', 'paris'],
        ], $overrides));
    }

    protected function addFillBlankQuestion(Quiz $quiz, array $overrides = []): QuizQuestion {
        $maxSequence = $quiz->questions()->max('sequence') ?? 0;

        return QuizQuestion::create(array_merge([
            'quiz_id' => $quiz->id,
            'type' => 'fill_blank',
            'question_text' => 'Water is made of hydrogen and ____.',
            'points' => 5,
            'sequence' => $maxSequence + 1,
            'correct_answer' => ['oxygen', 'Oxygen'],
        ], $overrides));
    }

    protected function addEssayQuestion(Quiz $quiz, array $overrides = []): QuizQuestion {
        $maxSequence = $quiz->questions()->max('sequence') ?? 0;

        return QuizQuestion::create(array_merge([
            'quiz_id' => $quiz->id,
            'type' => 'essay',
            'question_text' => 'Explain photosynthesis in your own words.',
            'points' => 20,
            'sequence' => $maxSequence + 1,
        ], $overrides));
    }

    protected function enrollStudent(Student $student, Course $course): Enrollment {
        return Enrollment::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
    }

    protected function createAttempt(Quiz $quiz, Student $student, array $overrides = []): QuizAttempt {
        $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->count();

        return QuizAttempt::create(array_merge([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'attempt_number' => $attemptCount + 1,
            'started_at' => now(),
            'grading_status' => 'pending',
            'ip_address' => '127.0.0.1',
        ], $overrides));
    }
}
