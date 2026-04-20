<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timetable_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('key');
        });

        $defaults = [
            [
                'key' => 'cycle_days',
                'value' => json_encode(6),
                'description' => 'Number of days in the timetable rotation cycle',
                'updated_at' => now(),
            ],
            [
                'key' => 'periods_per_day',
                'value' => json_encode(7),
                'description' => 'Number of teaching periods per day',
                'updated_at' => now(),
            ],
            [
                'key' => 'period_duration_minutes',
                'value' => json_encode(40),
                'description' => 'Duration of each period in minutes',
                'updated_at' => now(),
            ],
            [
                'key' => 'break_intervals',
                'value' => json_encode([
                    ['after_period' => 3, 'duration' => 20, 'label' => 'Tea Break'],
                    ['after_period' => 5, 'duration' => 45, 'label' => 'Lunch'],
                ]),
                'description' => 'Break intervals between periods',
                'updated_at' => now(),
            ],
        ];
        DB::table('timetable_settings')->insert($defaults);
    }

    public function down(): void {
        Schema::dropIfExists('timetable_settings');
    }
};
