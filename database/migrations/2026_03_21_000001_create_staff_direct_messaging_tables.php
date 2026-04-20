<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_direct_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_two_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('user_one_read_at')->nullable();
            $table->timestamp('user_two_read_at')->nullable();
            $table->boolean('is_archived_by_user_one')->default(false);
            $table->boolean('is_archived_by_user_two')->default(false);
            $table->timestamps();

            $table->unique(['user_one_id', 'user_two_id'], 'staff_direct_unique_pair');
            $table->index(['user_one_id', 'last_message_at'], 'staff_direct_user_one_index');
            $table->index(['user_two_id', 'last_message_at'], 'staff_direct_user_two_index');
        });

        Schema::create('staff_direct_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('staff_direct_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['conversation_id', 'created_at'], 'staff_direct_messages_conversation_created_index');
        });

        Schema::create('staff_user_presence', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_seen_at');
            $table->string('last_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_seen_at'], 'staff_user_presence_user_seen_index');
            $table->index('last_seen_at', 'staff_user_presence_seen_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_user_presence');
        Schema::dropIfExists('staff_direct_messages');
        Schema::dropIfExists('staff_direct_conversations');
    }
};
