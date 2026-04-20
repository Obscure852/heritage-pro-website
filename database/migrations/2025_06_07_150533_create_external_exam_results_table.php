<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){

        Schema::create('external_exam_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_exam_id');
            $table->unsignedBigInteger('final_student_id');
            $table->string('exam_number')->nullable();
            $table->string('excel_class_name')->nullable();
            $table->string('overall_grade')->nullable();
            $table->decimal('overall_points', 5, 1)->nullable();
            $table->integer('total_subjects')->default(0);
            $table->integer('passes')->default(0);
            $table->decimal('pass_percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('external_exam_id')->references('id')->on('external_exams')->onDelete('cascade');
            $table->foreign('final_student_id')->references('id')->on('final_students')->onDelete('cascade');
            $table->index(['external_exam_id', 'final_student_id']);
            $table->index('overall_grade');
            $table->index('overall_points');
            $table->index('exam_number');
            $table->unique(['external_exam_id', 'final_student_id']);
        });
    }

    public function down(){
        Schema::dropIfExists('external_exam_results');
    }
};
