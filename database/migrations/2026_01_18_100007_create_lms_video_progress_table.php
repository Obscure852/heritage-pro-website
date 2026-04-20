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
        Schema::create('lms_video_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('lms_videos')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('current_time')->default(0);
            $table->integer('furthest_time')->default(0);
            $table->integer('total_watch_time')->default(0);
            $table->decimal('watch_percentage', 5, 2)->default(0);
            $table->boolean('completed')->default(false);
            $table->decimal('playback_rate', 3, 2)->default(1.00);
            $table->json('events')->nullable();
            $table->dateTime('last_watched_at')->nullable();
            $table->timestamps();

            $table->unique(['video_id', 'student_id']);
            $table->index(['student_id', 'completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_video_progress');
    }
};
