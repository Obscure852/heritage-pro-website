<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the unmapped_biometric_ids table for tracking device IDs that cannot be matched.
     * Aggregates event counts and timestamps for admin attention.
     * DEV-06 requirement: unmapped biometric ID tracking.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('unmapped_biometric_ids')) {
            return;
        }
        Schema::create('unmapped_biometric_ids', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number', 50)->unique();          // From device, must be unique
            $table->timestamp('first_seen_at');                       // When this ID was first detected
            $table->timestamp('last_seen_at');                        // Most recent event from this ID
            $table->unsignedInteger('event_count')->default(1);       // Number of events from this ID
            $table->timestamps();

            // Index for querying recent unmapped IDs
            $table->index('last_seen_at', 'unmapped_biometric_ids_last_seen_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('unmapped_biometric_ids');
    }
};
