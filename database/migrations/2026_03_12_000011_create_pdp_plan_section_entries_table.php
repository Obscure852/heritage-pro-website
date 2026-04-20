<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdp_plan_section_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pdp_plan_id')->constrained('pdp_plans')->cascadeOnDelete();
            $table->foreignId('pdp_plan_review_id')->nullable()->constrained('pdp_plan_reviews')->cascadeOnDelete();
            $table->foreignId('parent_entry_id')->nullable()->constrained('pdp_plan_section_entries')->cascadeOnDelete();
            $table->string('section_key', 64);
            $table->string('entry_group_key', 64)->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->json('values_json')->nullable();
            $table->json('computed_values_json')->nullable();
            $table->timestamps();

            $table->index(['pdp_plan_id', 'section_key'], 'pdp_plan_section_entries_plan_section_idx');
            $table->index(['pdp_plan_review_id', 'section_key'], 'pdp_plan_section_entries_review_section_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdp_plan_section_entries');
    }
};
