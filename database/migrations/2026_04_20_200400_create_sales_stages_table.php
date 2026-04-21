<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sales_stages')) {
            return;
        }

        Schema::create('sales_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->timestamps();
        });

        $defaults = [
            ['name' => 'Cold', 'position' => 1, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Contacted', 'position' => 2, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Qualified', 'position' => 3, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Demo Scheduled', 'position' => 4, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Proposal Sent', 'position' => 5, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Negotiation', 'position' => 6, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Won', 'position' => 7, 'is_won' => true, 'is_lost' => false],
            ['name' => 'Lost', 'position' => 8, 'is_won' => false, 'is_lost' => true],
        ];

        DB::table('sales_stages')->insert(array_map(static fn (array $stage) => [
            'name' => $stage['name'],
            'slug' => Str::slug($stage['name']),
            'position' => $stage['position'],
            'is_active' => true,
            'is_won' => $stage['is_won'],
            'is_lost' => $stage['is_lost'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $defaults));
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_stages');
    }
};
