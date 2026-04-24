<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (! Schema::hasColumn('leads', 'location')) {
                    $table->string('location')->nullable()->after('country');
                }

                if (! Schema::hasColumn('leads', 'region')) {
                    $table->string('region')->nullable()->after('location');
                }
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (! Schema::hasColumn('customers', 'fax')) {
                    $table->string('fax')->nullable()->after('phone');
                }

                if (! Schema::hasColumn('customers', 'location')) {
                    $table->string('location')->nullable()->after('country');
                }

                if (! Schema::hasColumn('customers', 'region')) {
                    $table->string('region')->nullable()->after('location');
                }
            });
        }

        if (Schema::hasTable('crm_import_entity_locks')) {
            DB::table('crm_import_entity_locks')->updateOrInsert(
                ['entity' => 'customers'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_import_entity_locks')) {
            DB::table('crm_import_entity_locks')->where('entity', 'customers')->delete();
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                foreach (['region', 'location', 'fax'] as $column) {
                    if (Schema::hasColumn('customers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                foreach (['region', 'location'] as $column) {
                    if (Schema::hasColumn('leads', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
