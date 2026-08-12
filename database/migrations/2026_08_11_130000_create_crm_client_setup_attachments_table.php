<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_client_setup_attachments')) {
            return;
        }

        Schema::create('crm_client_setup_attachments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('submission_id')->constrained('crm_client_setup_submissions')->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('crm_client_setup_invitations')->nullOnDelete();
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 100);
            $table->string('requirement', 30)->default('optional');
            $table->string('original_name', 255);
            $table->string('disk', 40)->default('documents');
            $table->string('path', 500);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256', 64)->nullable();
            $table->string('scan_status', 30)->default('pending');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->index(['submission_id', 'category'], 'crm_client_setup_attachment_category_index');
            $table->index(['submission_id', 'scan_status'], 'crm_client_setup_attachment_scan_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_client_setup_attachments');
    }
};
