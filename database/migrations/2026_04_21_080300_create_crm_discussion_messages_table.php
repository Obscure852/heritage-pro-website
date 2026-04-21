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
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_discussion_messages');
    }
};
