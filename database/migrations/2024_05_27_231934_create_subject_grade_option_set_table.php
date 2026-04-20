<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('subject_grade_option_set', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('grade_option_set_id');
            $table->timestamps();


            $table->foreign('grade_subject_id')->references('id')->on('grade_subject')->onDelete('cascade');
            $table->foreign('grade_option_set_id')->references('id')->on('grade_option_sets')->onDelete('cascade');

            $table->index('grade_subject_id');
            $table->index('grade_option_set_id');
        });
    }

    public function down(){
        Schema::dropIfExists('subject_grade_options');
    }
};
