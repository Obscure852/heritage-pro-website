<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        if (!Schema::hasTable('s_m_s_api_settings')) {
            return;
        }

        Schema::table('s_m_s_api_settings', function (Blueprint $table): void {
            $table->text('value')->change();
        });
    }

    public function down(): void {
        if (!Schema::hasTable('s_m_s_api_settings')) {
            return;
        }

        Schema::table('s_m_s_api_settings', function (Blueprint $table): void {
            $table->string('value')->change();
        });
    }
};
