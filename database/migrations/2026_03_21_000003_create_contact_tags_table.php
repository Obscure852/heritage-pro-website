<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contact_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique('contact_tags_name_unique');
            $table->string('slug')->unique('contact_tags_slug_unique');
            $table->text('description')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('usable_in_assets')->default(false);
            $table->boolean('usable_in_maintenance')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'usable_in_assets']);
            $table->index(['is_active', 'usable_in_maintenance']);
        });

        DB::table('contact_tags')->insert([
            [
                'name' => 'Vendor',
                'slug' => 'vendor',
                'description' => 'Default tag for external vendors and suppliers used by the Assets module.',
                'color' => '#1d4ed8',
                'is_active' => true,
                'usable_in_assets' => true,
                'usable_in_maintenance' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Supplier',
                'slug' => 'supplier',
                'description' => 'Businesses that supply goods to the school.',
                'color' => '#0f766e',
                'is_active' => true,
                'usable_in_assets' => true,
                'usable_in_maintenance' => false,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maintenance Provider',
                'slug' => 'maintenance-provider',
                'description' => 'Businesses that provide maintenance and repair services.',
                'color' => '#b45309',
                'is_active' => true,
                'usable_in_assets' => false,
                'usable_in_maintenance' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Service Provider',
                'slug' => 'service-provider',
                'description' => 'Businesses that provide external services to the school.',
                'color' => '#7c3aed',
                'is_active' => true,
                'usable_in_assets' => true,
                'usable_in_maintenance' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Contractor',
                'slug' => 'contractor',
                'description' => 'Businesses engaged for project or contract work.',
                'color' => '#be123c',
                'is_active' => true,
                'usable_in_assets' => true,
                'usable_in_maintenance' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_tags');
    }
};
