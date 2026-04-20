<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        Schema::create('final_student_optional_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('final_student_id');
            $table->unsignedBigInteger('final_optional_subject_id');
            $table->unsignedBigInteger('graduation_term_id');
            $table->unsignedBigInteger('final_klass_id');
            $table->year('graduation_year');
            $table->timestamps();

            $table->foreign('final_student_id', 'fk_fsos_student')
                  ->references('id')->on('final_students')->onDelete('cascade');
            
            $table->foreign('final_optional_subject_id', 'fk_fsos_optional_subject')
                  ->references('id')->on('final_optional_subjects')->onDelete('cascade');
            
            $table->foreign('final_klass_id', 'fk_fsos_klass')
                  ->references('id')->on('final_klasses')->onDelete('cascade');
            
            $table->index(['graduation_term_id', 'graduation_year'], 'idx_fsos_graduation');
            $table->unique(['final_student_id', 'final_optional_subject_id', 'graduation_term_id'], 'unique_student_optional_term');
        });
    }

    public function down(){
        Schema::dropIfExists('final_student_optional_subjects');
    }
};