<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_approvals table for the document approval workflow.
 * Tracks submission, review, and approval status for each document version.
 * Supports multi-level approval with workflow_step ordering.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_approvals')) {
            return;
        }

        Schema::create('document_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('version_id');

            // Workflow
            $table->unsignedInteger('workflow_step')->default(1); // For multi-level approval

            // Reviewer
            $table->unsignedBigInteger('reviewer_id');

            // Status (string, not enum)
            $table->string('status', 30)->default('pending'); // pending, in_review, approved, rejected, revision_required

            // Submission
            $table->unsignedBigInteger('submitted_by_user_id');
            $table->text('submission_notes')->nullable();
            $table->timestamp('submitted_at');

            // Review
            $table->text('review_comments')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            // Deadline
            $table->date('due_date')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');

            $table->foreign('version_id')
                ->references('id')
                ->on('document_versions')
                ->onDelete('cascade');

            $table->foreign('reviewer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('submitted_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes for efficient querying
            $table->index('document_id', 'idx_approvals_document');
            $table->index('reviewer_id', 'idx_approvals_reviewer');
            $table->index('status', 'idx_approvals_status');
            $table->index('due_date', 'idx_approvals_due');
            $table->index(['reviewer_id', 'status'], 'idx_approvals_pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_approvals');
    }
};
