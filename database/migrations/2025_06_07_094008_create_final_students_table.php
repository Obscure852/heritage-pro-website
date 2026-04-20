<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    
    public function up(){
        Schema::create('final_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_student_id');
            $table->string('connect_id')->nullable();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('exam_number')->nullable();
            $table->enum('gender', ['M', 'F']);
            $table->date('date_of_birth')->nullable();
            $table->string('email')->nullable();
            $table->string('nationality')->nullable();
            $table->string('id_number')->nullable();
            $table->string('status')->default('Alumni');
            $table->decimal('credit', 10, 2)->default(0);
            $table->boolean('parent_is_staff')->default(false);
            $table->unsignedBigInteger('student_filter_id')->nullable();
            $table->unsignedBigInteger('student_type_id')->nullable();
            $table->unsignedBigInteger('graduation_term_id');
            $table->year('graduation_year');
            $table->unsignedBigInteger('graduation_grade_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['graduation_term_id', 'graduation_year']);
            $table->index('original_student_id');
            $table->index(['first_name', 'last_name']);
        });
    }

    public function down(){
        Schema::dropIfExists('final_students');
    }
};
