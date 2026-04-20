<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the standard_scheme_entries table — weekly breakdown of a standard scheme.
 *
 * Each entry represents one week's planned content within a standard scheme.
 * Entries can optionally reference a syllabus topic to indicate curriculum coverage.
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('standard_scheme_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_scheme_id')->index();
            $table->unsignedSmallInteger('week_number');

            // Optional link to the syllabus topic being covered this week
            $table->unsignedBigInteger('syllabus_topic_id')->nullable();

            // Planning content
            $table->string('topic', 255)->nullable();
            $table->string('sub_topic', 255)->nullable();
            $table->text('learning_objectives')->nullable();

            // Entry status: planned, taught, completed, skipped
            $table->string('status', 30)->default('planned');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('standard_scheme_id')
                ->references('id')->on('standard_schemes')
                ->onDelete('cascade');

            $table->foreign('syllabus_topic_id')
                ->references('id')->on('syllabus_topics')
                ->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('standard_scheme_entries');
    }
};
