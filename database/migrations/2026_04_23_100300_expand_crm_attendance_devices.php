<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crm_attendance_devices', function (Blueprint $table) {
            $table->string('brand', 30)->default('zkteco')->after('name');
            $table->string('model', 80)->nullable()->after('brand');
            $table->string('serial_number', 80)->nullable()->after('device_identifier');
            $table->string('ip_address', 45)->nullable()->after('serial_number');
            $table->unsignedSmallInteger('port')->nullable()->after('ip_address');
            $table->string('communication_key', 100)->nullable()->after('port');
            $table->string('protocol', 20)->default('push')->after('communication_key');
            $table->string('direction', 10)->default('both')->after('protocol');
            $table->string('timezone', 40)->nullable()->after('direction');
            $table->unsignedSmallInteger('heartbeat_interval')->default(60)->after('timezone');
            $table->unsignedSmallInteger('push_interval')->default(30)->after('heartbeat_interval');
            $table->string('firmware_version', 60)->nullable()->after('push_interval');
            $table->unsignedInteger('user_capacity')->nullable()->after('firmware_version');
            $table->unsignedInteger('fingerprint_capacity')->nullable()->after('user_capacity');
            $table->unsignedInteger('face_capacity')->nullable()->after('fingerprint_capacity');
            $table->json('supported_verify_methods')->nullable()->after('face_capacity');
            $table->json('device_options')->nullable()->after('supported_verify_methods');
        });

        Schema::table('crm_attendance_device_logs', function (Blueprint $table) {
            $table->string('card_number', 30)->nullable()->after('verification_method');
            $table->decimal('temperature', 4, 1)->nullable()->after('card_number');
            $table->string('work_code', 20)->nullable()->after('temperature');
            $table->text('raw_payload')->nullable()->after('error_message');
        });
    }

    public function down(): void
    {
        Schema::table('crm_attendance_devices', function (Blueprint $table) {
            $table->dropColumn([
                'brand', 'model', 'serial_number', 'ip_address', 'port',
                'communication_key', 'protocol', 'direction', 'timezone',
                'heartbeat_interval', 'push_interval', 'firmware_version',
                'user_capacity', 'fingerprint_capacity', 'face_capacity',
                'supported_verify_methods', 'device_options',
            ]);
        });

        Schema::table('crm_attendance_device_logs', function (Blueprint $table) {
            $table->dropColumn(['card_number', 'temperature', 'work_code', 'raw_payload']);
        });
    }
};
