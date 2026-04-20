<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invigilation_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('grade_subject_id');
            $table->string('paper_label')->nullable();
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('day_of_cycle')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('series_id')->references('id')->on('invigilation_series')->cascadeOnDelete();
            $table->foreign('grade_subject_id')->references('id')->on('grade_subject')->cascadeOnDelete();

            $table->index(['series_id', 'exam_date']);
            $table->index(['series_id', 'grade_subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invigilation_sessions');
    }
};
