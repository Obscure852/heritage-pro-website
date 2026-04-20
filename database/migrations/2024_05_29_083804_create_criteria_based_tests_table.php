<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('criteria_based_tests', function (Blueprint $table) {
            $table->id();
            $table->integer('sequence');
            $table->string('name');
            $table->string('abbrev');
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            $table->string('type');
            $table->boolean('assessment');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('grade_subject_id')->references('id')->on('grade_subject')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');


            $table->index('name');
            $table->index('abbrev');
            $table->index('grade_subject_id');
            $table->index('term_id');
            $table->index('grade_id');
        });
    }

    public function down(){
        Schema::dropIfExists('criteria_based_tests');
    }
};
