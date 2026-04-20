<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activity_schedules')) {
            Schema::create('activity_schedules', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('frequency', 20);
                $table->unsignedTinyInteger('day_of_week');
                $table->time('start_time');
                $table->time('end_time');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->string('location')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('activity_id')->references('id')->on('activities')->cascadeOnDelete();
                $table->index(['activity_id', 'active']);
            });
        }

        if (!Schema::hasTable('activity_sessions')) {
            Schema::create('activity_sessions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('activity_schedule_id')->nullable();
                $table->string('session_type', 50);
                $table->date('session_date');
                $table->dateTime('start_datetime');
                $table->dateTime('end_datetime')->nullable();
                $table->string('location')->nullable();
                $table->string('status', 20)->default('planned');
                $table->boolean('attendance_locked')->default(false);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('activity_id')->references('id')->on('activities')->cascadeOnDelete();
                $table->foreign('activity_schedule_id')->references('id')->on('activity_schedules')->nullOnDelete();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

                $table->index(['activity_id', 'session_date']);
                $table->index(['status', 'attendance_locked']);
            });
        }

        if (!Schema::hasTable('activity_session_attendance')) {
            Schema::create('activity_session_attendance', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_session_id');
                $table->unsignedBigInteger('activity_enrollment_id');
                $table->unsignedBigInteger('student_id');
                $table->string('status', 20);
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('marked_by')->nullable();
                $table->timestamp('marked_at')->nullable();
                $table->timestamps();

                $table->foreign('activity_session_id')->references('id')->on('activity_sessions')->cascadeOnDelete();
                $table->foreign('activity_enrollment_id')->references('id')->on('activity_enrollments')->cascadeOnDelete();
                $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
                $table->foreign('marked_by')->references('id')->on('users')->nullOnDelete();

                $table->unique(['activity_session_id', 'student_id'], 'activity_session_student_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_session_attendance');
        Schema::dropIfExists('activity_sessions');
        Schema::dropIfExists('activity_schedules');
    }
};
