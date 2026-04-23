<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_attendance_settings')) {
            Schema::create('crm_attendance_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('show_topbar_clock')->default(true);
                $table->boolean('show_dashboard_clock')->default(true);
                $table->timestamps();
            });

            DB::table('crm_attendance_settings')->insert([
                'show_topbar_clock' => true,
                'show_dashboard_clock' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_attendance_settings');
    }
};
