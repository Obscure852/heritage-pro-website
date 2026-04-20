<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rubrics table - reusable grading rubrics
        Schema::create('lms_rubrics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_template')->default(false); // Can be reused
            $table->decimal('total_points', 8, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_by');
            $table->index('is_template');
        });

        // Rubric criteria - the rows/criteria of a rubric
        Schema::create('lms_rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained('lms_rubrics')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('max_points', 8, 2);
            $table->integer('sequence')->default(0);
            $table->timestamps();

            $table->index(['rubric_id', 'sequence']);
        });

        // Rubric levels - the columns/levels for each criterion (e.g., Excellent, Good, Fair, Poor)
        Schema::create('lms_rubric_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterion_id')->constrained('lms_rubric_criteria')->cascadeOnDelete();
            $table->string('title'); // e.g., "Excellent", "Good", "Needs Improvement"
            $table->text('description')->nullable();
            $table->decimal('points', 8, 2);
            $table->integer('sequence')->default(0);
            $table->timestamps();

            $table->index(['criterion_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_rubric_levels');
        Schema::dropIfExists('lms_rubric_criteria');
        Schema::dropIfExists('lms_rubrics');
    }
};
