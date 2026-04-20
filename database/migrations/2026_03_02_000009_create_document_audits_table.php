<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_audits table for comprehensive audit trail logging.
 * Records all document actions including views, downloads, edits, shares, and workflow changes.
 * Audit logs are immutable — only created_at, no updated_at.
 *
 * Uses string for action column (NOT enum) due to large number of possible values.
 * Metadata JSON field captures action-specific context.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_audits')) {
            return;
        }

        Schema::create('document_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('version_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // NULL for anonymous/public access

            // Action (string, not enum — too many values)
            $table->string('action', 50); // created, viewed, downloaded, previewed, updated, versioned, renamed, moved, shared, unshared, etc.

            // Context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('session_id', 100)->nullable();

            // Additional metadata (JSON)
            $table->json('metadata')->nullable();

            // Immutable — only created_at, no updated_at
            $table->timestamp('created_at')->useCurrent();

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
                ->onDelete('set null');

            // Indexes for efficient querying
            $table->index('document_id', 'idx_audits_document');
            $table->index('user_id', 'idx_audits_user');
            $table->index('action', 'idx_audits_action');
            $table->index('created_at', 'idx_audits_created');
            $table->index(['user_id', 'action', 'created_at'], 'idx_audits_user_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_audits');
    }
};
