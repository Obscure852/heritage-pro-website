<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the test_scheme_entries pivot table — links tests to scheme entries.
 *
 * Stub for Phase 6 (Assessment Alignment). Connects existing tests to the specific
 * scheme of work entries they are designed to assess, enabling curriculum coverage
 * tracking and gap analysis in the HOD dashboard.
 *
 * Requirements: FOUN-01 (Phase 6 stub)
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('test_scheme_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_id');
            $table->unsignedBigInteger('scheme_of_work_entry_id');
            $table->timestamps();

            $table->foreign('test_id')
                ->references('id')
                ->on('tests')
                ->onDelete('cascade');

            $table->foreign('scheme_of_work_entry_id')
                ->references('id')
                ->on('scheme_of_work_entries')
                ->onDelete('cascade');

            // Prevent duplicate links between the same test and entry
            $table->unique(
                ['test_id', 'scheme_of_work_entry_id'],
                'uniq_test_scheme_entry'
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('test_scheme_entries');
    }
};
