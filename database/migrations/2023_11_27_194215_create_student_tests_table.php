<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('student_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('test_id')->nullable();
            $table->integer('score')->nullable();
            $table->string('percentage')->nullable();
            $table->string('grade')->nullable();
            $table->integer('points')->default(0);
            $table->integer('avg_score')->nullable();
            $table->string('avg_grade')->nullable();
            $table->string('comment')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');

            $table->index('student_id');
            $table->index('test_id');
            $table->index('score');
            $table->index('comment');
            $table->index('percentage');
            $table->index('grade');
            $table->index('avg_score');
        });
    }

    public function down(){
        Schema::dropIfExists('student_tests');
    }
};