<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('senior_admission_placement_criteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_setup_id');
            $table->string('pathway');
            $table->unsignedInteger('priority')->default(1);
            $table->string('science_best_grade')->nullable();
            $table->string('science_worst_grade')->nullable();
            $table->string('mathematics_best_grade')->nullable();
            $table->string('mathematics_worst_grade')->nullable();
            $table->unsignedInteger('target_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_setup_id', 'pathway'], 'senior_adm_place_criteria_school_pathway_unique');
            $table->foreign('school_setup_id')
                ->references('id')
                ->on('school_setup')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('senior_admission_placement_criteria');
    }
};
