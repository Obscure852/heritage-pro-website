<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pdp_template_sections')) {
            return;
        }

        Schema::create('pdp_template_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_template_id');
            $table->string('key', 100);
            $table->string('label', 255);
            $table->string('section_type', 100);
            $table->unsignedInteger('sequence')->default(1);
            $table->boolean('is_repeatable')->default(false);
            $table->unsignedSmallInteger('min_items')->default(0);
            $table->unsignedSmallInteger('max_items')->nullable();
            $table->json('applies_when_json')->nullable();
            $table->json('editable_by_json')->nullable();
            $table->json('layout_config_json')->nullable();
            $table->json('print_config_json')->nullable();
            $table->timestamps();

            $table->unique(['pdp_template_id', 'key']);

            $table->foreign('pdp_template_id')
                ->references('id')
                ->on('pdp_templates')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdp_template_sections');
    }
};
