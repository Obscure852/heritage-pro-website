<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the document_retention_policies table for configuring automated
 * document retention and cleanup rules. Conditions are stored as JSON for
 * flexible matching criteria (category, folder, age, status).
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (Schema::hasTable('document_retention_policies')) {
            return;
        }

        Schema::create('document_retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();

            // Conditions (JSON for flexibility)
            $table->json('conditions');

            // Action (string, not enum)
            $table->string('action', 20)->default('archive'); // archive, delete, notify_owner
            $table->unsignedInteger('retention_days');
            $table->unsignedInteger('grace_period_days')->default(30);

            // Scheduling
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();

            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('created_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('document_retention_policies');
    }
};
