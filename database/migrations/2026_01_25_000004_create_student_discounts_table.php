<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student discounts are assigned per student per year (annual discounts).
 * A student can only have one discount of each type per year.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('student_discounts')) {
            return;
        }
        Schema::create('student_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('discount_type_id');
            $table->year('year');
            $table->unsignedBigInteger('assigned_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('discount_type_id')->references('id')->on('discount_types')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');

            // One discount type per student per year
            $table->unique(['student_id', 'discount_type_id', 'year']);
            $table->index('student_id');
            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_discounts');
    }
};
