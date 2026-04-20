<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::table('library_settings')->insert([
            'key' => 'lost_book_fine',
            'value' => json_encode(['amount' => 100.00]),
            'description' => 'Fixed fine amount for lost books when no replacement cost available (BWP)',
            'updated_at' => now(),
        ]);
    }

    public function down(): void {
        DB::table('library_settings')->where('key', 'lost_book_fine')->delete();
    }
};
