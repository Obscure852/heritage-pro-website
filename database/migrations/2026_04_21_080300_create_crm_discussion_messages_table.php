<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_discussion_messages')) {
            return;
        }

        Schema::create('crm_discussion_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('crm_discussion_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direction', 20)->default('outbound');
            $table->string('channel', 20)->default('app');
            $table->text('body');
            $table->string('delivery_status', 30)->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
        });

        if (! Schema::hasTable('crm_discussion_message_attachments')) {
            Schema::create('crm_discussion_message_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained('crm_discussion_messages')->cascadeOnDelete();
                $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('disk', 40)->default('documents');
                $table->string('path');
                $table->string('original_name');
                $table->string('mime_type', 150)->nullable();
                $table->string('extension', 20)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();

                $table->index(['message_id', 'created_at'], 'crm_discussion_message_attachments_message_idx');
            });
        }

        if (! Schema::hasTable('crm_discussion_campaigns')) {
            Schema::create('crm_discussion_campaigns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('initiated_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('thread_id')->nullable()->constrained('crm_discussion_threads')->nullOnDelete();
                $table->foreignId('integration_id')->nullable()->constrained('crm_integrations')->nullOnDelete();
                $table->string('channel', 20);
                $table->string('status', 20)->default('draft');
                $table->string('subject');
                $table->text('body');
                $table->text('notes')->nullable();
                $table->json('audience_snapshot')->nullable();
                $table->string('source_type', 40)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->timestamp('last_sent_at')->nullable();
                $table->timestamps();

                $table->index(['channel', 'status'], 'crm_discussion_campaigns_channel_status_idx');
                $table->index(['source_type', 'source_id'], 'crm_discussion_campaigns_source_idx');
            });
        }

        if (! Schema::hasTable('crm_discussion_campaign_recipients')) {
            Schema::create('crm_discussion_campaign_recipients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('crm_discussion_campaigns')->cascadeOnDelete();
                $table->foreignId('thread_id')->nullable()->constrained('crm_discussion_threads')->nullOnDelete();
                $table->foreignId('message_id')->nullable()->constrained('crm_discussion_messages')->nullOnDelete();
                $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('recipient_type', 30)->nullable();
                $table->unsignedBigInteger('recipient_id')->nullable();
                $table->string('recipient_label')->nullable();
                $table->string('recipient_address')->nullable();
                $table->string('delivery_status', 30)->default('queued');
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['campaign_id', 'delivery_status'], 'crm_discussion_campaign_recipients_campaign_status_idx');
                $table->index(['recipient_type', 'recipient_id'], 'crm_discussion_campaign_recipients_target_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_discussion_campaign_recipients');
        Schema::dropIfExists('crm_discussion_campaigns');
        Schema::dropIfExists('crm_discussion_message_attachments');
        Schema::dropIfExists('crm_discussion_messages');
    }
};
