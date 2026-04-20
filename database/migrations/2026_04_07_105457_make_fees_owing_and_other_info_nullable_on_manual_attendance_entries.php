<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_attendance_entries', function (Blueprint $table) {
            $table->decimal('school_fees_owing', 10, 2)->nullable()->default(null)->change();
            $table->text('other_info')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('manual_attendance_entries', function (Blueprint $table) {
            $table->decimal('school_fees_owing', 10, 2)->default(0.00)->change();
            $table->text('other_info')->nullable()->change();
        });
    }
};
