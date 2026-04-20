<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('klass_student', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('klass_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            
            $table->year('year')->default(date('Y'));
            $table->boolean('active')->default(true);

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('klass_id')->references('id')->on('klasses')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();


            $table->index('student_id');
            $table->index('klass_id');
            $table->index('term_id');
            $table->index('grade_id');
            
        });
    }

    public function down() {
        Schema::dropIfExists('klass_student');
    }
};
