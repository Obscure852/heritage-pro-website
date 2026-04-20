<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the user_document_quotas table for managing per-user storage quotas.
 * Default quota is 500MB (524288000 bytes) with configurable warning threshold.
 * Supports unlimited quota override for administrators.
 *
 * Requirements: DOC-07 foundation (quota management)
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('user_document_quotas')) {
            return;
        }

        Schema::create('user_document_quotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();

            // Quota
            $table->unsignedBigInteger('quota_bytes')->default(524288000);  // 500MB default
            $table->unsignedBigInteger('used_bytes')->default(0);

            // Warnings
            $table->tinyInteger('warning_threshold_percent')->unsigned()->default(80);
            $table->timestamp('warning_sent_at')->nullable();

            // Override
            $table->boolean('is_unlimited')->default(false);

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('user_document_quotas');
    }
};
