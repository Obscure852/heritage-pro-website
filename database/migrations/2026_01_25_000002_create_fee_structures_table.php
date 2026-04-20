<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fee structures are defined per grade per year (annual fee schedule).
 * One fee type can only have one amount per grade per year.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('fee_structures')) {
            return;
        }
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_type_id');
            $table->unsignedBigInteger('grade_id');
            $table->year('year');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // One fee structure per fee type per grade per year
            $table->unique(['fee_type_id', 'grade_id', 'year']);
            $table->index('grade_id');
            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
