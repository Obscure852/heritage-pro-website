<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the scheme_entry_objectives pivot table.
 *
 * Links scheme_of_work_entries to syllabus_objectives, allowing teachers
 * to specify which learning objectives are addressed in a given week's entry.
 * One entry can cover multiple objectives; one objective can appear in multiple entries.
 *
 * Requirements: FOUN-01
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('scheme_entry_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheme_of_work_entry_id');
            $table->unsignedBigInteger('syllabus_objective_id');
            $table->timestamps();

            $table->foreign('scheme_of_work_entry_id')
                ->references('id')
                ->on('scheme_of_work_entries')
                ->onDelete('cascade');

            $table->foreign('syllabus_objective_id')
                ->references('id')
                ->on('syllabus_objectives')
                ->onDelete('cascade');

            // Prevent duplicate pivot rows
            $table->unique(
                ['scheme_of_work_entry_id', 'syllabus_objective_id'],
                'uniq_entry_objective'
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('scheme_entry_objectives');
    }
};
