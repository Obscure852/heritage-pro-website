<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('leads') || Schema::hasColumn('leads', 'fax')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->string('fax')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('leads') || ! Schema::hasColumn('leads', 'fax')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('fax');
        });
    }
};
