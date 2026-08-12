<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_client_setup_migration_errors')) {
            return;
        }

        Schema::create('crm_client_setup_migration_errors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('migration_upload_id')
                ->constrained('crm_client_setup_migration_uploads')
                ->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('messages');
            $table->timestamps();

            $table->index(['migration_upload_id', 'row_number'], 'crm_client_setup_migration_error_row_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_client_setup_migration_errors');
    }
};
