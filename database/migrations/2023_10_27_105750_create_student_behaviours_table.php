<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){

        Schema::create('student_behaviours', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('student_id')->index()->constrained('students')->onDelete('cascade');
            $table->foreignId('term_id')->index()->constrained()->onDelete('cascade');

            // Columns
            $table->date('date');
            $table->string('behaviour_type');
            $table->text('description')->nullable();
            $table->string('action_taken')->nullable();
            $table->text('remarks')->nullable();
            $table->string('reported_by');

            // Year and soft deletes
            $table->year('year');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('student_behaviours');
    }
};
