<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('crm_products', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_products', 'cpi_increase_rate')) {
                $table->decimal('cpi_increase_rate', 5, 2)->default(0)->after('default_unit_price');
            }
        });

        Schema::table('crm_quotes', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_quotes', 'tax_scope')) {
                $table->string('tax_scope', 20)->default('line')->after('currency_precision');
            }

            if (! Schema::hasColumn('crm_quotes', 'document_tax_rate')) {
                $table->decimal('document_tax_rate', 5, 2)->default(0)->after('tax_scope');
            }
        });

        Schema::table('crm_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_invoices', 'tax_scope')) {
                $table->string('tax_scope', 20)->default('line')->after('currency_precision');
            }

            if (! Schema::hasColumn('crm_invoices', 'document_tax_rate')) {
                $table->decimal('document_tax_rate', 5, 2)->default(0)->after('tax_scope');
            }
        });

        if (! Schema::hasTable('crm_product_units')) {
            Schema::create('crm_product_units', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80);
                $table->string('label', 40)->unique();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        $now = now();
        DB::table('crm_product_units')->upsert([
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
        ], ['label'], ['name', 'is_active', 'sort_order', 'updated_at']);
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_product_units');

        Schema::table('crm_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('crm_invoices', 'document_tax_rate')) {
                $table->dropColumn('document_tax_rate');
            }

            if (Schema::hasColumn('crm_invoices', 'tax_scope')) {
                $table->dropColumn('tax_scope');
            }
        });

        Schema::table('crm_quotes', function (Blueprint $table) {
            if (Schema::hasColumn('crm_quotes', 'document_tax_rate')) {
                $table->dropColumn('document_tax_rate');
            }

            if (Schema::hasColumn('crm_quotes', 'tax_scope')) {
                $table->dropColumn('tax_scope');
            }
        });

        Schema::table('crm_products', function (Blueprint $table) {
            if (Schema::hasColumn('crm_products', 'cpi_increase_rate')) {
                $table->dropColumn('cpi_increase_rate');
            }
        });
    }
};
