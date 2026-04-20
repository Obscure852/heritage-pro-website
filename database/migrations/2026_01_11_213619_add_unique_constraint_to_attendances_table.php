<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, remove any duplicate records (keep the most recent one)
        // This is necessary before adding a unique constraint
        DB::statement("
            DELETE FROM attendances
            WHERE id IN (
                SELECT duplicate_id FROM (
                    SELECT a1.id AS duplicate_id
                    FROM attendances a1
                    INNER JOIN attendances a2
                        ON a1.id < a2.id
                        AND a1.student_id = a2.student_id
                        AND a1.klass_id = a2.klass_id
                        AND a1.term_id = a2.term_id
                        AND a1.date = a2.date
                ) duplicates
            )
        ");

        // Remove records with null dates (data integrity issue)
        DB::statement("DELETE FROM attendances WHERE date IS NULL");

        Schema::table('attendances', function (Blueprint $table) {
            // Make date non-nullable
            $table->date('date')->nullable(false)->change();

            // Add unique constraint to prevent duplicate attendance records
            $table->unique(
                ['student_id', 'klass_id', 'term_id', 'date'],
                'attendance_unique_record'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_unique_record');
            $table->date('date')->nullable()->change();
        });
    }
};
