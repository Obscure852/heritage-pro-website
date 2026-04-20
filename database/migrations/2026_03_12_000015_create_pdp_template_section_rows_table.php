<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pdp_template_section_rows')) {
            return;
        }

        Schema::create('pdp_template_section_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pdp_template_section_id')->constrained('pdp_template_sections')->cascadeOnDelete();
            $table->string('key', 100);
            $table->json('values_json')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['pdp_template_section_id', 'key'], 'pdp_template_section_rows_section_key_unique');
            $table->index(['pdp_template_section_id', 'sort_order'], 'pdp_template_section_rows_section_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdp_template_section_rows');
    }
};
