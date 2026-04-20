<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->foreignId('klass_subject_id')->constrained('klass_subject');
            $table->unsignedTinyInteger('day_of_cycle');
            $table->unsignedTinyInteger('period_number');
            $table->unsignedTinyInteger('duration')->default(1);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            $table->unique(['timetable_id', 'klass_subject_id', 'day_of_cycle', 'period_number'], 'tt_slots_unique');
            $table->index(['timetable_id', 'day_of_cycle', 'period_number'], 'tt_slots_day_period_index');
            $table->index('klass_subject_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('timetable_slots');
    }
};
