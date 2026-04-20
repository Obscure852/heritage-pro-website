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
        Schema::create('lms_content_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('lms_enrollments')->onDelete('cascade');
            $table->foreignId('content_item_id')->constrained('lms_content_items')->onDelete('cascade');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->decimal('score', 10, 2)->nullable();
            $table->decimal('score_percentage', 5, 2)->nullable();
            $table->integer('attempts')->default(0);
            $table->decimal('best_score', 10, 2)->nullable();
            $table->json('last_position')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'content_item_id']);
            $table->index(['content_item_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_content_progress');
    }
};
