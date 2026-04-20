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
        Schema::create('lms_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('lms_quizzes')->onDelete('cascade');
            $table->foreignId('question_bank_id')->nullable();
            $table->enum('type', [
                'multiple_choice',
                'multiple_answer',
                'true_false',
                'matching',
                'fill_blank',
                'short_answer',
                'essay',
                'ordering'
            ]);
            $table->text('question_text');
            $table->json('question_media')->nullable();
            $table->decimal('points', 5, 2)->default(1.00);
            $table->integer('sequence')->default(0);
            $table->text('feedback_correct')->nullable();
            $table->text('feedback_incorrect')->nullable();
            $table->json('options')->nullable();
            $table->json('correct_answer')->nullable();
            $table->boolean('case_sensitive')->default(false);
            $table->boolean('partial_credit')->default(false);
            $table->timestamps();

            $table->index(['quiz_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_quiz_questions');
    }
};
