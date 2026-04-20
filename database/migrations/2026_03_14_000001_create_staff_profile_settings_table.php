<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('staff_profile_settings')) {
            return;
        }

        Schema::create('staff_profile_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('key', 'staff_profile_settings_key_index');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        DB::table('staff_profile_settings')->insert([
            [
                'key' => 'force_profile_update_enabled',
                'value' => json_encode(false),
                'description' => 'Whether staff are forced to complete their profile before accessing the system',
                'updated_at' => now(),
            ],
            [
                'key' => 'force_profile_update_sections',
                'value' => json_encode(['basic_info']),
                'description' => 'Which profile sections are required when force update is enabled',
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profile_settings');
    }
};
