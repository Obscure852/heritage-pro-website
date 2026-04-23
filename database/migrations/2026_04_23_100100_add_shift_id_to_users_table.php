<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'shift_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('shift_id')->nullable()->after('date_of_appointment')->constrained('crm_attendance_shifts')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'shift_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shift_id');
            });
        }
    }
};
