<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the test_syllabus_objectives pivot table — links tests to syllabus objectives.
 *
 * Stub for Phase 6 (Assessment Alignment). Connects existing tests to the specific
 * syllabus objectives they assess, enabling objective-level coverage reporting
 * in the HOD dashboard (e.g. "Which objectives are never tested?").
 *
 * Requirements: FOUN-01 (Phase 6 stub)
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('test_syllabus_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_id');
            $table->unsignedBigInteger('syllabus_objective_id');
            $table->timestamps();

            $table->foreign('test_id')
                ->references('id')
                ->on('tests')
                ->onDelete('cascade');

            $table->foreign('syllabus_objective_id')
                ->references('id')
                ->on('syllabus_objectives')
                ->onDelete('cascade');

            // Prevent duplicate links between the same test and objective
            $table->unique(
                ['test_id', 'syllabus_objective_id'],
                'uniq_test_syllabus_objective'
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('test_syllabus_objectives');
    }
};
