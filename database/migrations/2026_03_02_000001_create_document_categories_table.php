<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_categories table for organizing documents into hierarchical categories.
 * Each category can have a parent for nested category structures.
 * Supports retention period defaults and approval requirements per category.
 *
 * Requirements: DOC-03 (file type organization)
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_categories')) {
            return;
        }

        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('icon', 50)->nullable()->default('folder');
            $table->string('color', 7)->nullable()->default('#6c757d');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('retention_days')->nullable()->default(2555); // 7 years default
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Self-referencing foreign key for category hierarchy
            $table->foreign('parent_id')
                ->references('id')
                ->on('document_categories')
                ->onDelete('set null');

            // Indexes for efficient querying
            $table->index('slug', 'idx_categories_slug');
            $table->index('parent_id', 'idx_categories_parent');
            $table->index('is_active', 'idx_categories_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_categories');
    }
};
