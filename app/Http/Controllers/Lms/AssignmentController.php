<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Assignment;
use App\Models\Lms\AssignmentAttachment;
use App\Models\Lms\AssignmentSubmission;
use App\Models\Lms\ContentItem;
use App\Models\Lms\Enrollment;
use App\Models\Lms\Module;
use App\Models\Lms\Rubric;
use App\Models\Lms\SubmissionFile;
use App\Http\Requests\Lms\SubmitAssignmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller {
    /**
     * Show assignment details
     */
    public function show(Assignment $assignment) {
        $assignment->load(['rubric.criteria.levels', 'contentItem.module.course', 'attachments']);
        $course = $assignment->contentItem?->module?->course;

        $student = Auth::guard('student')->user();
        $submission = null;
        $canSubmit = false;

        // Check if student is enrolled in the course or user has manage permission
        if ($student && $course) {
            $isEnrolled = Enrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->exists();

            if (!$isEnrolled) {
                return redirect()
                    ->route('lms.courses.show', $course)
                    ->with('error', 'You must be enrolled in this course to access assignments.');
            }

            $submission = $assignment->getSubmissionForStudent($student->id);
            if ($submission) {
                $submission->load('attachedFiles');
            }
            $canSubmit = $assignment->canStudentSubmit($student->id);
        } elseif (!$student && !Gate::allows('manage-lms-content')) {
            abort(403, 'Unauthorized access to assignment.');
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

        return view('lms.assignments.show', compact('assignment', 'submission', 'canSubmit', 'enrolledCount', 'enrollments'));
    }

    /**
     * Create assignment for a module
     */
    public function create(Module $module) {
        Gate::authorize('manage-lms-content');

        $rubrics = Rubric::where('is_template', true)
            ->orWhere('created_by', Auth::id())
            ->orderBy('title')
            ->get();

        return view('lms.assignments.create', compact('module', 'rubrics'));
    }

    /**
     * Store new assignment
     */
    public function store(Request $request, Module $module) {
        Gate::authorize('manage-lms-content');

        $allowedMimes = implode(',', AssignmentAttachment::ALLOWED_MIMES);
        $maxFileSize = AssignmentAttachment::MAX_FILE_SIZE_MB * 1024;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'submission_type' => 'required|in:file,text,both',
            'allowed_file_types' => 'nullable|string',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
            'max_files' => 'required|integer|min:1|max:20',
            'available_from' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:available_from',
            'cutoff_date' => 'nullable|date|after_or_equal:due_date',
            'max_points' => 'required|numeric|min:1|max:1000',
            'rubric_id' => 'nullable|exists:lms_rubrics,id',
            'allow_late_submissions' => 'boolean',
            'late_penalty_percent' => 'nullable|numeric|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'allow_resubmission' => 'boolean',
            'require_submission_text' => 'boolean',
            'attachments' => 'nullable|array|max:' . AssignmentAttachment::MAX_ATTACHMENTS,
            'attachments.*' => "nullable|file|mimes:{$allowedMimes}|max:{$maxFileSize}",
            'attachment_labels' => 'nullable|array',
            'attachment_labels.*' => 'nullable|string|max:255',
        ]);

        // Parse allowed file types
        if (!empty($validated['allowed_file_types'])) {
            $validated['allowed_file_types'] = array_map(
                'trim',
                explode(',', strtolower($validated['allowed_file_types']))
            );
        } else {
            $validated['allowed_file_types'] = Assignment::DEFAULT_FILE_TYPES;
        }

        $validated['allow_late_submissions'] = $request->boolean('allow_late_submissions');
        $validated['allow_resubmission'] = $request->boolean('allow_resubmission');
        $validated['require_submission_text'] = $request->boolean('require_submission_text');
        $validated['status'] = 'draft';

        $assignment = Assignment::create($validated);

        // Create content item linking to the assignment
        $maxSequence = $module->contentItems()->max('sequence') ?? 0;
        ContentItem::create([
            'module_id' => $module->id,
            'type' => 'assignment',
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'sequence' => $maxSequence + 1,
            'is_required' => true,
            'contentable_id' => $assignment->id,
            'contentable_type' => Assignment::class,
        ]);

        // Process attachment uploads
        if ($request->hasFile('attachments')) {
            $labels = $request->input('attachment_labels', []);
            $sortOrder = 0;

            foreach ($request->file('attachments') as $index => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('lms/assignments/' . $assignment->id . '/attachments', 'public');

                    $assignment->attachments()->create([
                        'label' => $labels[$index] ?? null,
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }

        return redirect()
            ->route('lms.assignments.edit', $assignment)
            ->with('success', 'Assignment created successfully.');
    }

    /**
     * Edit assignment
     */
    public function edit(Assignment $assignment) {
        Gate::authorize('manage-lms-content');

        $assignment->load(['rubric.criteria.levels', 'contentItem.module.course', 'attachments']);

        $rubrics = Rubric::where('is_template', true)
            ->orWhere('created_by', Auth::id())
            ->orderBy('title')
            ->get();

        $submissionStats = $assignment->submissions()
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN graded_at IS NOT NULL THEN 1 END) as graded,
                COUNT(CASE WHEN graded_at IS NULL AND submitted_at IS NOT NULL THEN 1 END) as pending
            ")->first();

        $enrolledCount = 0;
        $enrollments = collect();
        $course = $assignment->contentItem?->module?->course;
        if ($course) {
            $enrollments = Enrollment::where('course_id', $course->id)
                ->where('status', 'active')
                ->with('student')
                ->get()
                ->sortBy(fn($e) => $e->student?->last_name);
            $enrolledCount = $enrollments->count();
        }

        return view('lms.assignments.edit', compact('assignment', 'rubrics', 'submissionStats', 'enrolledCount', 'enrollments'));
    }

    /**
     * Update assignment
     */
    public function update(Request $request, Assignment $assignment) {
        Gate::authorize('manage-lms-content');

        $currentAttachmentCount = $assignment->attachments()->count();
        $deleteCount = count($request->input('delete_attachments', []));
        $remainingSlots = AssignmentAttachment::MAX_ATTACHMENTS - $currentAttachmentCount + $deleteCount;

        $allowedMimes = implode(',', AssignmentAttachment::ALLOWED_MIMES);
        $maxFileSize = AssignmentAttachment::MAX_FILE_SIZE_MB * 1024;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'submission_type' => 'required|in:file,text,both',
            'allowed_file_types' => 'nullable|string',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
            'max_files' => 'required|integer|min:1|max:20',
            'available_from' => 'nullable|date',
            'due_date' => 'nullable|date',
            'cutoff_date' => 'nullable|date',
            'max_points' => 'required|numeric|min:1|max:1000',
            'rubric_id' => 'nullable|exists:lms_rubrics,id',
            'allow_late_submissions' => 'boolean',
            'late_penalty_percent' => 'nullable|numeric|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1',
            'allow_resubmission' => 'boolean',
            'require_submission_text' => 'boolean',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'exists:lms_assignment_attachments,id',
            'attachments' => 'nullable|array|max:' . max(0, $remainingSlots),
            'attachments.*' => "nullable|file|mimes:{$allowedMimes}|max:{$maxFileSize}",
            'attachment_labels' => 'nullable|array',
            'attachment_labels.*' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['allowed_file_types'])) {
            $validated['allowed_file_types'] = array_map(
                'trim',
                explode(',', strtolower($validated['allowed_file_types']))
            );
        }

        $validated['allow_late_submissions'] = $request->boolean('allow_late_submissions');
        $validated['allow_resubmission'] = $request->boolean('allow_resubmission');
        $validated['require_submission_text'] = $request->boolean('require_submission_text');

        $assignment->update($validated);

        // Update linked content item
        if ($assignment->contentItem) {
            $assignment->contentItem->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
            ]);
        }

        // Handle attachment deletions
        if ($request->has('delete_attachments')) {
            $attachmentsToDelete = $assignment->attachments()
                ->whereIn('id', $request->input('delete_attachments'))
                ->get();

            foreach ($attachmentsToDelete as $attachment) {
                $attachment->delete();
            }
        }

        // Process new attachment uploads
        if ($request->hasFile('attachments')) {
            $labels = $request->input('attachment_labels', []);
            $maxSortOrder = $assignment->attachments()->max('sort_order') ?? -1;
            $sortOrder = $maxSortOrder + 1;

            foreach ($request->file('attachments') as $index => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('lms/assignments/' . $assignment->id . '/attachments', 'public');

                    $assignment->attachments()->create([
                        'label' => $labels[$index] ?? null,
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }

        return back()->with('success', 'Assignment updated successfully.');
    }

    /**
     * Publish assignment
     */
    public function publish(Assignment $assignment) {
        Gate::authorize('manage-lms-content');

        $assignment->publish();

        return back()->with('success', 'Assignment published.');
    }

    /**
     * Close assignment
     */
    public function close(Assignment $assignment) {
        Gate::authorize('manage-lms-content');

        $assignment->close();

        return back()->with('success', 'Assignment closed. No more submissions accepted.');
    }

    /**
     * Delete assignment
     */
    public function destroy(Assignment $assignment) {
        Gate::authorize('manage-lms-content');

        // Check for existing submissions
        $submissionCount = $assignment->submissions()->count();
        if ($submissionCount > 0) {
            return back()->with('error', "Cannot delete assignment with {$submissionCount} submission(s). Close the assignment instead.");
        }

        $module = $assignment->contentItem?->module;

        // Delete content item link
        if ($assignment->contentItem) {
            $assignment->contentItem->delete();
        }

        $assignment->delete();

        $redirectRoute = $module
            ? route('lms.modules.edit', $module)
            : route('lms.courses.index');

        return redirect($redirectRoute)
            ->with('success', 'Assignment deleted successfully.');
    }

    /**
     * Show submission form for student
     */
    public function submitForm(Assignment $assignment) {
        $student = Auth::guard('student')->user();

        if (!$student) {
            abort(403, 'Student authentication required.');
        }

        if (!$assignment->canStudentSubmit($student->id)) {
            return redirect()
                ->route('lms.assignments.show', $assignment)
                ->with('error', 'You cannot submit to this assignment.');
        }

        $submission = $assignment->getSubmissionForStudent($student->id);

        return view('lms.assignments.submit', compact('assignment', 'submission'));
    }

    /**
     * Submit assignment (student)
     */
    public function submit(SubmitAssignmentRequest $request, Assignment $assignment) {
        $student = Auth::guard('student')->user();
        $validated = $request->validated();

        return DB::transaction(function () use ($request, $assignment, $student, $validated) {
            // Lock to prevent duplicate submissions
            $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            $submission = null;
            $attemptNumber = 1;

            if ($existingSubmission) {
                // Check if already submitted and resubmission not allowed
                if ($existingSubmission->status === 'submitted' && !$assignment->allow_resubmission) {
                    return redirect()
                        ->route('lms.assignments.show', $assignment)
                        ->with('info', 'You have already submitted this assignment.');
                }

                // If resubmitting, increment attempt and clean up old files
                if ($existingSubmission->status === 'submitted' && $assignment->allow_resubmission) {
                    $attemptNumber = $existingSubmission->attempt_number + 1;
                    $existingSubmission->deleteFiles();
                } elseif ($existingSubmission->status === 'draft') {
                    // Update existing draft
                    $submission = $existingSubmission;
                    $attemptNumber = $existingSubmission->attempt_number;
                }
            }

            if (!$submission) {
                $submission = AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'attempt_number' => $attemptNumber,
                    'status' => 'draft',
                ]);
            }

            // Update submission text
            if (isset($validated['submission_text'])) {
                $submission->update(['submission_text' => $validated['submission_text']]);
            }

            // Handle file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('lms/submissions/' . $submission->id, 'public');
                    $submission->addFile(
                        $path,
                        $file->getClientOriginalName(),
                        $file->getMimeType(),
                        $file->getSize()
                    );
                }
            }

            // Submit the assignment
            $submission->submit();

            return redirect()
                ->route('lms.assignments.show', $assignment)
                ->with('success', 'Assignment submitted successfully.');
        });
    }

    /**
     * View all submissions for an assignment (teacher)
     */
    public function submissions(Assignment $assignment) {
        Gate::authorize('grade-lms-content');

        $assignment->load('contentItem.module.course');

        $submissions = $assignment->submissions()
            ->with(['student', 'grader', 'attachedFiles'])
            ->submitted()
            ->orderBy('submitted_at', 'desc')
            ->paginate(25);

        $statsRow = $assignment->submissions()
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN submitted_at IS NOT NULL THEN 1 END) as submitted,
                COUNT(CASE WHEN status IN ('graded', 'returned') THEN 1 END) as graded,
                COUNT(CASE WHEN submitted_at IS NOT NULL AND status = 'submitted' THEN 1 END) as needs_grading
            ")->first();

        $stats = [
            'total' => $statsRow->total,
            'submitted' => $statsRow->submitted,
            'graded' => $statsRow->graded,
            'needs_grading' => $statsRow->needs_grading,
        ];

        return view('lms.assignments.submissions', compact('assignment', 'submissions', 'stats'));
    }

    /**
     * View enrolled students and their submission status (teacher)
     */
    public function enrollments(Request $request, Assignment $assignment) {
        Gate::authorize('grade-lms-content');

        $assignment->load('contentItem.module.course');
        $course = $assignment->contentItem?->module?->course;

        if (!$course) {
            abort(404, 'Assignment is not linked to a course.');
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

        // Get submission stats grouped by student
        $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->whereNotNull('submitted_at')
            ->selectRaw("student_id, COUNT(*) as submission_count, MAX(COALESCE(score_after_penalty, score)) as best_score, MAX(CASE WHEN status = 'graded' THEN 1 ELSE 0 END) as is_graded")
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $enrolledCount = Enrollment::where('course_id', $course->id)->where('status', 'active')->count();
        $submittedCount = $submissions->count();

        return view('lms.assignments.enrollments', compact('assignment', 'enrollments', 'submissions', 'enrolledCount', 'submittedCount'));
    }

    /**
     * Grade submission form
     */
    public function gradeForm(AssignmentSubmission $submission) {
        Gate::authorize('grade-lms-content');

        $submission->load(['assignment.rubric.criteria.levels', 'student', 'attachedFiles']);

        return view('lms.assignments.grade', compact('submission'));
    }

    /**
     * Save grade for submission
     */
    public function saveGrade(Request $request, AssignmentSubmission $submission) {
        Gate::authorize('grade-lms-content');

        $assignment = $submission->assignment;

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:' . $assignment->max_points,
            'feedback' => 'nullable|string|max:10000',
            'rubric_scores' => 'nullable|array',
            'rubric_scores.*.level_id' => 'nullable|exists:lms_rubric_levels,id',
            'rubric_scores.*.points' => 'nullable|numeric|min:0',
            'rubric_scores.*.comment' => 'nullable|string',
        ]);

        $submission->grade(
            $validated['score'],
            $validated['feedback'] ?? null,
            Auth::id(),
            $validated['rubric_scores'] ?? null
        );

        return redirect()
            ->route('lms.assignments.submissions', $assignment)
            ->with('success', 'Submission graded successfully.');
    }

    /**
     * Download submission file
     */
    public function downloadFile(SubmissionFile $file) {
        $student = Auth::guard('student')->user();
        $user = Auth::user();

        // Check access
        $canAccess = false;

        if ($user && Gate::allows('grade-lms-content')) {
            $canAccess = true;
        } elseif ($student && $file->submission->student_id === $student->id) {
            $canAccess = true;
        }

        if (!$canAccess) {
            abort(403);
        }

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    /**
     * Download assignment attachment (instructor reference material)
     */
    public function downloadAttachment(AssignmentAttachment $attachment) {
        $student = Auth::guard('student')->user();
        $user = Auth::user();

        // Check access - instructors can always download, students need enrollment check
        $canAccess = false;

        if ($user && Gate::allows('manage-lms-content')) {
            $canAccess = true;
        } elseif ($student) {
            // Check if student is enrolled in the course containing this assignment
            $assignment = $attachment->assignment;
            $contentItem = $assignment->contentItem;

            if ($contentItem && $contentItem->module) {
                $courseId = $contentItem->module->course_id;
                $isEnrolled = Enrollment::where('course_id', $courseId)
                    ->where('student_id', $student->id)
                    ->where('status', 'active')
                    ->exists();

                if ($isEnrolled && $assignment->status === 'published') {
                    $canAccess = true;
                }
            }
        }

        if (!$canAccess) {
            abort(403);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }
}
