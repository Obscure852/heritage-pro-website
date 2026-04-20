<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the biometric_raw_events table for storing raw events from biometric devices.
     * Stores unprocessed clock events before they are converted to attendance records.
     * DEV-05 requirement: device_id, employee_number, event_timestamp, event_type.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('biometric_raw_events')) {
            return;
        }
        Schema::create('biometric_raw_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');                        // Device that captured the event
            $table->string('employee_number', 50);                          // From device, maps to users.id_number
            $table->timestamp('event_timestamp');                           // When event occurred on device
            $table->string('event_type', 30);                               // 'clock_in', 'clock_out', 'break_start', 'break_end'
            $table->json('raw_payload')->nullable();                        // Full device response for debugging
            $table->boolean('processed')->default(false);                   // Whether event has been processed
            $table->timestamp('processed_at')->nullable();                  // When event was processed
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('device_id', 'biometric_raw_events_device_index');
            $table->index('employee_number', 'biometric_raw_events_employee_index');
            $table->index('event_timestamp', 'biometric_raw_events_timestamp_index');
            $table->index(['employee_number', 'event_timestamp'], 'biometric_raw_events_employee_timestamp_index');
            $table->index('processed', 'biometric_raw_events_processed_index');

            // Foreign key constraint
            $table->foreign('device_id')
                ->references('id')
                ->on('attendance_devices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_raw_events');
    }
};
