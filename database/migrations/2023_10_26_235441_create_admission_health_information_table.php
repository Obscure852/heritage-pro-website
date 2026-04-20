<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('admission_health_information', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id');
            $table->text('health_history')->nullable();
            $table->text('immunization_records')->nullable();
            $table->boolean('peanuts')->default(false);
            $table->boolean('red_meat')->default(false);
            $table->boolean('vegetarian')->default(false);
            $table->text('other_allergies')->nullable();
            $table->boolean('left_leg')->default(false);
            $table->boolean('right_leg')->default(false);
            $table->boolean('left_hand')->default(false);
            $table->boolean('right_hand')->default(false);
            $table->text('other_disabilities')->nullable();
            $table->boolean('left_eye')->default(false);
            $table->boolean('right_eye')->default(false);
            $table->boolean('left_ear')->default(false);
            $table->boolean('right_ear')->default(false);
            $table->text('medical_conditions')->nullable();
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');

            $table->index('admission_id');
        });
    }


    public function down(){
        Schema::dropIfExists('admission_health_information');
    }
};