<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('twilio');
            $table->string('external_id')->unique();
            $table->string('name');
            $table->string('language')->default('en');
            $table->string('category')->nullable();
            $table->string('status')->default('draft');
            $table->string('body_preview')->nullable();
            $table->json('variables')->nullable();
            $table->json('content')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index(['name', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
