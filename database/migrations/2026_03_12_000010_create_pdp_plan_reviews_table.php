<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdp_plan_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pdp_plan_id')->constrained('pdp_plans')->cascadeOnDelete();
            $table->string('period_key', 64);
            $table->string('status', 32)->default('pending');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('score_summary_json')->nullable();
            $table->text('narrative_summary')->nullable();
            $table->timestamps();

            $table->unique(['pdp_plan_id', 'period_key'], 'pdp_plan_reviews_plan_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdp_plan_reviews');
    }
};
