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
     * @return void
     */
    public function up()
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('student_invoice_items', function (Blueprint $table) {
                $table->unsignedBigInteger('fee_structure_id')->nullable()->change();
            });

            return;
        }

        Schema::table('student_invoice_items', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['fee_structure_id']);

            // Make the column nullable
            $table->unsignedBigInteger('fee_structure_id')->nullable()->change();

            // Re-add the foreign key with nullOnDelete
            $table->foreign('fee_structure_id')
                ->references('id')
                ->on('fee_structures')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('student_invoice_items', function (Blueprint $table) {
                $table->unsignedBigInteger('fee_structure_id')->nullable(false)->change();
            });

            return;
        }

        Schema::table('student_invoice_items', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['fee_structure_id']);

            // Make the column not nullable again
            $table->unsignedBigInteger('fee_structure_id')->nullable(false)->change();

            // Re-add the original foreign key
            $table->foreign('fee_structure_id')
                ->references('id')
                ->on('fee_structures')
                ->onDelete('cascade');
        });
    }
};
