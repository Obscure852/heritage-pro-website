<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_comments table for threaded comments and annotations on documents.
 * Supports parent_id for threaded replies and position fields for PDF annotations.
 * Comments can be resolved as part of the review workflow.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_comments')) {
            return;
        }

        Schema::create('document_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('version_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parent_id')->nullable(); // For threaded replies

            // Content
            $table->text('content');

            // Location (for PDF annotations)
            $table->unsignedInteger('page_number')->nullable();
            $table->decimal('position_x', 5, 2)->nullable(); // Percentage from left
            $table->decimal('position_y', 5, 2)->nullable(); // Percentage from top

            // Status
            $table->boolean('is_resolved')->default(false);
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            $table->timestamp('resolved_at')->nullable();

            // Edit tracking
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');

            $table->foreign('version_id')
                ->references('id')
                ->on('document_versions')
                ->onDelete('set null');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('parent_id')
                ->references('id')
                ->on('document_comments')
                ->onDelete('cascade');

            $table->foreign('resolved_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes for efficient querying
            $table->index('document_id', 'idx_comments_document');
            $table->index('user_id', 'idx_comments_user');
            $table->index('parent_id', 'idx_comments_parent');
            $table->index('is_resolved', 'idx_comments_resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_comments');
    }
};
