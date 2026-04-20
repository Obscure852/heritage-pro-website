<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timetable_constraints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->string('constraint_type', 100);
            $table->json('constraint_config');
            $table->boolean('is_hard')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['timetable_id', 'constraint_type'], 'tt_constraints_type_index');
        });
    }

    public function down(): void {
        Schema::dropIfExists('timetable_constraints');
    }
};
