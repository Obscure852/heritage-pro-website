<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * TRANSITIONAL MIGRATION
 *
 * This migration converts existing term-based fee tables to year-based.
 * Uses raw SQL for proper ordering of FK/index drops.
 *
 * MySQL uses indexes to support foreign keys. When dropping a composite unique index
 * that starts with a column that has a FK, we must: drop FK -> drop index -> add
 * simple index -> recreate FK.
 *
 * REMOVE THIS FILE after all environments are using the updated base migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ===========================================
        // 1. FEE_STRUCTURES
        // ===========================================
        if (Schema::hasColumn('fee_structures', 'term_id')) {
            // The unique index (fee_type_id, grade_id, term_id) is used by fee_type_id FK
            $this->dropForeignKeyIfExists('fee_structures', 'fee_structures_fee_type_id_foreign');
            $this->dropForeignKeyIfExists('fee_structures', 'fee_structures_term_id_foreign');
            $this->dropIndexIfExists('fee_structures', 'fee_structures_fee_type_id_grade_id_term_id_unique');
            $this->dropIndexIfExists('fee_structures', 'fee_structures_term_id_index');

            // Add simple index for FK before dropping column
            $this->addIndexIfMissing('fee_structures', 'fee_structures_fee_type_id', 'fee_structures_fee_type_id_index');

            Schema::table('fee_structures', function (Blueprint $table) {
                $table->dropColumn('term_id');
            });

            // Recreate FK
            $this->addForeignKeyIfMissing(
                'fee_structures',
                'fee_type_id',
                'fee_types',
                'fee_structures_fee_type_id_foreign'
            );
        }

        if (!$this->indexExists('fee_structures', 'fee_structures_fee_type_id_grade_id_year_unique')) {
            Schema::table('fee_structures', function (Blueprint $table) {
                $table->unique(['fee_type_id', 'grade_id', 'year']);
            });
        }

        // ===========================================
        // 2. STUDENT_INVOICES (check if already done)
        // ===========================================
        if (Schema::hasColumn('student_invoices', 'term_id')) {
            // The unique index (student_id, term_id) is used by student_id FK
            $this->dropForeignKeyIfExists('student_invoices', 'student_invoices_student_id_foreign');
            $this->dropForeignKeyIfExists('student_invoices', 'student_invoices_term_id_foreign');
            $this->dropIndexIfExists('student_invoices', 'student_invoices_student_id_term_id_unique');
            $this->dropIndexIfExists('student_invoices', 'student_invoices_term_id_index');

            // Add simple index for FK before dropping column
            $this->addIndexIfMissing('student_invoices', 'student_id', 'student_invoices_student_id_index');

            Schema::table('student_invoices', function (Blueprint $table) {
                $table->dropColumn('term_id');
            });

            // Recreate FK
            $this->addForeignKeyIfMissing(
                'student_invoices',
                'student_id',
                'students',
                'student_invoices_student_id_foreign'
            );
        }

        if (!$this->indexExists('student_invoices', 'student_invoices_student_id_year_unique')) {
            Schema::table('student_invoices', function (Blueprint $table) {
                $table->unique(['student_id', 'year']);
            });
        }

        // ===========================================
        // 3. STUDENT_DISCOUNTS (check if already done)
        // ===========================================
        if (Schema::hasColumn('student_discounts', 'term_id')) {
            // The unique index (student_id, discount_type_id, term_id) is used by student_id FK
            $this->dropForeignKeyIfExists('student_discounts', 'student_discounts_student_id_foreign');
            $this->dropForeignKeyIfExists('student_discounts', 'student_discounts_term_id_foreign');
            $this->dropIndexIfExists('student_discounts', 'student_discounts_student_id_discount_type_id_term_id_unique');
            $this->dropIndexIfExists('student_discounts', 'student_discounts_term_id_index');

            $this->addIndexIfMissing('student_discounts', 'student_id', 'student_discounts_student_id_index');

            Schema::table('student_discounts', function (Blueprint $table) {
                $table->dropColumn('term_id');
            });

            $this->addForeignKeyIfMissing(
                'student_discounts',
                'student_id',
                'students',
                'student_discounts_student_id_foreign'
            );
        }

        if (!$this->indexExists('student_discounts', 'student_discounts_student_id_discount_type_id_year_unique')) {
            Schema::table('student_discounts', function (Blueprint $table) {
                $table->unique(['student_id', 'discount_type_id', 'year']);
            });
        }

        // ===========================================
        // 4. STUDENT_CLEARANCES
        // ===========================================
        if (Schema::hasColumn('student_clearances', 'term_id')) {
            // The unique index (student_id, term_id) is used by student_id FK
            $this->dropForeignKeyIfExists('student_clearances', 'student_clearances_student_id_foreign');
            $this->dropForeignKeyIfExists('student_clearances', 'student_clearances_term_id_foreign');
            $this->dropIndexIfExists('student_clearances', 'student_clearances_student_id_term_id_unique');
            $this->dropIndexIfExists('student_clearances', 'student_clearances_term_id_index');

            // Add simple index for FK
            $this->addIndexIfMissing('student_clearances', 'student_id', 'student_clearances_student_id_index');

            // Add year column
            if (!Schema::hasColumn('student_clearances', 'year')) {
                Schema::table('student_clearances', function (Blueprint $table) {
                    $table->year('year')->after('student_id');
                });
            }

            Schema::table('student_clearances', function (Blueprint $table) {
                $table->dropColumn('term_id');
            });

            // Recreate FK
            $this->addForeignKeyIfMissing(
                'student_clearances',
                'student_id',
                'students',
                'student_clearances_student_id_foreign'
            );
        }

        if (!Schema::hasColumn('student_clearances', 'year')) {
            Schema::table('student_clearances', function (Blueprint $table) {
                $table->year('year')->after('student_id');
            });
        }

        if (!$this->indexExists('student_clearances', 'student_clearances_student_id_year_unique')) {
            Schema::table('student_clearances', function (Blueprint $table) {
                $table->unique(['student_id', 'year']);
            });
        }

        if (!$this->indexExists('student_clearances', 'student_clearances_year_index')) {
            Schema::table('student_clearances', function (Blueprint $table) {
                $table->index('year');
            });
        }

        // ===========================================
        // 5. FEE_BALANCE_CARRYOVERS
        // ===========================================
        if (Schema::hasColumn('fee_balance_carryovers', 'from_term_id')) {
            // Drop FKs on term columns
            $this->dropForeignKeyIfExists('fee_balance_carryovers', 'fee_balance_carryovers_from_term_id_foreign');
            $this->dropForeignKeyIfExists('fee_balance_carryovers', 'fee_balance_carryovers_to_term_id_foreign');
            $this->dropIndexIfExists('fee_balance_carryovers', 'fee_balance_carryovers_from_term_id_to_term_id_index');
            $this->dropIndexIfExists('fee_balance_carryovers', 'fee_balance_carryovers_from_term_id_index');
            $this->dropIndexIfExists('fee_balance_carryovers', 'fee_balance_carryovers_to_term_id_index');

            if (!Schema::hasColumn('fee_balance_carryovers', 'from_year')) {
                Schema::table('fee_balance_carryovers', function (Blueprint $table) {
                    $table->year('from_year')->after('student_id');
                });
            }

            if (!Schema::hasColumn('fee_balance_carryovers', 'to_year')) {
                Schema::table('fee_balance_carryovers', function (Blueprint $table) {
                    $table->year('to_year')->after('from_year');
                });
            }

            if (DB::getDriverName() === 'sqlite') {
                Schema::table('fee_balance_carryovers', function (Blueprint $table) {
                    $table->dropColumn('from_term_id');
                });
                Schema::table('fee_balance_carryovers', function (Blueprint $table) {
                    $table->dropColumn('to_term_id');
                });
            } else {
                Schema::table('fee_balance_carryovers', function (Blueprint $table) {
                    $table->dropColumn(['from_term_id', 'to_term_id']);
                });
            }
        }

        if (!Schema::hasColumn('fee_balance_carryovers', 'from_year')) {
            Schema::table('fee_balance_carryovers', function (Blueprint $table) {
                $table->year('from_year')->after('student_id');
            });
        }

        if (!Schema::hasColumn('fee_balance_carryovers', 'to_year')) {
            Schema::table('fee_balance_carryovers', function (Blueprint $table) {
                $table->year('to_year')->after('from_year');
            });
        }

        if (!$this->indexExists('fee_balance_carryovers', 'fee_balance_carryovers_from_year_to_year_index')) {
            Schema::table('fee_balance_carryovers', function (Blueprint $table) {
                $table->index(['from_year', 'to_year']);
            });
        }

        if (!$this->indexExists('fee_balance_carryovers', 'fee_balance_carryovers_student_id_from_year_to_year_unique')) {
            Schema::table('fee_balance_carryovers', function (Blueprint $table) {
                $table->unique(['student_id', 'from_year', 'to_year']);
            });
        }

        // ===========================================
        // 6. FEE_PAYMENTS
        // ===========================================
        if (Schema::hasColumn('fee_payments', 'term_id')) {
            // Drop FK on term_id
            $this->dropForeignKeyIfExists('fee_payments', 'fee_payments_term_id_foreign');
            $this->dropIndexIfExists('fee_payments', 'fee_payments_term_id_index');

            if (!Schema::hasColumn('fee_payments', 'year')) {
                Schema::table('fee_payments', function (Blueprint $table) {
                    $table->year('year')->after('student_id');
                });
            }

            Schema::table('fee_payments', function (Blueprint $table) {
                $table->dropColumn('term_id');
            });
        }

        if (!Schema::hasColumn('fee_payments', 'year')) {
            Schema::table('fee_payments', function (Blueprint $table) {
                $table->year('year')->after('student_id');
            });
        }

        if (!$this->indexExists('fee_payments', 'fee_payments_year_index')) {
            Schema::table('fee_payments', function (Blueprint $table) {
                $table->index('year');
            });
        }
    }

    /**
     * Drop a foreign key if it exists.
     */
    private function dropForeignKeyIfExists(string $table, string $constraintName): void
    {
        if (DB::getDriverName() === 'sqlite') {
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($constraintName) {
                    $blueprint->dropForeign($constraintName);
                });
            } catch (\Throwable) {
                // SQLite does not reliably expose FK names; ignore when absent.
            }

            return;
        }

        if ($this->foreignKeyExists($table, $constraintName)) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
        }
    }

    /**
     * Check if a foreign key exists on a table.
     */
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return false;
        }

        $database = config('database.connections.mysql.database');

        $result = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = ?
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$database, $table, $constraintName]);

        return $result[0]->cnt > 0;
    }

    /**
     * Drop an index if it exists.
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            if (DB::getDriverName() === 'sqlite') {
                Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                    $blueprint->dropIndex($indexName);
                });
            } else {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            }
        }
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(function ($index) use ($indexName): bool {
                return ($index->name ?? null) === $indexName;
            });
        }

        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    private function addIndexIfMissing(string $table, string|array $columns, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    private function addForeignKeyIfMissing(
        string $table,
        string $column,
        string $referencesTable,
        string $constraintName,
        string $referencesColumn = 'id',
        string $onDelete = 'cascade'
    ): void {
        if (DB::getDriverName() === 'sqlite' && $this->sqliteForeignKeyExists($table, $column, $referencesTable, $referencesColumn)) {
            return;
        }

        if (DB::getDriverName() !== 'sqlite' && $this->foreignKeyExists($table, $constraintName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use (
            $column,
            $referencesTable,
            $constraintName,
            $referencesColumn,
            $onDelete
        ) {
            $foreign = $blueprint->foreign($column, $constraintName)
                ->references($referencesColumn)
                ->on($referencesTable);

            if ($onDelete !== '') {
                $foreign->onDelete($onDelete);
            }
        });
    }

    private function sqliteForeignKeyExists(
        string $table,
        string $column,
        string $referencesTable,
        string $referencesColumn
    ): bool {
        $foreignKeys = DB::select("PRAGMA foreign_key_list('{$table}')");

        return collect($foreignKeys)->contains(function ($foreignKey) use ($column, $referencesTable, $referencesColumn): bool {
            return ($foreignKey->from ?? null) === $column
                && ($foreignKey->table ?? null) === $referencesTable
                && ($foreignKey->to ?? null) === $referencesColumn;
        });
    }

    public function down(): void
    {
        throw new \Exception(
            'This transitional migration cannot be rolled back. ' .
            'Restore from backup if needed.'
        );
    }
};
