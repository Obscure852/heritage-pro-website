stu<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('student_optional_subjects', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_id')->unsigned();
            $table->bigInteger('optional_subject_id')->unsigned();
            $table->bigInteger('term_id')->unsigned();
            $table->bigInteger('klass_id')->unsigned();
            $table->timestamps();

            $table->unique(['student_id', 'optional_subject_id', 'term_id'], 'unique_student_subject_term');

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('optional_subject_id')->references('id')->on('optional_subjects')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('klass_id')->references('id')->on('klasses')->onDelete('cascade');

            $table->index('student_id');
            $table->index('optional_subject_id');
            $table->index('term_id');
            $table->index('klass_id');
        });
    }

    public function down(){
        Schema::dropIfExists('student_optional_subjects');
    }
};
