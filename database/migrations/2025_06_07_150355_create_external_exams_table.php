<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){

        Schema::create('external_exams', function (Blueprint $table) {
            $table->id();
            $table->string('exam_type');
            $table->string('exam_session');
            $table->string('centre_code')->nullable();
            $table->string('centre_name')->nullable();
            $table->year('exam_year');
            $table->year('graduation_year');
            $table->unsignedBigInteger('graduation_term_id');
            $table->date('import_date');
            $table->unsignedBigInteger('imported_by');
            $table->text('import_notes')->nullable();
            $table->json('excel_columns')->nullable();
            $table->string('original_filename')->nullable();
            $table->timestamps();

            $table->index(['exam_type', 'exam_year']);
            $table->index(['graduation_year', 'graduation_term_id']);
            $table->index('imported_by');
        });
    }

    public function down(){
        Schema::dropIfExists('external_exams');
    }
};
