<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the documents table — the core table of the Document Management System.
 * Stores document metadata, storage references, status, version info, and flags.
 *
 * Allowed file types are configured in config/documents.php (DOC-03).
 * File integrity ensured via SHA-256 checksum (DOC-04).
 * Storage paths reference the 'documents' disk (DOC-06).
 * ULID provides public-facing identifier (DOC-09).
 *
 * Requirements: DOC-03 (file types), DOC-04 (checksum), DOC-06 (storage), DOC-09 (ULID)
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('documents')) {
            return;
        }

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();                              // Public-facing identifier (DOC-09)
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('source_type', 30)->default('upload');
            $table->string('external_url', 2048)->nullable();

            // File Storage (DOC-04, DOC-06)
            $table->string('storage_disk', 50)->nullable()->default('documents');
            $table->string('storage_path', 500)->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->char('checksum_sha256', 64)->nullable();                 // SHA-256 integrity check (DOC-04)

            // Organization
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('owner_id');

            // Status & Workflow (string, not enum — matches StaffAttendanceRecord pattern)
            $table->string('status', 30)->default('draft');                  // draft, pending_review, under_review, revision_required, approved, published, archived, deleted
            $table->string('visibility', 20)->default('private');            // private, internal, public

            // Version Control
            $table->string('current_version', 10)->default('1.0');
            $table->unsignedInteger('version_count')->default(1);

            // Dates
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            // Flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_template')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('locked_by_user_id')->nullable();
            $table->timestamp('locked_at')->nullable();

            // Legal Hold
            $table->boolean('legal_hold')->default(false);
            $table->text('legal_hold_reason')->nullable();
            $table->unsignedBigInteger('legal_hold_by_user_id')->nullable();
            $table->timestamp('legal_hold_at')->nullable();

            // Counters
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);

            // Full-text Search (for future v2 search)
            $table->timestamp('content_indexed_at')->nullable();
            $table->longText('content_text')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('folder_id')
                ->references('id')
                ->on('document_folders')
                ->onDelete('set null');

            $table->foreign('category_id')
                ->references('id')
                ->on('document_categories')
                ->onDelete('set null');

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('locked_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('legal_hold_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes for efficient querying
            $table->index('folder_id', 'idx_documents_folder');
            $table->index('category_id', 'idx_documents_category');
            $table->index('owner_id', 'idx_documents_owner');
            $table->index('source_type', 'idx_documents_source_type');
            $table->index('status', 'idx_documents_status');
            $table->index('visibility', 'idx_documents_visibility');
            $table->index('expiry_date', 'idx_documents_expiry');
            $table->index('is_featured', 'idx_documents_featured');
            $table->index('deleted_at', 'idx_documents_deleted');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // Composite search index with prefix length on TEXT column (MySQL requirement)
            DB::statement('ALTER TABLE `documents` ADD INDEX `idx_documents_search` (`title`, `description`(100))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('documents');
    }
};
