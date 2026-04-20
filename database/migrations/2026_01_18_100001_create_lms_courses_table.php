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
        Schema::create('lms_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
            $table->foreignId('grade_subject_id')->nullable()->constrained('grade_subject')->onDelete('set null');
            $table->foreignId('term_id')->constrained('terms')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->enum('visibility', ['public', 'enrolled', 'private'])->default('enrolled');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('estimated_duration_minutes')->default(0);
            $table->decimal('passing_score', 5, 2)->default(60.00);
            $table->boolean('allow_self_enrollment')->default(false);
            $table->integer('max_attempts')->nullable();
            $table->boolean('sequential_content')->default(true);
            $table->boolean('adaptive_learning_enabled')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['grade_id', 'term_id']);
            $table->index(['instructor_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_courses');
    }
};
