<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_direct_conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_one_last_read_message_id')->nullable()->after('user_one_read_at');
            $table->unsignedBigInteger('user_two_last_read_message_id')->nullable()->after('user_two_read_at');
            $table->index('user_one_last_read_message_id', 'staff_direct_user_one_last_read_index');
            $table->index('user_two_last_read_message_id', 'staff_direct_user_two_last_read_index');
        });

        DB::table('staff_direct_conversations')
            ->orderBy('id')
            ->chunkById(100, function ($conversations) {
                foreach ($conversations as $conversation) {
                    $userOneLastReadMessageId = null;
                    $userTwoLastReadMessageId = null;

                    if ($conversation->user_one_read_at) {
                        $userOneLastReadMessageId = DB::table('staff_direct_messages')
                            ->where('conversation_id', $conversation->id)
                            ->where('sender_id', '!=', $conversation->user_one_id)
                            ->where('created_at', '<=', $conversation->user_one_read_at)
                            ->max('id');
                    }

                    if ($conversation->user_two_read_at) {
                        $userTwoLastReadMessageId = DB::table('staff_direct_messages')
                            ->where('conversation_id', $conversation->id)
                            ->where('sender_id', '!=', $conversation->user_two_id)
                            ->where('created_at', '<=', $conversation->user_two_read_at)
                            ->max('id');
                    }

                    DB::table('staff_direct_conversations')
                        ->where('id', $conversation->id)
                        ->update([
                            'user_one_last_read_message_id' => $userOneLastReadMessageId,
                            'user_two_last_read_message_id' => $userTwoLastReadMessageId,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('staff_direct_conversations', function (Blueprint $table) {
            $table->dropIndex('staff_direct_user_one_last_read_index');
            $table->dropIndex('staff_direct_user_two_last_read_index');
            $table->dropColumn([
                'user_one_last_read_message_id',
                'user_two_last_read_message_id',
            ]);
        });
    }
};
