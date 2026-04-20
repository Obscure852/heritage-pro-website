<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pdp_template_fields')) {
            return;
        }

        Schema::create('pdp_template_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_template_section_id');
            $table->unsignedBigInteger('parent_field_id')->nullable();
            $table->string('key', 100);
            $table->string('label', 255);
            $table->string('field_type', 100);
            $table->string('data_type', 50)->nullable();
            $table->string('input_mode', 50)->default('manual_entry');
            $table->boolean('required')->default(false);
            $table->json('validation_rules_json')->nullable();
            $table->string('mapping_source', 50)->nullable();
            $table->string('mapping_key', 255)->nullable();
            $table->json('default_value_json')->nullable();
            $table->json('options_json')->nullable();
            $table->string('period_scope', 100)->nullable();
            $table->string('rating_scheme_key', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['pdp_template_section_id', 'key']);

            $table->foreign('pdp_template_section_id')
                ->references('id')
                ->on('pdp_template_sections')
                ->onDelete('cascade');

            $table->foreign('parent_field_id')
                ->references('id')
                ->on('pdp_template_fields')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdp_template_fields');
    }
};
