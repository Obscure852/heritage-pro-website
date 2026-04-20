<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('lms_calendar_events', function (Blueprint $table) {
            $table->enum('audience_scope', ['all', 'course', 'grade', 'class', 'mixed'])
                  ->default('all')
                  ->after('is_published');
        });
    }

    public function down(): void {
        Schema::table('lms_calendar_events', function (Blueprint $table) {
            $table->dropColumn('audience_scope');
        });
    }
};
