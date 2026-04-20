<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdp_plan_signatures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pdp_plan_id')->constrained('pdp_plans')->cascadeOnDelete();
            $table->foreignId('pdp_plan_review_id')->nullable()->constrained('pdp_plan_reviews')->cascadeOnDelete();
            $table->string('approval_step_key', 64);
            $table->string('role_type', 64);
            $table->foreignId('signer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->text('comment')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->index(['pdp_plan_id', 'approval_step_key'], 'pdp_plan_signatures_plan_step_idx');
            $table->index(['pdp_plan_review_id', 'approval_step_key'], 'pdp_plan_signatures_review_step_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdp_plan_signatures');
    }
};
