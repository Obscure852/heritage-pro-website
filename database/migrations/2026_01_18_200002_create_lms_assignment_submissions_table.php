<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('lms_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->integer('attempt_number')->default(1);

            // Submission content
            $table->text('submission_text')->nullable();
            $table->json('files')->nullable(); // [{path, original_name, size, mime_type}]

            // Submission metadata
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->integer('days_late')->default(0);
            $table->string('ip_address')->nullable();

            // Grading
            $table->enum('status', ['draft', 'submitted', 'grading', 'graded', 'returned'])->default('draft');
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('score_after_penalty', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->json('rubric_scores')->nullable(); // {criterion_id: {level_id, points, comment}}
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['assignment_id', 'student_id', 'attempt_number'], 'lms_submissions_unique');
            $table->index(['assignment_id', 'status']);
            $table->index(['student_id', 'status']);
        });

        // Submission files - detailed file tracking
        Schema::create('lms_submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('lms_assignment_submissions')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->timestamps();

            $table->index('submission_id');
        });

        // Submission comments - for teacher-student communication
        Schema::create('lms_submission_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('lms_assignment_submissions')->cascadeOnDelete();
            $table->morphs('author'); // User or Student
            $table->text('comment');
            $table->boolean('is_private')->default(false); // Only visible to graders
            $table->timestamps();

            $table->index('submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_submission_comments');
        Schema::dropIfExists('lms_submission_files');
        Schema::dropIfExists('lms_assignment_submissions');
    }
};
