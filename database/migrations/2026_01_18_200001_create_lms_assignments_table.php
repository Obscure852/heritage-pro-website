<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();

            // Submission settings
            $table->enum('submission_type', ['file', 'text', 'both'])->default('file');
            $table->json('allowed_file_types')->nullable(); // ['pdf', 'doc', 'docx']
            $table->integer('max_file_size_mb')->default(10);
            $table->integer('max_files')->default(1);

            // Dates
            $table->timestamp('available_from')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('cutoff_date')->nullable(); // No submissions after this

            // Points and grading
            $table->decimal('max_points', 8, 2)->default(100);
            $table->foreignId('rubric_id')->nullable()->constrained('lms_rubrics')->nullOnDelete();

            // Settings
            $table->boolean('allow_late_submissions')->default(true);
            $table->decimal('late_penalty_percent', 5, 2)->default(0); // % deducted per day late
            $table->integer('max_attempts')->nullable(); // null = unlimited
            $table->boolean('allow_resubmission')->default(false);
            $table->boolean('require_submission_text')->default(false);
            $table->boolean('anonymous_grading')->default(false);

            // Peer review
            $table->boolean('peer_review_enabled')->default(false);
            $table->integer('peer_reviews_per_student')->nullable();

            // Grade integration
            $table->foreignId('grade_subject_id')->nullable()->constrained('grade_subject')->nullOnDelete();
            $table->boolean('sync_to_gradebook')->default(false);

            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_assignments');
    }
};
