<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('subject_comments', function (Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('student_test_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('grade_subject_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('term_id');
            $table->string('remarks');
            $table->year('year');
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('student_test_id')->references('id')->on('student_tests')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('grade_subject_id')->references('id')->on('grade_subject')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');

            $table->index('student_test_id');
            $table->index('student_id');
            $table->index('grade_subject_id');
            $table->index('user_id');
            $table->index('term_id');
            $table->index('year');
        });
    }

    public function down(){
        Schema::dropIfExists('subject_comments');
    }
};