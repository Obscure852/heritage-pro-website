<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('gender')->index(); 
            $table->date('date_of_birth');
            $table->string('nationality')->index(); 
            $table->string('phone')->nullable(); 
            $table->string('id_number')->index(); 
            $table->string('grade_applying_for')->index(); 
            $table->date('application_date');
            $table->string('status')->default('Pending')->index();
            $table->unsignedBigInteger('term_id');
            $table->year('year');
            $table->integer('last_updated_by')->nullable();

            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('admissions');
    }
};
