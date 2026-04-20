<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('lms_calendar_events', function (Blueprint $table) {
            $table->boolean('notify_students')->default(false)->after('audience_scope');
        });
    }

    public function down(): void {
        Schema::table('lms_calendar_events', function (Blueprint $table) {
            $table->dropColumn('notify_students');
        });
    }
};
