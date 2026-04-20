<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_favorites pivot table for users to bookmark documents.
 * Uses composite primary key (user_id, document_id) — no auto-increment id.
 * No updated_at column — favorites are simple toggles.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_favorites')) {
            return;
        }

        Schema::create('document_favorites', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('document_id');
            $table->timestamp('created_at')->nullable();

            // Composite primary key
            $table->primary(['user_id', 'document_id']);

            // Foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');

            // Index for reverse lookups (find users who favorited a document)
            $table->index('document_id', 'idx_favorites_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_favorites');
    }
};
