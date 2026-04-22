<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_discussion_threads')) {
            return;
        }

        Schema::create('crm_discussion_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('initiated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direct_participant_key', 64)->nullable();
            $table->foreignId('integration_id')->nullable()->constrained('crm_integrations')->nullOnDelete();
            $table->string('subject');
            $table->string('channel', 20)->default('app');
            $table->string('kind', 30)->default('external_direct');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('delivery_status', 30)->default('sent');
            $table->string('status', 20)->default('sent');
            $table->timestamp('last_message_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_type', 40)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('target_type', 30)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->timestamp('metadata_updated_at')->nullable();
            $table->foreignId('edited_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['channel', 'delivery_status']);
            $table->index(['initiated_by_id', 'recipient_user_id']);
        });

        if (! Schema::hasTable('crm_discussion_thread_participants')) {
            Schema::create('crm_discussion_thread_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('thread_id')->constrained('crm_discussion_threads')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role', 20)->default('member');
                $table->timestamp('last_read_at')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();

                $table->unique(['thread_id', 'user_id'], 'crm_discussion_thread_participants_unique');
                $table->index(['user_id', 'archived_at'], 'crm_discussion_thread_participants_user_archived_idx');
                $table->index(['thread_id', 'last_read_at'], 'crm_discussion_thread_participants_thread_read_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_discussion_thread_participants');
        Schema::dropIfExists('crm_discussion_threads');
    }
};
