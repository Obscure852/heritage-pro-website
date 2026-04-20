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
        Schema::create('lms_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_item_id')->constrained('lms_content_items')->onDelete('cascade');
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->integer('time_limit_minutes')->nullable();
            $table->integer('max_attempts')->nullable();
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_answers')->default(false);
            $table->boolean('show_correct_answers')->default(false);
            $table->dateTime('show_correct_answers_after')->nullable();
            $table->decimal('passing_score', 5, 2)->default(60.00);
            $table->boolean('allow_review')->default(true);
            $table->boolean('one_question_per_page')->default(false);
            $table->boolean('lock_after_attempt')->default(false);
            $table->boolean('require_access_code')->default(false);
            $table->string('access_code', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_quizzes');
    }
};
