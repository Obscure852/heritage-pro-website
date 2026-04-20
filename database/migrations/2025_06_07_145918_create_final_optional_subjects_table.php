<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('final_optional_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_optional_subject_id');
            $table->string('name');
            $table->unsignedBigInteger('final_grade_subject_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('graduation_term_id');
            $table->unsignedBigInteger('grade_id');
            $table->string('grouping')->nullable();
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->boolean('active')->default(true);
            $table->year('graduation_year');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('final_grade_subject_id')->references('id')->on('final_grade_subjects')->onDelete('cascade');
            $table->index(['graduation_term_id', 'graduation_year']);
            $table->index('original_optional_subject_id');
        });
    }

    public function down(){
        Schema::dropIfExists('final_optional_subjects');
    }
};
