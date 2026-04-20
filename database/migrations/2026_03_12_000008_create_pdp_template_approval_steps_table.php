<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pdp_template_approval_steps')) {
            return;
        }

        Schema::create('pdp_template_approval_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_template_id');
            $table->string('key', 100);
            $table->string('label', 255);
            $table->unsignedInteger('sequence')->default(1);
            $table->string('role_type', 100);
            $table->boolean('required')->default(true);
            $table->string('period_scope', 100)->nullable();
            $table->boolean('comment_required')->default(false);
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
        Schema::dropIfExists('pdp_template_approval_steps');
    }
};
