<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Calendar events
        Schema::create('lms_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // class, assignment, quiz, meeting, office_hours, holiday, custom
            $table->string('color')->default('#6366f1');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable(); // For virtual meetings
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->cascadeOnDelete();
            $table->nullableMorphs('eventable'); // Link to quiz, assignment, etc.
            $table->string('recurrence_rule')->nullable(); // RRULE format
            $table->foreignId('parent_event_id')->nullable(); // For recurring event instances
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_id', 'start_date']);
            $table->index(['start_date', 'end_date']);
            $table->index('type');
        });

        // Event attendees/visibility
        Schema::create('lms_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('lms_calendar_events')->cascadeOnDelete();
            $table->morphs('attendee'); // User or group
            $table->string('status')->default('pending'); // pending, accepted, declined, tentative
            $table->boolean('is_required')->default(false);
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'attendee_type', 'attendee_id'], 'lms_event_attendee_unique');
        });

        // Event reminders
        Schema::create('lms_event_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('lms_calendar_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('minutes_before')->default(30);
            $table->string('method')->default('notification'); // notification, email, both
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'is_sent']);
        });

        // User calendar preferences
        Schema::create('lms_calendar_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('default_view')->default('month'); // day, week, month, agenda
            $table->string('week_start')->default('sunday'); // sunday, monday
            $table->json('working_hours')->nullable(); // {start: "09:00", end: "17:00"}
            $table->json('hidden_event_types')->nullable();
            $table->json('color_overrides')->nullable();
            $table->string('timezone')->default('UTC');
            $table->boolean('show_weekends')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });

        // Scheduling: Available time slots (for office hours, tutoring, etc.)
        Schema::create('lms_availability_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('lms_courses')->cascadeOnDelete();
            $table->string('title'); // "Office Hours", "Tutoring Session"
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable();
            $table->integer('slot_duration')->default(30); // minutes
            $table->integer('buffer_time')->default(0); // minutes between slots
            $table->integer('max_bookings_per_slot')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Weekly recurring availability
        Schema::create('lms_availability_windows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('lms_availability_schedules')->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0=Sunday, 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->index(['schedule_id', 'day_of_week']);
        });

        // One-time availability overrides
        Schema::create('lms_availability_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('lms_availability_schedules')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_available')->default(true); // false = blocked
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'date']);
        });

        // Booked appointments
        Schema::create('lms_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('lms_availability_schedules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status')->default('confirmed'); // confirmed, cancelled, completed, no_show
            $table->text('student_notes')->nullable();
            $table->text('instructor_notes')->nullable();
            $table->string('meeting_url')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['schedule_id', 'start_time']);
            $table->index(['student_id', 'status']);
        });

        // Course schedules (class sessions)
        Schema::create('lms_course_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->string('title')->nullable(); // "Lecture", "Lab", "Discussion"
            $table->tinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'day_of_week']);
        });

        // Deadlines aggregator (pulls from assignments, quizzes, etc.)
        Schema::create('lms_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // assignment, quiz, discussion, custom
            $table->nullableMorphs('deadlineable'); // The source item
            $table->dateTime('due_date');
            $table->integer('grace_period_minutes')->default(0);
            $table->boolean('allows_late')->default(false);
            $table->decimal('late_penalty_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['course_id', 'due_date']);
            $table->index('due_date');
        });

        // Student deadline tracking
        Schema::create('lms_student_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deadline_id')->constrained('lms_deadlines')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->dateTime('extended_due_date')->nullable(); // Individual extension
            $table->string('extension_reason')->nullable();
            $table->foreignId('extended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();

            $table->unique(['deadline_id', 'student_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('lms_student_deadlines');
        Schema::dropIfExists('lms_deadlines');
        Schema::dropIfExists('lms_course_schedules');
        Schema::dropIfExists('lms_appointments');
        Schema::dropIfExists('lms_availability_overrides');
        Schema::dropIfExists('lms_availability_windows');
        Schema::dropIfExists('lms_availability_schedules');
        Schema::dropIfExists('lms_calendar_preferences');
        Schema::dropIfExists('lms_event_reminders');
        Schema::dropIfExists('lms_event_attendees');
        Schema::dropIfExists('lms_calendar_events');
    }
};
