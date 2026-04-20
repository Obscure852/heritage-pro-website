<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        Schema::create('external_exam_subject_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_exam_result_id');
            $table->unsignedBigInteger('final_grade_subject_id')->nullable();
            $table->string('subject_code');
            $table->string('subject_name')->nullable();
            $table->string('grade');
            $table->decimal('grade_points', 3, 1)->nullable();
            $table->boolean('is_pass')->default(false);
            $table->boolean('is_mapped')->default(false);
            $table->boolean('was_taken')->default(false);
            $table->text('mapping_notes')->nullable();
            $table->timestamps();

            $table->foreign('external_exam_result_id', 'fk_eesr_exam_result')
                  ->references('id')->on('external_exam_results')->onDelete('cascade');
            
            $table->foreign('final_grade_subject_id', 'fk_eesr_grade_subject')
                  ->references('id')->on('final_grade_subjects')->onDelete('set null');
            
            $table->index(['external_exam_result_id', 'subject_code'], 'idx_eesr_exam_subject');
            $table->index(['final_grade_subject_id', 'grade'], 'idx_eesr_grade_subject_grade');
            $table->index('is_mapped', 'idx_eesr_is_mapped');
            $table->index('is_pass', 'idx_eesr_is_pass');
            $table->index('grade', 'idx_eesr_grade');
        });
    }

    public function down(){
        Schema::dropIfExists('external_exam_subject_results');
    }
};