<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pdp_templates')) {
            return;
        }

        Schema::create('pdp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_family_key', 100);
            $table->unsignedInteger('version')->default(1);
            $table->string('code', 150)->unique();
            $table->string('name', 255);
            $table->string('source_reference', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('draft');
            $table->boolean('is_default')->default(false);
            $table->json('settings_json')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['template_family_key', 'version']);
            $table->index(['status', 'is_default']);

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdp_templates');
    }
};
