<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crm_client_setup_change_requests', function (Blueprint $table): void {
            $table->text('client_response')->nullable()->after('body');
            $table->timestamp('responded_at')->nullable()->after('client_response');
        });
    }

    public function down(): void
    {
        Schema::table('crm_client_setup_change_requests', function (Blueprint $table): void {
            $table->dropColumn(['client_response', 'responded_at']);
        });
    }
};
