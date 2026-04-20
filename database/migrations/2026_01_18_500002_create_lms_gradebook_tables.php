<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Grade scales (letter grades, percentages, etc.)
        Schema::create('lms_grade_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('letter'); // letter, percentage, points, pass_fail, custom
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Grade scale items (A, B, C or 90-100, 80-89, etc.)
        Schema::create('lms_grade_scale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_scale_id')->constrained('lms_grade_scales')->cascadeOnDelete();
            $table->string('grade'); // A+, A, B+, etc.
            $table->string('label')->nullable(); // Excellent, Good, etc.
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->decimal('grade_points', 4, 2)->nullable(); // GPA points (4.0, 3.7, etc.)
            $table->string('color')->default('#10b981');
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['grade_scale_id', 'position']);
        });

        // Course gradebook settings
        Schema::create('lms_gradebook_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('grade_scale_id')->nullable()->constrained('lms_grade_scales')->nullOnDelete();
            $table->string('grading_method')->default('weighted'); // weighted, points, simple_average
            $table->decimal('passing_grade', 5, 2)->default(50);
            $table->boolean('show_grade_to_students')->default(true);
            $table->boolean('show_rank_to_students')->default(false);
            $table->boolean('show_statistics')->default(true);
            $table->boolean('drop_lowest')->default(false);
            $table->integer('drop_lowest_count')->default(1);
            $table->boolean('include_incomplete')->default(false); // Include 0 for missing
            $table->json('settings')->nullable(); // Additional settings
            $table->timestamps();

            $table->unique('course_id');
        });

        // Grade categories (assignments, quizzes, exams, participation)
        Schema::create('lms_grade_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight', 5, 2)->default(0); // Percentage weight (0-100)
            $table->integer('position')->default(0);
            $table->boolean('drop_lowest')->default(false);
            $table->integer('drop_lowest_count')->default(0);
            $table->string('color')->default('#6366f1');
            $table->boolean('is_extra_credit')->default(false);
            $table->timestamps();

            $table->index(['course_id', 'position']);
        });

        // Grade items (individual assignments, quizzes, etc.)
        Schema::create('lms_grade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('lms_grade_categories')->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('manual'); // manual, assignment, quiz, attendance, participation
            $table->nullableMorphs('gradeable'); // Links to assignment, quiz, etc.
            $table->decimal('max_points', 8, 2)->default(100);
            $table->decimal('weight', 5, 2)->nullable(); // Override category weight
            $table->boolean('is_extra_credit')->default(false);
            $table->boolean('is_excluded')->default(false); // Exclude from final grade
            $table->boolean('is_hidden')->default(false); // Hide from students
            $table->date('due_date')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'category_id']);
        });

        // Student grades
        Schema::create('lms_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_item_id')->constrained('lms_grade_items')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
            $table->decimal('score', 8, 2)->nullable(); // Raw score
            $table->decimal('max_score', 8, 2)->nullable(); // Max possible (if different from item)
            $table->decimal('percentage', 5, 2)->nullable(); // Calculated percentage
            $table->string('letter_grade')->nullable(); // Calculated letter grade
            $table->string('status')->default('pending'); // pending, graded, excused, incomplete, dropped
            $table->text('feedback')->nullable();
            $table->boolean('is_late')->default(false);
            $table->decimal('late_penalty', 5, 2)->default(0); // Percentage deducted
            $table->boolean('is_overridden')->default(false); // Manual override
            $table->decimal('original_score', 8, 2)->nullable(); // Before override
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['grade_item_id', 'student_id']);
            $table->index(['student_id', 'enrollment_id']);
        });

        // Grade history/audit log
        Schema::create('lms_grade_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('lms_grades')->cascadeOnDelete();
            $table->decimal('old_score', 8, 2)->nullable();
            $table->decimal('new_score', 8, 2)->nullable();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->string('action'); // created, updated, excused, dropped, overridden
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['grade_id', 'changed_at']);
        });

        // Cached course grades (final grades)
        Schema::create('lms_course_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
            $table->decimal('total_points_earned', 10, 2)->default(0);
            $table->decimal('total_points_possible', 10, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('weighted_percentage', 5, 2)->default(0);
            $table->string('letter_grade')->nullable();
            $table->decimal('gpa_points', 4, 2)->nullable();
            $table->integer('rank')->nullable();
            $table->integer('items_graded')->default(0);
            $table->integer('items_total')->default(0);
            $table->boolean('is_passing')->default(false);
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('category_grades')->nullable(); // Breakdown by category
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
            $table->index(['course_id', 'rank']);
        });

        // Grade comments/annotations
        Schema::create('lms_grade_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('lms_grades')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment');
            $table->boolean('is_private')->default(false); // Only visible to instructors
            $table->timestamps();

            $table->index('grade_id');
        });

        // Grade item to rubric link
        Schema::create('lms_grade_item_rubric', function (Blueprint $table) {
            $table->foreignId('grade_item_id')->constrained('lms_grade_items')->cascadeOnDelete();
            $table->foreignId('rubric_id')->constrained('lms_rubrics')->cascadeOnDelete();

            $table->primary(['grade_item_id', 'rubric_id']);
        });

        // Rubric scores (student rubric evaluations)
        Schema::create('lms_rubric_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('lms_grades')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('lms_rubric_criteria')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('lms_rubric_levels')->nullOnDelete();
            $table->decimal('score', 8, 2)->nullable(); // Can override level points
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['grade_id', 'criterion_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('lms_rubric_scores');
        Schema::dropIfExists('lms_grade_item_rubric');
        Schema::dropIfExists('lms_grade_comments');
        Schema::dropIfExists('lms_course_grades');
        Schema::dropIfExists('lms_grade_history');
        Schema::dropIfExists('lms_grades');
        Schema::dropIfExists('lms_grade_items');
        Schema::dropIfExists('lms_grade_categories');
        Schema::dropIfExists('lms_gradebook_settings');
        Schema::dropIfExists('lms_grade_scale_items');
        Schema::dropIfExists('lms_grade_scales');
    }
};
