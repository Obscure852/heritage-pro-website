<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->timestamp('expiry_warning_sent_at')->nullable()->after('legal_hold_at');
            $table->timestamp('grace_period_notification_sent_at')->nullable()->after('expiry_warning_sent_at');
        });
    }

    public function down(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['expiry_warning_sent_at', 'grace_period_notification_sent_at']);
        });
    }
};
