<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the staff_attendance_codes table for configurable attendance code definitions.
     * Each code represents a status type (Present, Absent, Late, etc.) with display properties.
     * MAN-04 requirement: configurable codes with color and counts_as_present flag.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('staff_attendance_codes')) {
            return;
        }
        Schema::create('staff_attendance_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();              // Short code (P, A, L, etc.)
            $table->string('name', 50);                        // Full name (Present, Absent)
            $table->string('description', 255)->nullable();    // Optional description
            $table->string('color', 7)->default('#10b981');    // Hex color for UI display
            $table->boolean('counts_as_present')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('order', 'staff_attendance_codes_order_index');
            $table->index('is_active', 'staff_attendance_codes_active_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendance_codes');
    }
};
