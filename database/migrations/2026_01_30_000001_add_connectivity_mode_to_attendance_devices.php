<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds connectivity mode fields to support cloud-based deployment:
     * - pull: Server polls device (requires network access to device)
     * - push: Device pushes events to webhook (for Hikvision ISUP)
     * - agent: On-premise agent pushes to cloud API
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasColumn('attendance_devices', 'connectivity_mode')) {
            return;
        }
        Schema::table('attendance_devices', function (Blueprint $table) {
            // Connectivity mode determines how events are collected
            $table->enum('connectivity_mode', ['pull', 'push', 'agent'])
                ->default('pull')
                ->after('is_active')
                ->comment('How events are collected: pull (server polls), push (device webhook), agent (local sync agent)');

            // Webhook secret for verifying push requests (HMAC signature)
            $table->string('webhook_secret', 64)
                ->nullable()
                ->after('connectivity_mode')
                ->comment('Secret key for verifying webhook requests');

            // Public URL for devices that need to reach the server (push mode)
            // This is informational - shows the webhook URL to configure on device
            $table->string('public_url')
                ->nullable()
                ->after('webhook_secret')
                ->comment('Public webhook URL for device push configuration');

            // Index for filtering by connectivity mode
            $table->index('connectivity_mode', 'attendance_devices_connectivity_mode_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('attendance_devices', function (Blueprint $table) {
            $table->dropIndex('attendance_devices_connectivity_mode_index');
            $table->dropColumn(['connectivity_mode', 'webhook_secret', 'public_url']);
        });
    }
};
