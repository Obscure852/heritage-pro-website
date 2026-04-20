<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizAttempt;
use App\Models\Lms\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller {
    /**
     * Display quiz details
     */
    public function show(Quiz $quiz) {
        $quiz->load(['contentItem.module.course', 'questions']);
        $course = $quiz->contentItem?->module?->course;

        $student = Auth::guard('student')->user();
        $attempts = null;
        $canAttempt = false;

        // Check if student is enrolled in the course or user has manage permission
        if ($student && $course) {
            $isEnrolled = Enrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->exists();

            if (!$isEnrolled) {
                return redirect()
                    ->route('lms.courses.show', $course)
                    ->with('error', 'You must be enrolled in this course to access quizzes.');
            }

            $attempts = $quiz->attempts()
                ->where('student_id', $student->id)
                ->orderBy('attempt_number', 'desc')
                ->get();

            $canAttempt = $quiz->canStudentAttempt($student->id);
        } elseif (!$student && !Gate::allows('manage-lms-content')) {
            abort(403, 'Unauthorized access to quiz.');
        }

        // Enrollment data for teachers
        $enrolledCount = 0;
        $enrollments = collect();
        if (Gate::allows('manage-lms-content') && $course) {
            $enrollments = Enrollment::where('course_id', $course->id)
                ->where('status', 'active')
                ->with('student')
                ->get()
                ->sortBy(fn($e) => $e->student?->last_name);
            $enrolledCount = $enrollments->count();
        }

        return view('lms.quizzes.show', compact('quiz', 'attempts', 'canAttempt', 'enrolledCount', 'enrollments'));
    }

    /**
     * Edit quiz settings
     */
    public function edit(Quiz $quiz) {
        Gate::authorize('manage-lms-content');

        $quiz->load(['contentItem.module.course']);

        $enrolledCount = 0;
        $enrollments = collect();
        $course = $quiz->contentItem?->module?->course;
        if ($course) {
            $enrollments = Enrollment::where('course_id', $course->id)
                ->where('status', 'active')
                ->with('student')
                ->get()
                ->sortBy(fn($e) => $e->student?->last_name);
            $enrolledCount = $enrollments->count();
        }

        return view('lms.quizzes.edit', compact('quiz', 'enrolledCount', 'enrollments'));
    }

    /**
     * Update quiz settings
     */
    public function update(Request $request, Quiz $quiz) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1|max:480',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'shuffle_questions' => 'boolean',
            'shuffle_answers' => 'boolean',
            'show_correct_answers' => 'boolean',
            'show_feedback' => 'boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after_or_equal:available_from',
        ]);

        $validated['shuffle_questions'] = $request->boolean('shuffle_questions');
        $validated['shuffle_answers'] = $request->boolean('shuffle_answers');
        $validated['show_correct_answers'] = $request->boolean('show_correct_answers');
        $validated['show_feedback'] = $request->boolean('show_feedback');

        $quiz->update($validated);

        return back()->with('success', 'Quiz settings updated successfully.');
    }

    /**
     * Manage quiz questions
     */
    public function questions(Quiz $quiz) {
        Gate::authorize('manage-lms-content');

        $quiz->load([
            'contentItem.module.course',
            'questions' => function ($q) {
                $q->orderBy('sequence');
            },
        ]);

        $questionTypes = QuizQuestion::TYPES;

        return view('lms.quizzes.questions', compact('quiz', 'questionTypes'));
    }

    /**
     * Add a question to quiz
     */
    public function storeQuestion(Request $request, Quiz $quiz) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(QuizQuestion::TYPES)),
            'question_text' => 'required|string',
            'points' => 'required|numeric|min:0.5|max:100',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'correct_answer' => 'required',
            'feedback_correct' => 'nullable|string',
            'feedback_incorrect' => 'nullable|string',
            'case_sensitive' => 'boolean',
            'partial_credit' => 'boolean',
        ]);

        // Get next sequence
        $maxSequence = $quiz->questions()->max('sequence') ?? 0;

        // Process options and correct answer based on type
        $options = null;
        $correctAnswer = $validated['correct_answer'];

        switch ($validated['type']) {
            case 'multiple_choice':
            case 'multiple_answer':
                $options = array_values(array_filter($validated['options'] ?? []));
                if (is_string($correctAnswer)) {
                    $correctAnswer = array_map('intval', explode(',', $correctAnswer));
                }
                break;

            case 'true_false':
                $options = ['True', 'False'];
                $correctAnswer = [$correctAnswer === 'true' ? 0 : 1];
                break;

            case 'matching':
                $options = $validated['options'] ?? [];
                $correctAnswer = $validated['correct_answer'];
                break;

            case 'fill_blank':
                // Multiple acceptable answers
                if (is_string($correctAnswer)) {
                    $correctAnswer = array_map('trim', explode('|', $correctAnswer));
                }
                break;

            case 'ordering':
                $options = array_values(array_filter($validated['options'] ?? []));
                $correctAnswer = range(0, count($options) - 1);
                break;
        }

        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'type' => $validated['type'],
            'question_text' => $validated['question_text'],
            'points' => $validated['points'],
            'sequence' => $maxSequence + 1,
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'feedback_correct' => $validated['feedback_correct'] ?? null,
            'feedback_incorrect' => $validated['feedback_incorrect'] ?? null,
            'case_sensitive' => $request->boolean('case_sensitive'),
            'partial_credit' => $request->boolean('partial_credit'),
        ]);

        // Update quiz total points
        $quiz->updateTotalPoints();

        return back()->with('success', 'Question added successfully.');
    }

    /**
     * Update a question
     */
    public function updateQuestion(Request $request, QuizQuestion $question) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'question_text' => 'required|string',
            'points' => 'required|numeric|min:0.5|max:100',
            'options' => 'nullable|array',
            'correct_answer' => 'required',
            'feedback_correct' => 'nullable|string',
            'feedback_incorrect' => 'nullable|string',
            'case_sensitive' => 'boolean',
            'partial_credit' => 'boolean',
        ]);

        // Process based on question type
        $options = $question->options;
        $correctAnswer = $validated['correct_answer'];

        switch ($question->type) {
            case 'multiple_choice':
            case 'multiple_answer':
                $options = array_values(array_filter($validated['options'] ?? []));
                if (is_string($correctAnswer)) {
                    $correctAnswer = array_map('intval', explode(',', $correctAnswer));
                }
                break;

            case 'fill_blank':
                if (is_string($correctAnswer)) {
                    $correctAnswer = array_map('trim', explode('|', $correctAnswer));
                }
                break;

            case 'ordering':
                $options = array_values(array_filter($validated['options'] ?? []));
                break;
        }

        $question->update([
            'question_text' => $validated['question_text'],
            'points' => $validated['points'],
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'feedback_correct' => $validated['feedback_correct'] ?? null,
            'feedback_incorrect' => $validated['feedback_incorrect'] ?? null,
            'case_sensitive' => $request->boolean('case_sensitive'),
            'partial_credit' => $request->boolean('partial_credit'),
        ]);

        // Update quiz total points
        $question->quiz->updateTotalPoints();

        return back()->with('success', 'Question updated successfully.');
    }

    /**
     * Delete a question
     */
    public function destroyQuestion(QuizQuestion $question) {
        Gate::authorize('manage-lms-content');

        $quiz = $question->quiz;
        $question->delete();

        // Reorder remaining questions
        $quiz->questions()
            ->orderBy('sequence')
            ->get()
            ->each(function ($q, $index) {
                $q->update(['sequence' => $index + 1]);
            });

        // Update quiz total points
        $quiz->updateTotalPoints();

        return back()->with('success', 'Question deleted successfully.');
    }

    /**
     * Reorder questions
     */
    public function reorderQuestions(Request $request, Quiz $quiz) {
        Gate::authorize('manage-lms-content');

        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*' => 'integer|exists:lms_quiz_questions,id',
        ]);

        foreach ($validated['questions'] as $sequence => $questionId) {
            QuizQuestion::where('id', $questionId)
                ->where('quiz_id', $quiz->id)
                ->update(['sequence' => $sequence + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Start a quiz attempt (student)
     */
    public function start(Request $request, Quiz $quiz) {
        $student = Auth::guard('student')->user();

        // Verify enrollment
        $course = $quiz->contentItem->module->course;
        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        return DB::transaction(function () use ($request, $quiz, $student) {
            // Lock quiz row to prevent concurrent attempt creation
            $quiz->lockForUpdate();

            // Check for existing in-progress attempt
            $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->whereNull('submitted_at')
                ->lockForUpdate()
                ->first();

            if ($existingAttempt) {
                return redirect()->route('lms.quizzes.attempt', [$quiz, $existingAttempt]);
            }

            // Check attempt count atomically
            $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->count();

            if ($quiz->max_attempts && $attemptCount >= $quiz->max_attempts) {
                throw ValidationException::withMessages([
                    'quiz' => 'Maximum attempts reached for this quiz.',
                ]);
            }

            // Check if quiz is available
            if (!$quiz->canStudentAttempt($student->id)) {
                throw ValidationException::withMessages([
                    'quiz' => 'You cannot attempt this quiz. It may not be available yet.',
                ]);
            }

            // Create attempt atomically
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'attempt_number' => $attemptCount + 1,
                'started_at' => now(),
                'grading_status' => 'pending',
                'ip_address' => request()->ip(),
            ]);

            return redirect()->route('lms.quizzes.attempt', [$quiz, $attempt]);
        });
    }

    /**
     * Display quiz attempt page (student)
     */
    public function attempt(Quiz $quiz, QuizAttempt $attempt) {
        $student = Auth::guard('student')->user();

        // Verify ownership
        if ($attempt->student_id !== $student->id) {
            abort(403);
        }

        // Check if already submitted
        if ($attempt->is_submitted) {
            return redirect()->route('lms.quizzes.results', [$quiz, $attempt]);
        }

        // Check timeout
        if ($attempt->is_timed_out) {
            $attempt->submit();
            return redirect()
                ->route('lms.quizzes.results', [$quiz, $attempt])
                ->with('warning', 'Quiz time expired. Your answers have been submitted.');
        }

        $quiz->load(['questions' => function ($q) use ($quiz) {
            if ($quiz->shuffle_questions) {
                $q->inRandomOrder();
            } else {
                $q->orderBy('sequence');
            }
        }]);

        return view('lms.quizzes.attempt', compact('quiz', 'attempt'));
    }

    /**
     * Save answer (AJAX - student)
     */
    public function saveAnswer(Request $request, Quiz $quiz, QuizAttempt $attempt) {
        $student = Auth::guard('student')->user();

        if ($attempt->student_id !== $student->id || $attempt->is_submitted) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:lms_quiz_questions,id',
            'response' => 'nullable',
        ]);

        $attempt->saveAnswer($validated['question_id'], $validated['response']);

        return response()->json(['success' => true]);
    }

    /**
     * Submit quiz (student)
     */
    public function submit(Request $request, Quiz $quiz, QuizAttempt $attempt) {
        $student = Auth::guard('student')->user();

        if ($attempt->student_id !== $student->id) {
            abort(403);
        }

        return DB::transaction(function () use ($quiz, $attempt) {
            // Lock the attempt to prevent double submission
            $attempt = QuizAttempt::lockForUpdate()->find($attempt->id);

            if ($attempt->is_submitted) {
                return redirect()
                    ->route('lms.quizzes.results', [$quiz, $attempt])
                    ->with('info', 'This quiz has already been submitted.');
            }

            $attempt->submit();

            return redirect()
                ->route('lms.quizzes.results', [$quiz, $attempt])
                ->with('success', 'Quiz submitted successfully.');
        });
    }

    /**
     * View quiz results (student)
     */
    public function results(Quiz $quiz, QuizAttempt $attempt) {
        $student = Auth::guard('student')->user();

        if ($attempt->student_id !== $student->id) {
            abort(403);
        }

        if (!$attempt->is_submitted) {
            return redirect()->route('lms.quizzes.attempt', [$quiz, $attempt]);
        }

        $quiz->load(['questions' => function ($q) {
            $q->orderBy('sequence');
        }]);

        $showAnswers = $quiz->show_correct_answers;
        $showFeedback = $quiz->show_feedback;

        return view('lms.quizzes.results', compact('quiz', 'attempt', 'showAnswers', 'showFeedback'));
    }

    /**
     * View all attempts for a quiz (teacher)
     */
    public function attempts(Quiz $quiz) {
        Gate::authorize('grade-lms-content');

        $quiz->load('contentItem.module.course');

        $attempts = $quiz->attempts()
            ->with('student')
            ->submitted()
            ->orderBy('submitted_at', 'desc')
            ->paginate(25);

        $needsGrading = $quiz->attempts()
            ->needsGrading()
            ->count();

        return view('lms.quizzes.attempts', compact('quiz', 'attempts', 'needsGrading'));
    }

    /**
     * View enrolled students and their attempt status (teacher)
     */
    public function enrollments(Request $request, Quiz $quiz) {
        Gate::authorize('grade-lms-content');

        $quiz->load('contentItem.module.course');
        $course = $quiz->contentItem?->module?->course;

        if (!$course) {
            abort(404, 'Quiz is not linked to a course.');
        }

        $enrollmentsQuery = Enrollment::where('course_id', $course->id)
            ->where('status', 'active')
            ->with('student.currentClassRelation');

        // Search filter
        if ($search = $request->input('search')) {
            $enrollmentsQuery->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $enrollments = $enrollmentsQuery->paginate(50)->withQueryString();

        // Get attempt stats grouped by student
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->whereNotNull('submitted_at')
            ->selectRaw('student_id, COUNT(*) as attempt_count, MAX(score) as best_score')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $enrolledCount = Enrollment::where('course_id', $course->id)->where('status', 'active')->count();
        $attemptedCount = $attempts->count();

        return view('lms.quizzes.enrollments', compact('quiz', 'enrollments', 'attempts', 'enrolledCount', 'attemptedCount'));
    }

    /**
     * Grade form for an attempt (teacher)
     */
    public function gradeForm(Quiz $quiz, QuizAttempt $attempt) {
        Gate::authorize('grade-lms-content');

        $quiz->load(['questions' => function ($q) {
            $q->orderBy('sequence');
        }]);

        return view('lms.quizzes.grade', compact('quiz', 'attempt'));
    }

    /**
     * Save grades for an attempt (teacher)
     */
    public function saveGrade(Request $request, Quiz $quiz, QuizAttempt $attempt) {
        Gate::authorize('grade-lms-content');

        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);

        $attempt->manualGrade(
            $validated['scores'],
            $validated['feedback'] ?? null,
            Auth::id()
        );

        return redirect()
            ->route('lms.quizzes.attempts', $quiz)
            ->with('success', 'Quiz graded successfully.');
    }
}
