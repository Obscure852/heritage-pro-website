<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_client_setup_notes')) {
            Schema::create('crm_client_setup_notes', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('submission_id')->constrained('crm_client_setup_submissions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
                $table->index(['submission_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('crm_client_setup_change_requests')) {
            Schema::create('crm_client_setup_change_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('submission_id')->constrained('crm_client_setup_submissions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('stage_key', 80)->nullable();
                $table->string('field_key', 160)->nullable();
                $table->text('body');
                $table->string('status', 30)->default('open')->index();
                $table->foreignId('resolved_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->index(['submission_id', 'status'], 'crm_client_setup_change_submission_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_client_setup_change_requests');
        Schema::dropIfExists('crm_client_setup_notes');
    }
};
