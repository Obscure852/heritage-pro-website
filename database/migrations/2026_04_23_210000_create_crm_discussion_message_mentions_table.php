<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_discussion_message_mentions')) {
            return;
        }

        Schema::create('crm_discussion_message_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('crm_discussion_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['message_id', 'user_id'], 'crm_discussion_message_mentions_unique');
            $table->index(['user_id', 'created_at'], 'crm_discussion_message_mentions_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_discussion_message_mentions');
    }
};
