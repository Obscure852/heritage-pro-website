<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the standard_scheme_entry_objectives pivot table.
 *
 * Links standard scheme entries to specific syllabus objectives (many-to-many).
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('standard_scheme_entry_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_scheme_entry_id');
            $table->unsignedBigInteger('syllabus_objective_id');

            $table->foreign('standard_scheme_entry_id', 'ss_entry_obj_entry_fk')
                ->references('id')->on('standard_scheme_entries')
                ->onDelete('cascade');

            $table->foreign('syllabus_objective_id', 'ss_entry_obj_objective_fk')
                ->references('id')->on('syllabus_objectives')
                ->onDelete('cascade');

            $table->unique(
                ['standard_scheme_entry_id', 'syllabus_objective_id'],
                'uniq_ss_entry_objective'
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('standard_scheme_entry_objectives');
    }
};
