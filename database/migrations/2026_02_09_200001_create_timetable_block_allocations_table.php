<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timetable_block_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->foreignId('klass_subject_id')->constrained('klass_subject');
            $table->unsignedTinyInteger('singles')->default(0);
            $table->unsignedTinyInteger('doubles')->default(0);
            $table->unsignedTinyInteger('triples')->default(0);
            $table->unsignedTinyInteger('total_periods')->storedAs('singles + (doubles * 2) + (triples * 3)');
            $table->timestamps();
            $table->unique(['timetable_id', 'klass_subject_id'], 'tt_block_alloc_unique');
            $table->index('timetable_id');
            $table->index('klass_subject_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('timetable_block_allocations');
    }
};
