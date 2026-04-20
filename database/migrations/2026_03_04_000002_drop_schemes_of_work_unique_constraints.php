<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop DB-level unique indexes on schemes_of_work that conflict with soft deletes.
 *
 * The indexes (klass_subject_id, term_id) and (optional_subject_id, term_id) do not
 * include deleted_at, so soft-deleted rows block re-creation for the same assignment+term.
 *
 * Uniqueness is now enforced at the app layer via StoreSchemeRequest::withValidator(),
 * which filters out soft-deleted rows.
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('schemes_of_work', function (Blueprint $table) {
            $table->dropUnique('uniq_scheme_klass_term');
            $table->dropUnique('uniq_scheme_optional_term');
        });
    }

    public function down(): void {
        Schema::table('schemes_of_work', function (Blueprint $table) {
            $table->unique(['klass_subject_id', 'term_id'], 'uniq_scheme_klass_term');
            $table->unique(['optional_subject_id', 'term_id'], 'uniq_scheme_optional_term');
        });
    }
};
