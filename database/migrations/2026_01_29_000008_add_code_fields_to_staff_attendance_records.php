<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds attendance_code_id, entry_type, and recorded_by columns to staff_attendance_records.
     * MAN-06 requirement: track entry source (biometric, manual, self_service, leave_sync).
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasColumn('staff_attendance_records', 'attendance_code_id')) {
            return;
        }
        Schema::table('staff_attendance_records', function (Blueprint $table) {
            // Add attendance code foreign key (nullable for existing records)
            $table->unsignedBigInteger('attendance_code_id')->nullable()->after('status');

            // Entry type for tracking how attendance was recorded
            // Values: biometric, manual, self_service, leave_sync
            $table->string('entry_type', 30)->default('biometric')->after('attendance_code_id');

            // Who recorded the attendance (for manual/self-service entries)
            $table->unsignedBigInteger('recorded_by')->nullable()->after('entry_type');

            // Foreign keys
            $table->foreign('attendance_code_id')
                ->references('id')
                ->on('staff_attendance_codes')
                ->onDelete('restrict');

            $table->foreign('recorded_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Index for entry_type filtering
            $table->index('entry_type', 'staff_attendance_records_entry_type_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('staff_attendance_records', function (Blueprint $table) {
            $table->dropForeign(['attendance_code_id']);
            $table->dropForeign(['recorded_by']);
            $table->dropIndex('staff_attendance_records_entry_type_index');
            $table->dropColumn(['attendance_code_id', 'entry_type', 'recorded_by']);
        });
    }
};
