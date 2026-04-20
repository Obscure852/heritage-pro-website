<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(){
        Schema::create('student_house', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('house_id');
            $table->unsignedBigInteger('term_id');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('house_id')->references('id')->on('houses')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            
            $table->primary(['student_id', 'house_id', 'term_id']);

            $table->index('student_id');
            $table->index('house_id');
            $table->index('term_id');
        });
    }

    public function down(){
        Schema::dropIfExists('student_house');
    }
};
