<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_versions table for tracking version history of documents.
 * Each version stores its own file storage reference, allowing any version to be restored.
 * Version numbers follow Major.Minor format (e.g., 1.0, 1.1, 2.0).
 *
 * Requirements: DOC-04 (checksum per version), DOC-06 (storage)
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_versions')) {
            return;
        }

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->string('version_number', 10);                   // e.g., "1.0", "1.1", "2.0"
            $table->string('version_type', 10)->default('minor');   // major, minor

            // File Storage
            $table->string('storage_disk', 50)->default('documents');
            $table->string('storage_path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum_sha256', 64);

            // Metadata
            $table->text('version_notes')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id');

            // Status
            $table->boolean('is_current')->default(false);

            $table->timestamp('created_at')->nullable();

            // Foreign key constraints
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');

            $table->foreign('uploaded_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Unique constraint: one version number per document
            $table->unique(['document_id', 'version_number'], 'uk_document_version');

            // Indexes for efficient querying
            $table->index('document_id', 'idx_versions_document');
            $table->index(['document_id', 'is_current'], 'idx_versions_current');
            $table->index('uploaded_by_user_id', 'idx_versions_uploader');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_versions');
    }
};
