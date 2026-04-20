<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pdp_rollouts')) {
            return;
        }

        Schema::create('pdp_rollouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pdp_template_id')->constrained('pdp_templates');
            $table->string('label', 255);
            $table->unsignedSmallInteger('cycle_year')->nullable();
            $table->date('plan_period_start');
            $table->date('plan_period_end');
            $table->string('status', 32)->default('active');
            $table->string('provisioning_status', 32)->default('completed');
            $table->boolean('auto_provision_new_staff')->default(true);
            $table->foreignId('fallback_supervisor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('provisioned_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->json('summary_json')->nullable();
            $table->json('exceptions_json')->nullable();
            $table->foreignId('launched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('launched_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'auto_provision_new_staff'], 'pdp_rollouts_status_auto_provision_idx');
            $table->index(['cycle_year', 'status'], 'pdp_rollouts_cycle_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdp_rollouts');
    }
};
