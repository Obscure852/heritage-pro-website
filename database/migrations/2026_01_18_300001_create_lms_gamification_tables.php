<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Badges - Define available badges
        Schema::create('lms_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // FontAwesome icon class or image path
            $table->string('color', 7)->default('#6366f1'); // Hex color
            $table->enum('category', ['completion', 'achievement', 'streak', 'social', 'special'])->default('achievement');
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            $table->integer('points_value')->default(0); // Points awarded when earned
            $table->json('criteria')->nullable(); // JSON criteria for auto-awarding
            $table->boolean('is_active')->default(true);
            $table->boolean('is_secret')->default(false); // Hidden until earned
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Student Badges - Earned badges
        Schema::create('lms_student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('lms_badges')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->nullOnDelete();
            $table->timestamp('earned_at');
            $table->json('metadata')->nullable(); // Context of how it was earned
            $table->boolean('is_featured')->default(false); // Display on profile
            $table->timestamps();

            $table->unique(['student_id', 'badge_id', 'course_id'], 'unique_student_badge_course');
        });

        // Points Transactions - Track all point changes
        Schema::create('lms_points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->nullOnDelete();
            $table->integer('points'); // Can be negative for deductions
            $table->integer('balance_after'); // Running balance
            $table->enum('type', [
                'course_complete',
                'module_complete', 
                'content_complete',
                'quiz_pass',
                'quiz_perfect',
                'assignment_submit',
                'assignment_excellent',
                'badge_earned',
                'streak_bonus',
                'first_login',
                'daily_login',
                'discussion_post',
                'helpful_answer',
                'bonus',
                'penalty',
                'admin_adjustment'
            ]);
            $table->string('description')->nullable();
            $table->morphs('pointable'); // Related entity (quiz, assignment, etc.)
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
            $table->index(['student_id', 'course_id']);
        });

        // Student Points Summary - Cached totals
        Schema::create('lms_student_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->nullOnDelete();
            $table->integer('total_points')->default(0);
            $table->integer('level')->default(1);
            $table->integer('xp_to_next_level')->default(100);
            $table->integer('current_streak')->default(0); // Days in a row
            $table->integer('longest_streak')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'course_id'], 'unique_student_course_points');
        });

        // Achievements - Milestone-based rewards
        Schema::create('lms_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#10b981');
            $table->enum('type', ['milestone', 'cumulative', 'streak', 'speed', 'quality'])->default('milestone');
            $table->json('criteria'); // JSON criteria definition
            $table->integer('points_reward')->default(0);
            $table->foreignId('badge_id')->nullable()->constrained('lms_badges')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Student Achievements
        Schema::create('lms_student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained('lms_achievements')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->nullOnDelete();
            $table->integer('progress')->default(0); // Progress towards achievement (0-100)
            $table->integer('current_value')->default(0); // Current count/value
            $table->integer('target_value')->default(1); // Target to unlock
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'achievement_id', 'course_id'], 'unique_student_achievement');
        });

        // Leaderboard Cache - Periodically refreshed
        Schema::create('lms_leaderboard_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->nullOnDelete();
            $table->enum('period', ['daily', 'weekly', 'monthly', 'all_time'])->default('all_time');
            $table->integer('rank');
            $table->integer('points');
            $table->integer('badges_count')->default(0);
            $table->integer('courses_completed')->default(0);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'course_id', 'period', 'period_start'], 'unique_leaderboard_entry');
            $table->index(['course_id', 'period', 'rank']);
        });

        // Point Rules - Configurable point values
        Schema::create('lms_point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('action')->unique(); // e.g., 'course_complete', 'quiz_pass'
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points')->default(0);
            $table->integer('bonus_points')->default(0); // For exceptional performance
            $table->json('conditions')->nullable(); // Optional conditions for bonus
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_point_rules');
        Schema::dropIfExists('lms_leaderboard_cache');
        Schema::dropIfExists('lms_student_achievements');
        Schema::dropIfExists('lms_achievements');
        Schema::dropIfExists('lms_student_points');
        Schema::dropIfExists('lms_points_transactions');
        Schema::dropIfExists('lms_student_badges');
        Schema::dropIfExists('lms_badges');
    }
};
