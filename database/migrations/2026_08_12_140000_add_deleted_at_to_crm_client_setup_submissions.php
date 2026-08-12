<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_client_setup_submissions') && ! Schema::hasColumn('crm_client_setup_submissions', 'deleted_at')) {
            Schema::table('crm_client_setup_submissions', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_client_setup_submissions') && Schema::hasColumn('crm_client_setup_submissions', 'deleted_at')) {
            Schema::table('crm_client_setup_submissions', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }
};
