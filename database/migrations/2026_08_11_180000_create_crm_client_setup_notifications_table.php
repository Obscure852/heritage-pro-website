<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_client_setup_notifications')) {
            return;
        }

        Schema::create('crm_client_setup_notifications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('submission_id')->nullable()->constrained('crm_client_setup_submissions')->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained('crm_client_setup_invitations')->nullOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('audience', 20);
            $table->string('event_key', 80);
            $table->string('channel', 20)->default('email');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->json('payload')->nullable();
            $table->string('idempotency_key', 191)->unique();
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('available_at')->nullable()->index();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamps();
            $table->index(['submission_id', 'event_key'], 'crm_client_setup_notification_event_index');
            $table->index(['status', 'available_at'], 'crm_client_setup_notification_retry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_client_setup_notifications');
    }
};
