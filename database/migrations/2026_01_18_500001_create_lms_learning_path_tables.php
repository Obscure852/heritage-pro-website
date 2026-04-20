<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Learning Paths - curated course sequences
        if (!Schema::hasTable('lms_learning_paths')) {
            Schema::create('lms_learning_paths', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable(); // JSON array of learning objectives
            $table->string('thumbnail')->nullable();
            $table->string('level')->default('beginner'); // beginner, intermediate, advanced, expert
            $table->integer('estimated_duration_hours')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('enforce_sequence')->default(true); // Must complete courses in order
            $table->boolean('allow_skip')->default(false); // Allow skipping courses
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'is_featured']);
            });
        }

        // Courses within a learning path
        if (!Schema::hasTable('lms_learning_path_courses')) {
            Schema::create('lms_learning_path_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained('lms_learning_paths')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_milestone')->default(false); // Marks a checkpoint
            $table->string('milestone_title')->nullable();
            $table->integer('unlock_after_days')->nullable(); // Drip content
            $table->timestamps();

            $table->unique(['learning_path_id', 'course_id']);
            $table->index(['learning_path_id', 'position']);
            });
        }

        // Prerequisites for courses within a path
        if (!Schema::hasTable('lms_learning_path_prerequisites')) {
            Schema::create('lms_learning_path_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_course_id')->constrained('lms_learning_path_courses')->cascadeOnDelete();
            $table->foreignId('prerequisite_course_id')->constrained('lms_learning_path_courses')->cascadeOnDelete();
            $table->integer('minimum_score')->nullable(); // Minimum completion score required
            $table->timestamps();

            $table->unique(['path_course_id', 'prerequisite_course_id'], 'lms_path_prereq_unique');
            });
        }

        // Student enrollments in learning paths
        if (!Schema::hasTable('lms_learning_path_enrollments')) {
            Schema::create('lms_learning_path_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained('lms_learning_paths')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('status')->default('active'); // active, completed, paused, expired
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->integer('courses_completed')->default(0);
            $table->integer('total_courses')->default(0);
            $table->timestamp('enrolled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['learning_path_id', 'student_id']);
            $table->index(['student_id', 'status']);
            });
        }

        // Track course completion within learning path
        if (!Schema::hasTable('lms_learning_path_progress')) {
            Schema::create('lms_learning_path_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('lms_learning_path_enrollments')->cascadeOnDelete();
            $table->foreignId('path_course_id')->constrained('lms_learning_path_courses')->cascadeOnDelete();
            $table->foreignId('course_enrollment_id')->nullable()->constrained('lms_enrollments')->nullOnDelete();
            $table->string('status')->default('locked'); // locked, available, in_progress, completed
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->decimal('grade', 5, 2)->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'path_course_id']);
            });
        }

        // Learning path categories/tags
        if (!Schema::hasTable('lms_learning_path_categories')) {
            Schema::create('lms_learning_path_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#6366f1');
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            });
        }

        // Path to category pivot
        if (!Schema::hasTable('lms_learning_path_category')) {
            Schema::create('lms_learning_path_category', function (Blueprint $table) {
            $table->foreignId('learning_path_id')->constrained('lms_learning_paths')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('lms_learning_path_categories')->cascadeOnDelete();

            $table->primary(['learning_path_id', 'category_id']);
            });
        }

        // Milestones/achievements within paths
        if (!Schema::hasTable('lms_learning_path_milestones')) {
            Schema::create('lms_learning_path_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained('lms_learning_paths')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('fas fa-flag-checkered');
            $table->integer('position')->default(0);
            $table->integer('courses_required')->default(1); // Number of courses to complete
            $table->foreignId('badge_id')->nullable()->constrained('lms_badges')->nullOnDelete();
            $table->integer('points_awarded')->default(0);
            $table->timestamps();

            $table->index(['learning_path_id', 'position']);
            });
        }

        // Student milestone completions
        if (!Schema::hasTable('lms_path_milestone_completions')) {
            Schema::create('lms_path_milestone_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('lms_learning_path_enrollments')->cascadeOnDelete();
            $table->foreignId('milestone_id')->constrained('lms_learning_path_milestones')->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(['enrollment_id', 'milestone_id'], 'lms_milestone_completion_unique');
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('lms_path_milestone_completions');
        Schema::dropIfExists('lms_learning_path_milestones');
        Schema::dropIfExists('lms_learning_path_category');
        Schema::dropIfExists('lms_learning_path_categories');
        Schema::dropIfExists('lms_learning_path_progress');
        Schema::dropIfExists('lms_learning_path_enrollments');
        Schema::dropIfExists('lms_learning_path_prerequisites');
        Schema::dropIfExists('lms_learning_path_courses');
        Schema::dropIfExists('lms_learning_paths');
    }
};
