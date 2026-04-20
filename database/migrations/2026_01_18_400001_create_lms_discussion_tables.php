<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Discussion Forums - One per course
        Schema::create('lms_discussion_forums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->string('title')->default('Course Discussions');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('allow_anonymous')->default(false);
            $table->boolean('require_approval')->default(false);
            $table->enum('post_permission', ['all', 'enrolled', 'instructors'])->default('enrolled');
            $table->timestamps();

            $table->unique('course_id');
        });

        // Discussion Categories/Boards within a forum
        Schema::create('lms_discussion_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained('lms_discussion_forums')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6366f1');
            $table->string('icon')->default('fas fa-comments');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        // Discussion Threads/Topics
        Schema::create('lms_discussion_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained('lms_discussion_forums')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('lms_discussion_categories')->nullOnDelete();
            $table->foreignId('author_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('content_item_id')->nullable()->constrained('lms_content_items')->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('slug')->index();
            $table->enum('type', ['discussion', 'question', 'announcement'])->default('discussion');
            $table->enum('status', ['open', 'closed', 'resolved', 'pending'])->default('open');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->foreignId('last_reply_id')->nullable();
            $table->foreignId('accepted_answer_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['forum_id', 'is_pinned', 'last_activity_at']);
            $table->index(['forum_id', 'category_id']);
        });

        // Discussion Posts/Replies
        Schema::create('lms_discussion_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('lms_discussion_threads')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('lms_discussion_posts')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('students')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_answer')->default(false); // Marked as answer for questions
            $table->integer('likes_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->enum('status', ['visible', 'hidden', 'pending'])->default('visible');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['thread_id', 'created_at']);
            $table->index(['thread_id', 'parent_id']);
        });

        // Post Likes/Upvotes
        Schema::create('lms_discussion_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->morphs('likeable'); // Can like threads or posts
            $table->timestamps();

            $table->unique(['student_id', 'likeable_type', 'likeable_id'], 'unique_student_like');
        });

        // Thread Subscriptions
        Schema::create('lms_discussion_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('thread_id')->constrained('lms_discussion_threads')->cascadeOnDelete();
            $table->enum('frequency', ['instant', 'daily', 'none'])->default('instant');
            $table->timestamps();

            $table->unique(['student_id', 'thread_id']);
        });

        // Post Attachments
        Schema::create('lms_discussion_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('lms_discussion_posts')->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->timestamps();
        });

        // Post Mentions
        Schema::create('lms_discussion_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('lms_discussion_posts')->cascadeOnDelete();
            $table->foreignId('mentioned_student_id')->constrained('students')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->unique(['post_id', 'mentioned_student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_discussion_mentions');
        Schema::dropIfExists('lms_discussion_attachments');
        Schema::dropIfExists('lms_discussion_subscriptions');
        Schema::dropIfExists('lms_discussion_likes');
        Schema::dropIfExists('lms_discussion_posts');
        Schema::dropIfExists('lms_discussion_threads');
        Schema::dropIfExists('lms_discussion_categories');
        Schema::dropIfExists('lms_discussion_forums');
    }
};
