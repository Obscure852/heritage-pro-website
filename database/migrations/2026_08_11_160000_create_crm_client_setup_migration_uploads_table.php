<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_client_setup_migration_uploads')) {
            Schema::create('crm_client_setup_migration_uploads', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('submission_id')->constrained('crm_client_setup_submissions')->cascadeOnDelete();
                $table->foreignId('invitation_id')->nullable()->constrained('crm_client_setup_invitations')->nullOnDelete();
                $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('attachment_id')->nullable()->constrained('crm_client_setup_attachments')->nullOnDelete();
                $table->string('kind', 30);
                $table->string('template_version', 30);
                $table->string('original_name');
                $table->unsignedInteger('row_count')->default(0);
                $table->unsignedInteger('valid_row_count')->default(0);
                $table->unsignedInteger('error_count')->default(0);
                $table->string('validation_status', 30)->default('pending')->index();
                $table->json('validation_errors')->nullable();
                $table->json('headers')->nullable();
                $table->timestamp('uploaded_at')->index();
                $table->timestamps();

                $table->index(['submission_id', 'kind', 'created_at'], 'crm_client_setup_migration_history_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_client_setup_migration_uploads');
    }
};
