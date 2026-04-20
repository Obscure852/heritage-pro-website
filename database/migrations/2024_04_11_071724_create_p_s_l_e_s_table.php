<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('psle_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('overall_grade');
            $table->string('agriculture_grade', 191)->nullable();
            $table->string('mathematics_grade', 191)->nullable();
            $table->string('english_grade', 191)->nullable();
            $table->string('science_grade', 191)->nullable();
            $table->string('social_studies_grade', 191)->nullable();
            $table->string('setswana_grade', 191)->nullable();
            $table->string('capa_grade', 191)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');

            $table->index('overall_grade');
            $table->index('agriculture_grade');
            $table->index('mathematics_grade');
            $table->index('english_grade');
            $table->index('science_grade');
            $table->index('social_studies_grade');
            $table->index('setswana_grade');
            $table->index('capa_grade');
            $table->index('student_id');           
        });
    }

    public function down(){
        Schema::dropIfExists('psle_grades');
    }
};
