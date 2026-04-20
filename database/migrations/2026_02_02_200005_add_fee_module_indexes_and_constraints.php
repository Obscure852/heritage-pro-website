<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add indexes and constraints to improve fee module query performance
     * and data integrity.
     */
    public function up(): void
    {
        // Add index to fee_payments for faster invoice lookups
        Schema::table('fee_payments', function (Blueprint $table) {
            if (!$this->hasIndex('fee_payments', 'fee_payments_student_invoice_id_index')) {
                $table->index('student_invoice_id');
            }
        });

        // Add indexes to fee_refunds for faster lookups
        Schema::table('fee_refunds', function (Blueprint $table) {
            if (!$this->hasIndex('fee_refunds', 'fee_refunds_fee_payment_id_index')) {
                $table->index('fee_payment_id');
            }
            if (!$this->hasIndex('fee_refunds', 'fee_refunds_student_invoice_id_index')) {
                $table->index('student_invoice_id');
            }
        });

        // Add index to student_invoice_items for fee structure lookups
        Schema::table('student_invoice_items', function (Blueprint $table) {
            if (!$this->hasIndex('student_invoice_items', 'student_invoice_items_fee_structure_id_index')) {
                $table->index('fee_structure_id');
            }
        });

        // Add index to late_fee_charges for waived status filtering
        Schema::table('late_fee_charges', function (Blueprint $table) {
            if (!$this->hasIndex('late_fee_charges', 'late_fee_charges_waived_index')) {
                $table->index('waived');
            }
        });

        // Add index to student_discounts for discount type lookups
        Schema::table('student_discounts', function (Blueprint $table) {
            if (!$this->hasIndex('student_discounts', 'student_discounts_discount_type_id_index')) {
                $table->index('discount_type_id');
            }
        });

        // Add unique constraint to prevent duplicate carryovers
        Schema::table('fee_balance_carryovers', function (Blueprint $table) {
            if (!$this->hasIndex('fee_balance_carryovers', 'unique_carryover')) {
                $table->unique(['student_id', 'from_year', 'to_year'], 'unique_carryover');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropIndex(['student_invoice_id']);
        });

        Schema::table('fee_refunds', function (Blueprint $table) {
            $table->dropIndex(['fee_payment_id']);
            $table->dropIndex(['student_invoice_id']);
        });

        Schema::table('student_invoice_items', function (Blueprint $table) {
            $table->dropIndex(['fee_structure_id']);
        });

        Schema::table('late_fee_charges', function (Blueprint $table) {
            $table->dropIndex(['waived']);
        });

        Schema::table('student_discounts', function (Blueprint $table) {
            $table->dropIndex(['discount_type_id']);
        });

        Schema::table('fee_balance_carryovers', function (Blueprint $table) {
            $table->dropUnique('unique_carryover');
        });
    }

    /**
     * Check if an index exists on a table (Laravel 9 compatible).
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(function ($index) use ($indexName): bool {
                return ($index->name ?? null) === $indexName;
            });
        }

        $database = config('database.connections.mysql.database');
        $result = \DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$database, $table, $indexName]
        );
        return $result[0]->cnt > 0;
    }
};
