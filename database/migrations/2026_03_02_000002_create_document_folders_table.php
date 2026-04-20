<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_folders table for hierarchical folder organization.
 * Supports institutional, personal, shared, and department repository types (FLD-06).
 * Includes materialized path and depth columns for efficient hierarchy queries.
 *
 * Requirements: FLD-06 (repository types)
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_folders')) {
            return;
        }

        Schema::create('document_folders', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->string('repository_type', 20)->default('personal');  // institutional, personal, shared, department (FLD-06)
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('visibility', 20)->default('private');        // private, internal, public
            $table->boolean('inherit_permissions')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('path', 1000)->nullable();                    // Materialized path for hierarchy queries
            $table->tinyInteger('depth')->unsigned()->default(0);
            $table->unsignedInteger('document_count')->default(0);       // Denormalized count
            $table->unsignedBigInteger('total_size_bytes')->default(0);  // Denormalized size
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('parent_id')
                ->references('id')
                ->on('document_folders')
                ->onDelete('cascade');

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('set null');

            // Indexes for efficient querying
            $table->index('owner_id', 'idx_folders_owner');
            $table->index('parent_id', 'idx_folders_parent');
            $table->index('repository_type', 'idx_folders_type');
            $table->index('deleted_at', 'idx_folders_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_folders');
    }
};
