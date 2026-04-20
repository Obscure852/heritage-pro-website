<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timetable_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->unsignedBigInteger('slot_id')->nullable();
            $table->foreign('slot_id')->references('id')->on('timetable_slots')->onDelete('set null');
            $table->enum('type', ['hard', 'soft']);
            $table->string('constraint_type', 100)->nullable();
            $table->text('description');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at');
            $table->index(['timetable_id', 'resolved_at'], 'tt_conflicts_resolved_index');
        });
    }

    public function down(): void {
        Schema::dropIfExists('timetable_conflicts');
    }
};
