<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type', 20);
            $table->string('scope_value', 100)->nullable();
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->unsignedInteger('expected_count')->default(0);
            $table->unsignedInteger('scanned_count')->default(0);
            $table->unsignedInteger('discrepancy_count')->default(0);
            $table->foreignId('started_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['scope_type', 'scope_value']);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_session_id')->constrained('inventory_sessions')->cascadeOnDelete();
            $table->foreignId('copy_id')->constrained('copies')->cascadeOnDelete();
            $table->foreignId('scanned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scanned_at');

            $table->unique(['inventory_session_id', 'copy_id']);
            $table->index('inventory_session_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_sessions');
    }
};
