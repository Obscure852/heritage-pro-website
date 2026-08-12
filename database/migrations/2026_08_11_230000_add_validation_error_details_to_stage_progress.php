<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_client_setup_stage_progress') && ! Schema::hasColumn('crm_client_setup_stage_progress', 'validation_error_details')) {
            Schema::table('crm_client_setup_stage_progress', function (Blueprint $table): void {
                $table->json('validation_error_details')->nullable()->after('validation_errors');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_client_setup_stage_progress') && Schema::hasColumn('crm_client_setup_stage_progress', 'validation_error_details')) {
            Schema::table('crm_client_setup_stage_progress', function (Blueprint $table): void {
                $table->dropColumn('validation_error_details');
            });
        }
    }
};
