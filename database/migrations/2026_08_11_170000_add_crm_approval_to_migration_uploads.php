<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crm_client_setup_migration_uploads', function (Blueprint $table): void {
            $table->string('crm_approval_status', 30)->default('pending')->after('validation_status')->index();
            $table->foreignId('crm_approved_by_id')->nullable()->constrained('users')->nullOnDelete()->after('crm_approval_status');
            $table->timestamp('crm_approved_at')->nullable()->after('crm_approved_by_id');
            $table->text('crm_approval_note')->nullable()->after('crm_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('crm_client_setup_migration_uploads', function (Blueprint $table): void {
            $table->dropForeign(['crm_approved_by_id']);
            $table->dropColumn(['crm_approval_status', 'crm_approved_by_id', 'crm_approved_at', 'crm_approval_note']);
        });
    }
};
