<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('student_invoice_items', 'item_type')) {
            return;
        }
        Schema::table('student_invoice_items', function (Blueprint $table) {
            $table->enum('item_type', ['fee', 'carryover'])->default('fee')->after('fee_structure_id');
            $table->unsignedSmallInteger('source_year')->nullable()->after('item_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'source_year']);
        });
    }
};
