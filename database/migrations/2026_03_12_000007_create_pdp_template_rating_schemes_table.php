<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pdp_template_rating_schemes')) {
            return;
        }

        Schema::create('pdp_template_rating_schemes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_template_id');
            $table->string('key', 100);
            $table->string('label', 255);
            $table->string('input_type', 50);
            $table->json('scale_config_json')->nullable();
            $table->json('conversion_config_json')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->string('rounding_rule', 50)->nullable();
            $table->json('formula_config_json')->nullable();
            $table->json('band_config_json')->nullable();
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
        Schema::dropIfExists('pdp_template_rating_schemes');
    }
};
