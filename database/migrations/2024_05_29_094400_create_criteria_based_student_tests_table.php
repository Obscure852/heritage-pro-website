<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){

        Schema::create('criteria_based_student_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('component_id');
            $table->unsignedBigInteger('criteria_based_test_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('grade_option_id');
            $table->unsignedBigInteger('klass_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            $table->timestamps();

            $table->foreign('grade_subject_id')->references('id')->on('grade_subject')->onDelete('cascade');
            $table->foreign('component_id')->references('id')->on('components')->onDelete('cascade');
            $table->foreign('criteria_based_test_id')->references('id')->on('criteria_based_tests')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('grade_option_id')->references('id')->on('grade_options')->onDelete('cascade');
            $table->foreign('klass_id')->references('id')->on('klasses')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grade_options')->onDelete('cascade');



            $table->index('grade_subject_id');
            $table->index('component_id');
            $table->index('criteria_based_test_id');
            $table->index('student_id');
            $table->index('grade_option_id');
            $table->index('klass_id');
            $table->index('term_id');
        });
    }

    public function down(){
        Schema::dropIfExists('criteria_based_student_tests');
    }
};
