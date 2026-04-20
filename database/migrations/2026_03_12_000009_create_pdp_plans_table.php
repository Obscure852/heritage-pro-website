<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdp_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pdp_template_id')->constrained('pdp_templates');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('plan_period_start');
            $table->date('plan_period_end');
            $table->string('status', 32)->default('draft');
            $table->string('current_period_key', 64)->nullable();
            $table->json('calculated_summary_json')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status'], 'pdp_plans_user_status_idx');
            $table->index(['pdp_template_id', 'plan_period_start', 'plan_period_end'], 'pdp_plans_template_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdp_plans');
    }
};
