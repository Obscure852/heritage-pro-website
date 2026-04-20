<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Notifications
        Schema::create('lms_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('type'); // e.g., 'course_enrolled', 'quiz_graded', 'new_reply'
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable();
            $table->string('color')->default('#3b82f6');
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->morphs('notifiable'); // Related entity
            $table->json('data')->nullable(); // Additional data
            $table->timestamp('read_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'read_at', 'created_at']);
            $table->index(['student_id', 'type']);
        });

        // Notification Preferences
        Schema::create('lms_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('type'); // Notification type
            $table->boolean('in_app')->default(true);
            $table->boolean('email')->default(true);
            $table->boolean('push')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'type']);
        });

        // Email Queue for batch sending
        Schema::create('lms_notification_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('lms_notifications')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('email');
            $table->string('subject');
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Announcements (course-wide or system-wide)
        Schema::create('lms_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('send_email')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'published_at']);
        });

        // Announcement Read Status
        Schema::create('lms_announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('lms_announcements')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('read_at');

            $table->unique(['announcement_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_announcement_reads');
        Schema::dropIfExists('lms_announcements');
        Schema::dropIfExists('lms_notification_emails');
        Schema::dropIfExists('lms_notification_preferences');
        Schema::dropIfExists('lms_notifications');
    }
};
