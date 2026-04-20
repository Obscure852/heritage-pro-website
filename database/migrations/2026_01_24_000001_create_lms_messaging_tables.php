<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conversations - 1-on-1 between student and instructor
        Schema::create('lms_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('student_read_at')->nullable();
            $table->timestamp('instructor_read_at')->nullable();
            $table->boolean('is_archived_by_student')->default(false);
            $table->boolean('is_archived_by_instructor')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'instructor_id', 'course_id'], 'unique_conversation');
            $table->index(['student_id', 'is_archived_by_student', 'last_message_at'], 'student_inbox');
            $table->index(['instructor_id', 'is_archived_by_instructor', 'last_message_at'], 'instructor_inbox');
        });

        // Direct Messages
        Schema::create('lms_direct_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('lms_conversations')->cascadeOnDelete();
            $table->morphs('sender'); // sender_type, sender_id (Student or User)
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['conversation_id', 'created_at']);
        });

        // Message Attachments
        Schema::create('lms_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('lms_direct_messages')->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_message_attachments');
        Schema::dropIfExists('lms_direct_messages');
        Schema::dropIfExists('lms_conversations');
    }
};
