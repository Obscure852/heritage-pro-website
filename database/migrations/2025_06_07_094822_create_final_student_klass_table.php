<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('final_student_klass', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('final_student_id');
            $table->unsignedBigInteger('final_klass_id');
            $table->unsignedBigInteger('graduation_term_id');
            $table->year('graduation_year');
            $table->unsignedBigInteger('grade_id');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('final_student_id')->references('id')->on('final_students')->onDelete('cascade');
            $table->foreign('final_klass_id')->references('id')->on('final_klasses')->onDelete('cascade');
            $table->index(['graduation_term_id', 'graduation_year']);
            $table->unique(['final_student_id', 'final_klass_id', 'graduation_term_id'], 'unique_student_klass_term');
        });
    }

    public function down(){
        Schema::dropIfExists('final_student_klass');
    }
};
