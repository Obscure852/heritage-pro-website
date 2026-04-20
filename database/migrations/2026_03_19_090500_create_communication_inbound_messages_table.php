<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_inbound_messages', function (Blueprint $table) {
            $table->id();
            $table->string('channel');
            $table->string('provider')->nullable();
            $table->string('external_message_id')->nullable()->index();
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['channel', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_inbound_messages');
    }
};
