<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds late_minutes column to staff_attendance_records for tracking exact lateness.
     * SELF-08 requirement: track late minutes for self-service clock in/out.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasColumn('staff_attendance_records', 'late_minutes')) {
            return;
        }
        Schema::table('staff_attendance_records', function (Blueprint $table) {
            $table->unsignedInteger('late_minutes')->nullable()->after('status');
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
            $table->dropColumn('late_minutes');
        });
    }
};
