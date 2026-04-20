<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_threshold_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('thresholds')->nullable()->comment('Custom thresholds array, null uses system default');
            $table->boolean('highlight_enabled')->default(true);
            $table->timestamps();

            // Each teacher can only have one preference record
            $table->unique('user_id', 'unique_teacher_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_threshold_preferences');
    }
};
