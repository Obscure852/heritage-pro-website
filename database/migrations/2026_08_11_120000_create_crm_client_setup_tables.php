<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_client_setup_submissions')) {
            Schema::create('crm_client_setup_submissions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('primary_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
                $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('schema_version', 40)->default('1.0');
                $table->string('status', 40)->default('draft')->index();
                $table->string('academic_status', 40)->default('not_started')->index();
                $table->json('payload')->nullable();
                $table->json('completed_stages')->nullable();
                $table->timestamp('academic_submitted_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('last_activity_at')->nullable()->index();
                $table->timestamps();

                $table->index(['status', 'academic_status'], 'crm_client_setup_status_index');
            });
        }

        if (! Schema::hasTable('crm_client_setup_invitations')) {
            Schema::create('crm_client_setup_invitations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('submission_id')->constrained('crm_client_setup_submissions')->cascadeOnDelete();
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('email');
                $table->string('contact_name')->nullable();
                $table->string('token_hash', 64)->unique();
                $table->string('status', 30)->default('active')->index();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->string('verification_code_hash')->nullable();
                $table->timestamp('verification_code_expires_at')->nullable();
                $table->unsignedTinyInteger('verification_attempts')->default(0);
                $table->timestamp('verification_sent_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();

                $table->index(['submission_id', 'status'], 'crm_client_setup_invitation_submission_index');
            });
        }

        if (! Schema::hasTable('crm_client_setup_stage_progress')) {
            Schema::create('crm_client_setup_stage_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('crm_client_setup_submissions')->cascadeOnDelete();
                $table->string('stage_key', 80);
                $table->string('status', 30)->default('not_started');
                $table->json('validation_errors')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('last_saved_at')->nullable();
                $table->timestamps();

                $table->unique(['submission_id', 'stage_key'], 'crm_client_setup_stage_unique');
                $table->index(['submission_id', 'status'], 'crm_client_setup_stage_status_index');
            });
        }

        if (! Schema::hasTable('crm_client_setup_revisions')) {
            Schema::create('crm_client_setup_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('crm_client_setup_submissions')->cascadeOnDelete();
                $table->foreignId('invitation_id')->nullable()->constrained('crm_client_setup_invitations')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedInteger('revision_number');
                $table->string('source', 40);
                $table->string('stage_key', 80)->nullable();
                $table->json('payload');
                $table->json('changed_keys')->nullable();
                $table->timestamps();

                $table->unique(['submission_id', 'revision_number'], 'crm_client_setup_revision_unique');
                $table->index(['submission_id', 'created_at'], 'crm_client_setup_revision_history_index');
            });
        }

        if (! Schema::hasTable('crm_client_setup_events')) {
            Schema::create('crm_client_setup_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('crm_client_setup_submissions')->cascadeOnDelete();
                $table->foreignId('invitation_id')->nullable()->constrained('crm_client_setup_invitations')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_type', 30)->default('client');
                $table->string('event_type', 60);
                $table->string('stage_key', 80)->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamps();

                $table->index(['submission_id', 'occurred_at'], 'crm_client_setup_event_history_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_client_setup_events');
        Schema::dropIfExists('crm_client_setup_revisions');
        Schema::dropIfExists('crm_client_setup_stage_progress');
        Schema::dropIfExists('crm_client_setup_invitations');
        Schema::dropIfExists('crm_client_setup_submissions');
    }
};
