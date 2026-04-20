<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    
    public function up(){
        Schema::create('final_grade_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_grade_subject_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('graduation_term_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->year('graduation_year');
            $table->tinyInteger('type')->default(1);
            $table->boolean('mandatory')->default(true);
            $table->timestamps();

            $table->index(['graduation_term_id', 'graduation_year']);
            $table->index(['grade_id', 'subject_id']);
            $table->index('original_grade_subject_id');
        });
    }

    public function down(){
        Schema::dropIfExists('final_grade_subjects');
    }
};
