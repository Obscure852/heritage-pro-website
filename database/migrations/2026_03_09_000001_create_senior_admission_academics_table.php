<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('senior_admission_academics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->unique();
            $table->string('overall')->nullable();
            $table->string('english')->nullable();
            $table->string('setswana')->nullable();
            $table->string('science')->nullable();
            $table->string('mathematics')->nullable();
            $table->string('agriculture')->nullable();
            $table->string('social_studies')->nullable();
            $table->string('moral_education')->nullable();
            $table->string('design_and_technology')->nullable();
            $table->string('home_economics')->nullable();
            $table->string('office_procedures')->nullable();
            $table->string('accounting')->nullable();
            $table->string('french')->nullable();
            $table->string('art')->nullable();
            $table->string('music')->nullable();
            $table->string('physical_education')->nullable();
            $table->string('religious_education')->nullable();
            $table->string('private_agriculture')->nullable();
            $table->timestamps();

            $table->foreign('admission_id')
                ->references('id')
                ->on('admissions')
                ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('senior_admission_academics');
    }
};
