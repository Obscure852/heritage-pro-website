<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_tag pivot table for many-to-many relationship
 * between documents and document_tags.
 * Uses composite primary key (document_id, tag_id) — no auto-increment id.
 * No updated_at column — tag assignment is immutable once created.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_tag')) {
            return;
        }

        Schema::create('document_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('tagged_by_user_id')->nullable();
            $table->timestamp('created_at')->nullable();

            // Composite primary key
            $table->primary(['document_id', 'tag_id']);

            // Foreign key constraints
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');

            $table->foreign('tag_id')
                ->references('id')
                ->on('document_tags')
                ->onDelete('cascade');

            $table->foreign('tagged_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Index for reverse lookups (find documents by tag)
            $table->index('tag_id', 'idx_document_tag_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_tag');
    }
};
