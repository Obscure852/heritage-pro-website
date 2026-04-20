<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the standard_scheme_workflow_audits table.
 *
 * Immutable audit log for standard scheme workflow transitions.
 * Actions: submitted, placed_under_review, approved, revision_required,
 *          published, unpublished, distributed
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('standard_scheme_workflow_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_scheme_id');
            $table->unsignedBigInteger('actor_id');
            $table->string('action', 50);
            $table->string('from_status', 30);
            $table->string('to_status', 30);
            $table->text('comments')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('standard_scheme_id')
                ->references('id')->on('standard_schemes')
                ->onDelete('cascade');

            $table->foreign('actor_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->index('standard_scheme_id');
            $table->index('actor_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('standard_scheme_workflow_audits');
    }
};
