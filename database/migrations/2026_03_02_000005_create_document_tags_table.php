<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_tags table for tagging documents with searchable labels.
 * Supports official (admin-only) and user-created tags.
 * Includes denormalized usage_count for efficient tag cloud/sorting.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_tags')) {
            return;
        }

        Schema::create('document_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->string('color', 7)->nullable()->default('#007bff');
            $table->boolean('is_official')->default(false);            // Official tags can only be created by admins
            $table->unsignedInteger('usage_count')->default(0);        // Denormalized for performance
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('created_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes for efficient querying
            $table->index('usage_count', 'idx_tags_usage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_tags');
    }
};
