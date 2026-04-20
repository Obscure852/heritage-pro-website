<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('fee_audit_logs')) {
            return;
        }
        Schema::create('fee_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type', 255);
            $table->unsignedBigInteger('auditable_id');
            $table->string('action', 50);
            $table->unsignedBigInteger('user_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45);
            $table->timestamp('created_at');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_audit_logs');
    }
};
