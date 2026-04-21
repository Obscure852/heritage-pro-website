<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_development_requests')) {
            return;
        }

        Schema::create('crm_development_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('requested_by')->nullable();
            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('backlog');
            $table->string('target_module')->nullable();
            $table->text('business_value')->nullable();
            $table->string('next_step')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'status']);
            $table->index(['customer_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_development_requests');
    }
};
