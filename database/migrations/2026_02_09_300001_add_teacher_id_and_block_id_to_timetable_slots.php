<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('timetable_slots', function (Blueprint $table) {
            $table->foreignId('teacher_id')
                ->nullable()
                ->after('klass_subject_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->char('block_id', 36)
                ->nullable()
                ->after('is_locked');

            $table->index('teacher_id');
            $table->index('block_id');
        });
    }

    public function down(): void {
        Schema::table('timetable_slots', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['teacher_id', 'block_id']);
        });
    }
};
