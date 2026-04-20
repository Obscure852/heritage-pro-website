<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pdp_template_periods')) {
            return;
        }

        Schema::create('pdp_template_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_template_id');
            $table->string('key', 100);
            $table->string('label', 255);
            $table->unsignedInteger('sequence')->default(1);
            $table->string('window_type', 50)->default('relative');
            $table->json('due_rule_json')->nullable();
            $table->json('open_rule_json')->nullable();
            $table->json('close_rule_json')->nullable();
            $table->boolean('include_in_final_score')->default(true);
            $table->string('summary_label', 255)->nullable();
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
        Schema::dropIfExists('pdp_template_periods');
    }
};
