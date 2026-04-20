<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('klass_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('term_id');
            $table->string('class_teacher_remarks')->nullable();
            $table->string('school_head_remarks')->nullable();
            $table->year('year')->default(date('Y'));

            $table->unique(['student_id', 'klass_id', 'user_id', 'term_id', 'year']);

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('klass_id')->references('id')->on('klasses')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('comments');
    }
};
