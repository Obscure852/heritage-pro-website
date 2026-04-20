<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds standard_scheme_entry_id FK to scheme_of_work_entries table.
 *
 * When set, indicates this entry was copied from a standard scheme entry
 * during distribution. The entry is read-only for the teacher.
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('scheme_of_work_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('standard_scheme_entry_id')
                ->nullable()
                ->after('syllabus_topic_id')
                ->index();

            $table->foreign('standard_scheme_entry_id', 'sow_entries_ss_entry_fk')
                ->references('id')->on('standard_scheme_entries')
                ->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::table('scheme_of_work_entries', function (Blueprint $table) {
            $table->dropForeign('sow_entries_ss_entry_fk');
            $table->dropColumn('standard_scheme_entry_id');
        });
    }
};
