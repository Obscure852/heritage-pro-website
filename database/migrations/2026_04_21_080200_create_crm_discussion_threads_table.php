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
            $table->foreignId('integration_id')->nullable()->constrained('crm_integrations')->nullOnDelete();
            $table->string('subject');
            $table->string('channel', 20)->default('app');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('delivery_status', 30)->default('sent');
            $table->timestamp('last_message_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['channel', 'delivery_status']);
            $table->index(['initiated_by_id', 'recipient_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_discussion_threads');
    }
};
