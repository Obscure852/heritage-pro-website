<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the lesson_plans table — detailed daily lesson preparation.
 *
 * A lesson plan is a detailed daily preparation document created by a teacher.
 * It can optionally be linked to a scheme of work (and a specific entry within it)
 * for curriculum alignment tracking.
 *
 * Requirements: FOUN-01
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();

            // Optional links to the scheme hierarchy
            $table->unsignedBigInteger('scheme_of_work_id')->nullable();
            $table->unsignedBigInteger('scheme_of_work_entry_id')->nullable();

            $table->unsignedBigInteger('teacher_id')->index();
            $table->date('date');
            $table->string('period', 30)->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();

            // Lesson content
            $table->string('topic', 255);
            $table->string('sub_topic', 255)->nullable();
            $table->text('learning_objectives')->nullable();
            $table->text('prerequisite_knowledge')->nullable();

            // Lesson phases
            $table->text('introduction')->nullable();
            $table->text('development')->nullable();
            $table->text('conclusion')->nullable();

            // Assessment and differentiation
            $table->text('assessment')->nullable();
            $table->text('differentiation')->nullable();
            $table->text('resources')->nullable();
            $table->text('homework')->nullable();

            // Status and reflection
            $table->string('status', 30)->default('planned');
            $table->timestamp('taught_at')->nullable();
            $table->text('reflection_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('scheme_of_work_id')
                ->references('id')
                ->on('schemes_of_work')
                ->onDelete('set null');

            $table->foreign('scheme_of_work_entry_id')
                ->references('id')
                ->on('scheme_of_work_entries')
                ->onDelete('set null');

            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('lesson_plans');
    }
};
