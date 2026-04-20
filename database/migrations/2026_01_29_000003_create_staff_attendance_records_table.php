<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the staff_attendance_records table for storing processed daily attendance.
     * Each record represents one user's attendance for one day.
     * ATT-06 requirement: unique constraint on (user_id, date).
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('staff_attendance_records')) {
            return;
        }
        Schema::create('staff_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');                          // Staff member
            $table->date('date');                                           // Attendance date
            $table->timestamp('clock_in')->nullable();                      // First clock in time
            $table->timestamp('clock_out')->nullable();                     // Last clock out time
            $table->unsignedBigInteger('clock_in_device_id')->nullable();   // Device used for clock in
            $table->unsignedBigInteger('clock_out_device_id')->nullable();  // Device used for clock out
            $table->decimal('hours_worked', 5, 2)->nullable();              // Calculated hours worked
            $table->string('status', 30)->default('present');               // present, absent, late, half_day, on_leave
            $table->string('leave_type', 50)->nullable();                   // Leave type if status is on_leave
            $table->text('notes')->nullable();                              // Additional notes
            $table->timestamps();
            $table->softDeletes();

            // CRITICAL: Unique constraint on user_id + date (ATT-06 requirement)
            $table->unique(['user_id', 'date'], 'staff_attendance_unique_user_date');

            // Indexes for efficient querying
            $table->index('user_id', 'staff_attendance_records_user_index');
            $table->index('date', 'staff_attendance_records_date_index');
            $table->index('status', 'staff_attendance_records_status_index');

            // Foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('clock_in_device_id')
                ->references('id')
                ->on('attendance_devices')
                ->onDelete('set null');

            $table->foreign('clock_out_device_id')
                ->references('id')
                ->on('attendance_devices')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendance_records');
    }
};
