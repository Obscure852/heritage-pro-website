<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_products')) {
            return;
        }

        Schema::create('crm_products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->nullable()->unique();
            $table->string('name');
            $table->string('type', 30);
            $table->text('description')->nullable();
            $table->string('billing_frequency', 20)->default('one_time');
            $table->string('default_unit_label', 40)->default('unit');
            $table->decimal('default_unit_price', 12, 2)->default(0);
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'active']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_products');
    }
};
