<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('syllabi')) {
            return;
        }

        Schema::table('syllabi', function (Blueprint $table) {
            if (!Schema::hasColumn('syllabi', 'source_url')) {
                $table->string('source_url', 2048)->nullable()->after('description');
            }

            if (!Schema::hasColumn('syllabi', 'cached_structure')) {
                $table->json('cached_structure')->nullable()->after('source_url');
            }

            if (!Schema::hasColumn('syllabi', 'cached_at')) {
                $table->timestamp('cached_at')->nullable()->after('cached_structure');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('syllabi')) {
            return;
        }

        Schema::table('syllabi', function (Blueprint $table) {
            $columns = [];

            foreach (['source_url', 'cached_structure', 'cached_at'] as $column) {
                if (Schema::hasColumn('syllabi', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
