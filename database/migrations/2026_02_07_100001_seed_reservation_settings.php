<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $defaults = [
            [
                'key' => 'reservation_pickup_window',
                'value' => json_encode(['days' => 3]),
                'description' => 'Number of days a borrower has to collect a reserved book after notification',
                'updated_at' => now(),
            ],
            [
                'key' => 'max_reservations_per_borrower',
                'value' => json_encode(['student' => 2, 'staff' => 3]),
                'description' => 'Maximum number of active reservations per borrower type',
                'updated_at' => now(),
            ],
        ];

        DB::table('library_settings')->insert($defaults);
    }

    public function down(): void {
        DB::table('library_settings')->whereIn('key', [
            'reservation_pickup_window',
            'max_reservations_per_borrower',
        ])->delete();
    }
};
