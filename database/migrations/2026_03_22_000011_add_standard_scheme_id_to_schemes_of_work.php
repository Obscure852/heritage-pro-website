<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds standard_scheme_id FK to schemes_of_work table.
 *
 * When set, indicates this individual teacher scheme was distributed
 * from a standard (shared) scheme. Entries become read-only.
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('schemes_of_work', function (Blueprint $table) {
            $table->unsignedBigInteger('standard_scheme_id')
                ->nullable()
                ->after('cloned_from_id')
                ->index();

            $table->foreign('standard_scheme_id')
                ->references('id')->on('standard_schemes')
                ->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::table('schemes_of_work', function (Blueprint $table) {
            $table->dropForeign(['standard_scheme_id']);
            $table->dropColumn('standard_scheme_id');
        });
    }
};
