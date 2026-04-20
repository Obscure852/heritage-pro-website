<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('is_double')->nullable()->after('department');
        });

        DB::table('subjects')
            ->whereRaw('LOWER(name) = ?', ['double science'])
            ->update(['is_double' => true]);
    }

    public function down(): void {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('is_double');
        });
    }
};
