<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipient_channel_consents', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_type');
            $table->unsignedBigInteger('recipient_id');
            $table->string('channel');
            $table->string('status')->default('opted_out');
            $table->string('source')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('opted_out_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['recipient_type', 'recipient_id', 'channel'], 'recipient_channel_unique');
            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipient_channel_consents');
    }
};
