<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('passing_threshold_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_type', 20)->nullable()->comment('Junior, Senior, Primary, or null for all');
            $table->foreignId('grade_id')->nullable()->constrained('grades')->cascadeOnDelete();
            $table->foreignId('grade_subject_id')->nullable()->constrained('grade_subject')->cascadeOnDelete();
            $table->string('test_type', 20)->nullable()->comment('CA, Exam, Exercise, or null for all');
            $table->json('thresholds')->comment('Array of {name, max_percentage, color}');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Composite index for efficient threshold lookups
            $table->index(
                ['school_type', 'grade_id', 'grade_subject_id', 'test_type', 'is_active'],
                'idx_threshold_lookup'
            );
            $table->index('grade_subject_id', 'idx_grade_subject');

            // Unique constraint to prevent duplicate settings for same scope
            $table->unique(
                ['school_type', 'grade_id', 'grade_subject_id', 'test_type'],
                'unique_threshold_scope'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passing_threshold_settings');
    }
};
