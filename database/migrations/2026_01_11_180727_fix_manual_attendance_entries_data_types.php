<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, convert existing string values to proper types
        // Handle null or empty string values
        DB::statement("UPDATE manual_attendance_entries SET days_absent = '0' WHERE days_absent IS NULL OR days_absent = ''");
        DB::statement("UPDATE manual_attendance_entries SET school_fees_owing = '0' WHERE school_fees_owing IS NULL OR school_fees_owing = ''");

        Schema::table('manual_attendance_entries', function (Blueprint $table) {
            $table->unsignedInteger('days_absent')->default(0)->change();
            $table->decimal('school_fees_owing', 10, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manual_attendance_entries', function (Blueprint $table) {
            $table->string('days_absent')->default('0')->change();
            $table->string('school_fees_owing')->default('0.00')->change();
        });
    }
};
