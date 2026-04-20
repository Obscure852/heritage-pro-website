<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('student_medical_information', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('student_id')->index()->constrained('students')->onDelete('cascade');
            $table->foreignId('term_id')->index()->constrained()->onDelete('cascade');

            // Health history and immunization records
            $table->text('health_history')->nullable();
            $table->text('immunization_records')->nullable();

            // Blood groups
            $table->boolean('a_positive')->default(false);
            $table->boolean('a_negative')->default(false);
            $table->boolean('b_positive')->default(false);
            $table->boolean('b_negative')->default(false);
            $table->boolean('ab_positive')->default(false);
            $table->boolean('ab_negative')->default(false);
            $table->boolean('o_positive')->default(false);
            $table->boolean('o_negative')->default(false);

            // Other medical details
            $table->text('other_allergies')->nullable();
            $table->text('other_disabilities')->nullable();
            $table->text('medical_conditions')->nullable();  // Corrected spelling

            // Allergies
            $table->boolean('peanuts')->default(false);
            $table->boolean('red_meat')->default(false);
            $table->boolean('vegetarian')->default(false);

            // Limb disabilities
            $table->boolean('left_leg')->default(false);
            $table->boolean('right_leg')->default(false);
            $table->boolean('left_hand')->default(false);
            $table->boolean('right_hand')->default(false);

            // Eye sight & hearing
            $table->boolean('left_eye')->default(false);
            $table->boolean('right_eye')->default(false);
            $table->boolean('left_ear')->default(false);
            $table->boolean('right_ear')->default(false);

            // Year and soft deletes
            $table->year('year');
            $table->softDeletes();
            $table->timestamps();
        });
    }


    public function down(){
        Schema::dropIfExists('student_medical_information');
    }
};
