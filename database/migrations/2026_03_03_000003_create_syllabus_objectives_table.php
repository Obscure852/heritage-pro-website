<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the syllabus_objectives table — learning objectives within a topic.
 *
 * Each objective belongs to a syllabus topic and has a sequence number and short code.
 * The cognitive_level field (e.g. Knowledge, Comprehension, Application) follows
 * Bloom's taxonomy to classify the depth of learning expected.
 *
 * Objectives can be linked to scheme entries and assessment tests via pivot tables.
 *
 * Requirements: FOUN-01
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('syllabus_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('syllabus_topic_id')->index();
            $table->unsignedSmallInteger('sequence');
            $table->string('code', 30);
            $table->text('objective_text');
            $table->string('cognitive_level', 30)->nullable();
            $table->timestamps();

            $table->foreign('syllabus_topic_id')
                ->references('id')
                ->on('syllabus_topics')
                ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('syllabus_objectives');
    }
};
