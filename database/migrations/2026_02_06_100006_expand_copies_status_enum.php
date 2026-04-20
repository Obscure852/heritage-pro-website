<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE copies MODIFY COLUMN status ENUM('available', 'checked_out', 'in_repair', 'lost', 'on_hold') DEFAULT 'available'");
    }

    public function down(): void {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE copies MODIFY COLUMN status ENUM('available', 'checked_out', 'in_repair', 'lost') DEFAULT 'available'");
    }
};
