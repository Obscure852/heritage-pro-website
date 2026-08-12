<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_client_setup_migration_uploads')) {
            return;
        }

        Schema::table('crm_client_setup_migration_uploads', function (Blueprint $table): void {
            if (! Schema::hasColumn('crm_client_setup_migration_uploads', 'template_compatibility_status')) {
                $table->string('template_compatibility_status', 40)->default('unknown')->after('template_version');
            }
            if (! Schema::hasColumn('crm_client_setup_migration_uploads', 'template_fingerprint')) {
                $table->string('template_fingerprint', 64)->nullable()->after('template_compatibility_status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_client_setup_migration_uploads')) {
            return;
        }

        Schema::table('crm_client_setup_migration_uploads', function (Blueprint $table): void {
            $columns = [];
            foreach (['template_fingerprint', 'template_compatibility_status'] as $column) {
                if (Schema::hasColumn('crm_client_setup_migration_uploads', $column)) {
                    $columns[] = $column;
                }
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
