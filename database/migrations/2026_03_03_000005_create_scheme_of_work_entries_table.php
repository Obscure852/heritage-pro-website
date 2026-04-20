<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the scheme_of_work_entries table — weekly breakdown of a scheme of work.
 *
 * Each entry represents one week's planned content within a scheme of work.
 * Entries can optionally reference a syllabus topic to indicate curriculum coverage.
 * Teachers fill in what they plan to teach (topic, activities, resources, assessment).
 *
 * Requirements: FOUN-01
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('scheme_of_work_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheme_of_work_id')->index();
            $table->unsignedSmallInteger('week_number');

            // Optional link to the syllabus topic being covered this week
            $table->unsignedBigInteger('syllabus_topic_id')->nullable();

            // Teacher-entered planning content
            $table->string('topic', 255)->nullable();
            $table->string('sub_topic', 255)->nullable();
            $table->text('learning_objectives')->nullable();
            $table->text('teaching_activities')->nullable();
            $table->text('learning_activities')->nullable();
            $table->text('resources')->nullable();
            $table->text('assessment_methods')->nullable();
            $table->text('homework')->nullable();
            $table->text('references_text')->nullable();
            $table->text('remarks')->nullable();

            // Entry status: planned, taught, skipped
            $table->string('status', 30)->default('planned');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('scheme_of_work_id')
                ->references('id')
                ->on('schemes_of_work')
                ->onDelete('cascade');

            $table->foreign('syllabus_topic_id')
                ->references('id')
                ->on('syllabus_topics')
                ->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('scheme_of_work_entries');
    }
};
