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
        Schema::create('lms_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('enrollment_type', ['self', 'manual', 'auto', 'lti'])->default('manual');
            $table->enum('role', ['learner', 'instructor_assistant'])->default('learner');
            $table->enum('status', ['active', 'completed', 'dropped', 'suspended'])->default('active');
            $table->dateTime('enrolled_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->foreignId('current_module_id')->nullable()->constrained('lms_modules')->onDelete('set null');
            $table->foreignId('current_content_id')->nullable()->constrained('lms_content_items')->onDelete('set null');
            $table->decimal('grade', 10, 2)->nullable();
            $table->string('grade_letter', 5)->nullable();
            $table->dateTime('last_activity_at')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
            $table->index(['student_id', 'status']);
            $table->index(['course_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_enrollments');
    }
};
