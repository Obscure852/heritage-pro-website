<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ai_test_links') && Schema::hasTable('tests')) {
            $linkedTestIds = DB::table('ai_test_links')
                ->distinct()
                ->pluck('test_id')
                ->filter()
                ->map(fn ($value) => (int) $value)
                ->values();

            if ($linkedTestIds->isNotEmpty()) {
                DB::table('tests')
                    ->whereIn('id', $linkedTestIds->all())
                    ->delete();
            }
        }

        if (Schema::hasTable('ai_test_links')) {
            Schema::drop('ai_test_links');
        }

        if (Schema::hasTable('ai_test_objectives')) {
            Schema::drop('ai_test_objectives');
        }

        if (Schema::hasTable('ai_test_messages')) {
            Schema::drop('ai_test_messages');
        }

        if (Schema::hasTable('ai_test_revisions')) {
            Schema::drop('ai_test_revisions');
        }

        if (Schema::hasTable('ai_tests')) {
            Schema::drop('ai_tests');
        }
    }

    public function down(): void
    {
        // Intentionally irreversible.
    }
};
