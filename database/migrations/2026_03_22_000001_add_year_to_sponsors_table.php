<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('sponsors', 'year')) {
            Schema::table('sponsors', function (Blueprint $table) {
                $table->year('year')->nullable()->after('telephone');
            });
        }

        DB::table('sponsors')
            ->whereNull('year')
            ->update(['year' => (int) date('Y')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('sponsors', 'year')) {
            Schema::table('sponsors', function (Blueprint $table) {
                $table->dropColumn('year');
            });
        }
    }
};
