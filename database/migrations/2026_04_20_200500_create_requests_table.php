<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('requests')) {
            return;
        }

        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('sales_stage_id')->nullable()->constrained('sales_stages')->nullOnDelete();
            $table->string('type', 20);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('support_status', 30)->nullable();
            $table->string('outcome', 30)->nullable();
            $table->string('next_action')->nullable();
            $table->timestamp('next_action_at')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'type']);
            $table->index(['type', 'sales_stage_id']);
            $table->index(['type', 'support_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
