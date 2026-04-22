<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_commercial_currencies')) {
            return;
        }

        Schema::create('crm_commercial_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 120);
            $table->string('symbol', 12);
            $table->string('symbol_position', 12)->default('before');
            $table->unsignedTinyInteger('precision')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('crm_commercial_currencies')->insert([
            'code' => 'BWP',
            'name' => 'Botswana Pula',
            'symbol' => 'P',
            'symbol_position' => 'before',
            'precision' => 2,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_commercial_currencies');
    }
};
