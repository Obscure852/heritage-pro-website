<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('final_klass_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_klass_subject_id');
            $table->unsignedBigInteger('final_klass_id');
            $table->unsignedBigInteger('final_grade_subject_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('graduation_term_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->year('graduation_year');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('final_klass_id')->references('id')->on('final_klasses')->onDelete('cascade');
            $table->foreign('final_grade_subject_id')->references('id')->on('final_grade_subjects')->onDelete('cascade');
            $table->index(['graduation_term_id', 'graduation_year']);
            $table->index(['final_klass_id', 'final_grade_subject_id']);
        });
    }

    public function down(){
        Schema::dropIfExists('final_klass_subjects');
    }
};
