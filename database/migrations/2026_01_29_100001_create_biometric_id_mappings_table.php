<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the biometric_id_mappings table for linking device employee_numbers to users.
     * Supports both auto-matched and manual mappings with audit trail.
     * DEV-06 requirement: biometric ID to staff record mapping.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('biometric_id_mappings')) {
            return;
        }
        Schema::create('biometric_id_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number', 50)->unique();          // From device, must be unique
            $table->unsignedBigInteger('user_id');                    // Staff member this maps to
            $table->enum('source', ['auto', 'manual']);               // How mapping was created
            $table->unsignedBigInteger('created_by')->nullable();     // Who created (manual mappings)
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_id_mappings');
    }
};
