<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the syllabi table — the top-level curriculum document for a subject at a given grade/level.
 *
 * A syllabus defines what topics and objectives are taught for a subject in a specific grade and level.
 * Multiple schemes of work can reference a single syllabus.
 *
 * Requirements: FOUN-01
 */
return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('syllabi')) {
            return;
        }

        Schema::create('syllabi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id')->index();
            $table->json('grades');
            $table->string('level', 30);
            $table->unsignedBigInteger('document_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->json('cached_structure')->nullable();
            $table->timestamp('cached_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->onDelete('cascade');

            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('syllabi');
    }
};
