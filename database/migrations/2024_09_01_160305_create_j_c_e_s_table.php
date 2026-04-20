<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('jce_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('overall')->nullable();
            $table->string('mathematics')->nullable();
            $table->string('english')->nullable();
            $table->string('science')->nullable();
            $table->string('setswana')->nullable();
            $table->string('design_and_technology')->nullable();
            $table->string('home_economics')->nullable();
            $table->string('agriculture')->nullable();
            $table->string('social_studies')->nullable();
            $table->string('moral_education')->nullable();
            
            $table->string('music')->nullable();
            $table->string('physical_education')->nullable();
            $table->string('religious_education')->nullable();
            $table->string('art')->nullable();
            $table->string('office_procedures')->nullable();
            $table->string('accounting')->nullable();
            $table->string('french')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');

            $table->index('student_id');
            $table->index('overall');
            $table->index('mathematics');
            $table->index('english');
            $table->index('science');
            $table->index('setswana');
            $table->index('design_and_technology');
            $table->index('home_economics');
            $table->index('agriculture');
            $table->index('moral_education');
            $table->index('religious_education');
            $table->index('music');
            $table->index('physical_education');
            $table->index('art');
            $table->index('office_procedures');
            $table->index('accounting');
            $table->index('french');
        });
    }

    public function down() {
        Schema::dropIfExists('jce_grades');
    }
};