<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('final_student_houses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('final_student_id');
            $table->unsignedBigInteger('final_house_id');
            $table->unsignedBigInteger('graduation_term_id');
            $table->year('graduation_year');
            $table->timestamps();

            $table->foreign('final_student_id', 'fk_fsh_student')->references('id')->on('final_students')->onDelete('cascade');
            $table->foreign('final_house_id', 'fk_fsh_house')->references('id')->on('final_houses')->onDelete('cascade');
            
            $table->index(['graduation_term_id', 'graduation_year'], 'idx_fsh_graduation');
            $table->unique(['final_student_id', 'graduation_term_id'], 'unique_student_house_term');
        });
    }

    public function down(){
        Schema::dropIfExists('final_student_houses');
    }
};