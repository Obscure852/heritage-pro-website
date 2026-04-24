<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_products')) {
            Schema::create('crm_products', function (Blueprint $table) {
                $table->id();
                $table->string('code', 60)->nullable()->unique();
                $table->string('name');
                $table->string('type', 30);
                $table->text('description')->nullable();
                $table->string('billing_frequency', 20)->default('one_time');
                $table->string('default_unit_label', 40)->default('unit');
                $table->decimal('default_unit_price', 12, 2)->default(0);
                $table->decimal('cpi_increase_rate', 5, 2)->default(0);
                $table->decimal('default_tax_rate', 5, 2)->default(0);
                $table->boolean('active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['type', 'active']);
                $table->index('name');
            });
        }

        if (! Schema::hasTable('crm_product_units')) {
            Schema::create('crm_product_units', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80);
                $table->string('label', 40)->unique();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });

            $now = now();
            DB::table('crm_product_units')->insert([
                ['name' => 'Unit', 'label' => 'unit', 'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'License', 'label' => 'license', 'is_active' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'User', 'label' => 'user', 'is_active' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Seat', 'label' => 'seat', 'is_active' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Hour', 'label' => 'hour', 'is_active' => true, 'sort_order' => 50, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Day', 'label' => 'day', 'is_active' => true, 'sort_order' => 60, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Session', 'label' => 'session', 'is_active' => true, 'sort_order' => 70, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Package', 'label' => 'package', 'is_active' => true, 'sort_order' => 80, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Project', 'label' => 'project', 'is_active' => true, 'sort_order' => 90, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Month', 'label' => 'month', 'is_active' => true, 'sort_order' => 100, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Year', 'label' => 'year', 'is_active' => true, 'sort_order' => 110, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_product_units');
        Schema::dropIfExists('crm_products');
    }
};
