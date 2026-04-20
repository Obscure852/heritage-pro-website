<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Student activity logs (granular tracking)
        Schema::create('lms_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->cascadeOnDelete();
            $table->string('activity_type'); // login, content_view, quiz_start, quiz_submit, etc.
            $table->nullableMorphs('subject'); // The item being interacted with
            $table->json('metadata')->nullable(); // Additional context
            $table->integer('duration_seconds')->nullable(); // Time spent
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->timestamp('created_at');

            $table->index(['student_id', 'created_at']);
            $table->index(['course_id', 'activity_type']);
            $table->index('created_at');
        });

        // Daily engagement summaries (aggregated)
        Schema::create('lms_engagement_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->cascadeOnDelete();
            $table->date('date');
            $table->integer('total_time_seconds')->default(0);
            $table->integer('content_views')->default(0);
            $table->integer('quiz_attempts')->default(0);
            $table->integer('assignment_submissions')->default(0);
            $table->integer('discussion_posts')->default(0);
            $table->integer('videos_watched')->default(0);
            $table->integer('login_count')->default(0);
            $table->decimal('progress_delta', 5, 2)->default(0); // Progress change
            $table->timestamps();

            $table->unique(['student_id', 'course_id', 'date']);
            $table->index(['course_id', 'date']);
        });

        // Course analytics snapshots (periodic aggregations)
        Schema::create('lms_course_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->date('date');
            $table->string('period')->default('daily'); // daily, weekly, monthly
            $table->integer('total_enrollments')->default(0);
            $table->integer('active_students')->default(0);
            $table->integer('new_enrollments')->default(0);
            $table->integer('completions')->default(0);
            $table->decimal('avg_progress', 5, 2)->default(0);
            $table->decimal('avg_grade', 5, 2)->nullable();
            $table->integer('total_time_spent')->default(0); // seconds
            $table->decimal('avg_time_per_student', 10, 2)->default(0);
            $table->integer('content_views')->default(0);
            $table->integer('quiz_attempts')->default(0);
            $table->integer('quiz_passes')->default(0);
            $table->integer('assignment_submissions')->default(0);
            $table->integer('discussion_posts')->default(0);
            $table->decimal('engagement_score', 5, 2)->default(0);
            $table->json('completion_funnel')->nullable(); // Module-by-module drop-off
            $table->json('grade_distribution')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'date', 'period']);
            $table->index(['course_id', 'period']);
        });

        // Content analytics
        Schema::create('lms_content_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('lms_content_items')->cascadeOnDelete();
            $table->date('date');
            $table->integer('views')->default(0);
            $table->integer('unique_views')->default(0);
            $table->integer('completions')->default(0);
            $table->integer('total_time_seconds')->default(0);
            $table->decimal('avg_time_seconds', 10, 2)->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('avg_score', 5, 2)->nullable(); // For quizzes
            $table->integer('drop_off_count')->default(0); // Started but didn't complete
            $table->timestamps();

            $table->unique(['content_id', 'date']);
        });

        // Quiz analytics
        Schema::create('lms_quiz_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->date('date');
            $table->integer('attempts')->default(0);
            $table->integer('completions')->default(0);
            $table->integer('passes')->default(0);
            $table->decimal('avg_score', 5, 2)->default(0);
            $table->decimal('median_score', 5, 2)->default(0);
            $table->decimal('highest_score', 5, 2)->default(0);
            $table->decimal('lowest_score', 5, 2)->default(0);
            $table->integer('avg_duration_seconds')->default(0);
            $table->json('question_analytics')->nullable(); // Per-question stats
            $table->json('score_distribution')->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'date']);
        });

        // Learning path analytics
        Schema::create('lms_learning_path_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained('lms_learning_paths')->cascadeOnDelete();
            $table->date('date');
            $table->integer('enrollments')->default(0);
            $table->integer('active_learners')->default(0);
            $table->integer('completions')->default(0);
            $table->decimal('avg_progress', 5, 2)->default(0);
            $table->integer('avg_completion_days')->nullable();
            $table->json('course_completion_rates')->nullable();
            $table->json('milestone_completion_rates')->nullable();
            $table->timestamps();

            $table->unique(['learning_path_id', 'date']);
        });

        // Student learning insights
        Schema::create('lms_student_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('insight_type'); // at_risk, high_performer, inactive, improving, etc.
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->cascadeOnDelete();
            $table->string('severity')->default('info'); // info, warning, critical
            $table->string('title');
            $table->text('description');
            $table->json('data')->nullable();
            $table->boolean('is_dismissed')->default(false);
            $table->timestamp('generated_at');
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'is_dismissed']);
            $table->index(['course_id', 'insight_type']);
        });

        // Report definitions (saved reports)
        Schema::create('lms_report_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // course_progress, engagement, grades, completion, custom
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->json('chart_config')->nullable();
            $table->string('schedule')->nullable(); // daily, weekly, monthly
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Generated reports
        Schema::create('lms_generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('definition_id')->nullable()->constrained('lms_report_definitions')->nullOnDelete();
            $table->string('name');
            $table->string('type');
            $table->json('parameters')->nullable();
            $table->string('file_path')->nullable();
            $table->string('format')->default('pdf'); // pdf, csv, xlsx
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('lms_generated_reports');
        Schema::dropIfExists('lms_report_definitions');
        Schema::dropIfExists('lms_student_insights');
        Schema::dropIfExists('lms_learning_path_analytics');
        Schema::dropIfExists('lms_quiz_analytics');
        Schema::dropIfExists('lms_content_analytics');
        Schema::dropIfExists('lms_course_analytics');
        Schema::dropIfExists('lms_engagement_summaries');
        Schema::dropIfExists('lms_activity_logs');
    }
};
