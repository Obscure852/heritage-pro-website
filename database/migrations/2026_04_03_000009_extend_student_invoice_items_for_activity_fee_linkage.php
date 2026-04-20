<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_invoice_items', function (Blueprint $table): void {
            if (!Schema::hasColumn('student_invoice_items', 'activity_fee_charge_id')) {
                $table->unsignedBigInteger('activity_fee_charge_id')->nullable()->after('student_invoice_id');
            }
        });

        if (DB::getDriverName() !== 'sqlite' && Schema::hasColumn('student_invoice_items', 'item_type')) {
            DB::statement(
                "ALTER TABLE student_invoice_items MODIFY COLUMN item_type ENUM('fee', 'carryover', 'adjustment', 'credit_note', 'late_fee', 'activity_fee') NOT NULL DEFAULT 'fee'"
            );
        }

        Schema::table('student_invoice_items', function (Blueprint $table): void {
            if (!$this->hasIndex('student_invoice_items', 'student_invoice_items_activity_fee_charge_id_unique')) {
                $table->unique('activity_fee_charge_id', 'student_invoice_items_activity_fee_charge_id_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_invoice_items', function (Blueprint $table): void {
            if ($this->hasIndex('student_invoice_items', 'student_invoice_items_activity_fee_charge_id_unique')) {
                $table->dropUnique('student_invoice_items_activity_fee_charge_id_unique');
            }

            if (Schema::hasColumn('student_invoice_items', 'activity_fee_charge_id')) {
                $table->dropColumn('activity_fee_charge_id');
            }
        });

        if (DB::getDriverName() !== 'sqlite' && Schema::hasColumn('student_invoice_items', 'item_type')) {
            DB::statement(
                "ALTER TABLE student_invoice_items MODIFY COLUMN item_type ENUM('fee', 'carryover', 'adjustment', 'credit_note', 'late_fee') NOT NULL DEFAULT 'fee'"
            );
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($index): bool => ($index->name ?? null) === $indexName);
        }

        $database = config('database.connections.mysql.database');
        $result = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$database, $table, $indexName]
        );

        return ($result[0]->cnt ?? 0) > 0;
    }
};
