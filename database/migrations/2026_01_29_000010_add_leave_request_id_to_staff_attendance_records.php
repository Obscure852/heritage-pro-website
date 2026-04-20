<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds leave_request_id FK column to staff_attendance_records for leave correlation.
     * LEAVE-09 requirement: link attendance records to the leave request that generated them.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasColumn('staff_attendance_records', 'leave_request_id')) {
            return;
        }
        Schema::table('staff_attendance_records', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_request_id')->nullable()->after('user_id');
            $table->foreign('leave_request_id')
                ->references('id')
                ->on('leave_requests')
                ->onDelete('set null');
            $table->index('leave_request_id');
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
            $table->dropForeign(['leave_request_id']);
            $table->dropIndex(['leave_request_id']);
            $table->dropColumn('leave_request_id');
        });
    }
};
