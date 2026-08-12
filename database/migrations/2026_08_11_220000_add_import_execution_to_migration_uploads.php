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
            if (! Schema::hasColumn('crm_client_setup_migration_uploads', 'import_status')) {
                $table->string('import_status', 30)->default('not_started')->after('crm_approval_note')->index();
            }
            if (! Schema::hasColumn('crm_client_setup_migration_uploads', 'import_requested_by_id')) {
                $table->unsignedBigInteger('import_requested_by_id')->nullable()->after('import_status');
            }
            if (! Schema::hasColumn('crm_client_setup_migration_uploads', 'import_started_at')) {
                $table->timestamp('import_started_at')->nullable()->after('import_requested_by_id');
            }
            if (! Schema::hasColumn('crm_client_setup_migration_uploads', 'import_completed_at')) {
                $table->timestamp('import_completed_at')->nullable()->after('import_started_at');
            }
            if (! Schema::hasColumn('crm_client_setup_migration_uploads', 'import_reference')) {
                $table->string('import_reference', 255)->nullable()->after('import_completed_at');
            }
            if (! Schema::hasColumn('crm_client_setup_migration_uploads', 'import_summary')) {
                $table->json('import_summary')->nullable()->after('import_reference');
            }
            if (! Schema::hasColumn('crm_client_setup_migration_uploads', 'import_error')) {
                $table->text('import_error')->nullable()->after('import_summary');
            }
        });

        Schema::table('crm_client_setup_migration_uploads', function (Blueprint $table): void {
            $table->foreign('import_requested_by_id', 'crm_setup_import_requester_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_client_setup_migration_uploads')) {
            return;
        }

        Schema::table('crm_client_setup_migration_uploads', function (Blueprint $table): void {
            if (Schema::hasColumn('crm_client_setup_migration_uploads', 'import_requested_by_id')) {
                $table->dropForeign('crm_setup_import_requester_fk');
            }
            $columns = [];
            foreach (['import_error', 'import_summary', 'import_reference', 'import_completed_at', 'import_started_at', 'import_requested_by_id', 'import_status'] as $column) {
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
