<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_shares table for sharing documents with users, roles,
 * departments, or via public links. Uses polymorphic shareable_type/shareable_id
 * for flexible share targets. Public links use access_token with optional
 * password protection and view limits.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_shares')) {
            return;
        }

        Schema::create('document_shares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');

            // Polymorphic share target (user, role, department, public_link)
            $table->string('shareable_type', 50);
            $table->unsignedBigInteger('shareable_id')->nullable();  // NULL for public links

            // Permission (string, not enum)
            $table->string('permission_level', 20)->default('view'); // view, comment, edit, manage

            // Share metadata
            $table->unsignedBigInteger('shared_by_user_id');
            $table->text('message')->nullable();

            // Public link specific
            $table->string('access_token', 64)->nullable()->unique(); // For public links
            $table->string('password_hash', 255)->nullable();         // Optional password protection
            $table->boolean('allow_download')->default(true);
            $table->unsignedInteger('max_views')->nullable();         // NULL = unlimited
            $table->unsignedInteger('view_count')->default(0);

            // Expiration
            $table->timestamp('expires_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('revoked_by_user_id')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');

            $table->foreign('shared_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('revoked_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes for efficient querying
            $table->index('document_id', 'idx_shares_document');
            $table->index(['shareable_type', 'shareable_id'], 'idx_shares_shareable');
            $table->index('is_active', 'idx_shares_active');
            $table->index('expires_at', 'idx_shares_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_shares');
    }
};
