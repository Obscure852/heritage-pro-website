<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('timetable_slots', function (Blueprint $table) {
            $table->unsignedBigInteger('venue_id')->nullable()->after('teacher_id');
            $table->unsignedBigInteger('assistant_teacher_id')->nullable()->after('venue_id');

            $table->foreign('venue_id')->references('id')->on('venues')->nullOnDelete();
            $table->foreign('assistant_teacher_id')->references('id')->on('users')->nullOnDelete();

            $table->index('venue_id');
            $table->index('assistant_teacher_id');
        });
    }

    public function down(): void {
        Schema::table('timetable_slots', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->dropForeign(['assistant_teacher_id']);
            $table->dropColumn(['venue_id', 'assistant_teacher_id']);
        });
    }
};
