<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_integrations')) {
            return;
        }

        Schema::create('crm_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('kind', 30);
            $table->string('status', 20)->default('inactive');
            $table->string('school_code')->nullable();
            $table->string('base_url')->nullable();
            $table->string('auth_type')->nullable();
            $table->text('api_key')->nullable();
            $table->string('webhook_url')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kind', 'status']);
            $table->index('school_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_integrations');
    }
};
