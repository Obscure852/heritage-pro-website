<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('leads') && ! Schema::hasColumn('leads', 'postal_address')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('postal_address')->nullable()->after('location');
            });
        }

        if (Schema::hasTable('customers') && ! Schema::hasColumn('customers', 'postal_address')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('postal_address')->nullable()->after('location');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'postal_address')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('postal_address');
            });
        }

        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'postal_address')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('postal_address');
            });
        }
    }
};
