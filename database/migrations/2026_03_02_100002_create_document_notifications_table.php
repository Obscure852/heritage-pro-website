<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Creates the document_notifications table for in-app notification tracking.
     * Separate from the existing custom notifications table to avoid conflicts.
     */
    public function up(): void {
        Schema::create('document_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('type', 50);
            $table->string('title', 255);
            $table->text('message');
            $table->json('data')->nullable();
            $table->string('url', 500)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Efficient unread count queries: WHERE user_id = ? AND read_at IS NULL
            $table->index(['user_id', 'read_at'], 'doc_notif_user_read_idx');

            // Ordered listing queries: WHERE user_id = ? ORDER BY created_at DESC
            $table->index(['user_id', 'created_at'], 'doc_notif_user_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_notifications');
    }
};
