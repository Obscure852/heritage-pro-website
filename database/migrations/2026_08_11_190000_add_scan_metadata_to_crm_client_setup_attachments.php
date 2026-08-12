<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_client_setup_attachments')) {
            return;
        }

        Schema::table('crm_client_setup_attachments', function (Blueprint $table): void {
            if (! Schema::hasColumn('crm_client_setup_attachments', 'scan_provider')) {
                $table->string('scan_provider', 150)->nullable()->after('scan_status');
            }
            if (! Schema::hasColumn('crm_client_setup_attachments', 'scan_reference')) {
                $table->string('scan_reference', 255)->nullable()->after('scan_provider');
            }
            if (! Schema::hasColumn('crm_client_setup_attachments', 'scan_message')) {
                $table->string('scan_message', 1000)->nullable()->after('scan_reference');
            }
            if (! Schema::hasColumn('crm_client_setup_attachments', 'scan_completed_at')) {
                $table->timestamp('scan_completed_at')->nullable()->after('scan_message');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_client_setup_attachments')) {
            return;
        }

        Schema::table('crm_client_setup_attachments', function (Blueprint $table): void {
            $columns = [];
            foreach (['scan_completed_at', 'scan_message', 'scan_reference', 'scan_provider'] as $column) {
                if (Schema::hasColumn('crm_client_setup_attachments', $column)) {
                    $columns[] = $column;
                }
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
