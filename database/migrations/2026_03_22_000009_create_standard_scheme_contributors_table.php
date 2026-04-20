<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the standard_scheme_contributors table.
 *
 * Tracks which teachers are part of the subject panel for a standard scheme.
 * Roles: 'lead', 'contributor', 'viewer'
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('standard_scheme_contributors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('standard_scheme_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role', 20)->default('viewer');
            $table->timestamps();

            $table->foreign('standard_scheme_id')
                ->references('id')->on('standard_schemes')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->unique(
                ['standard_scheme_id', 'user_id'],
                'uniq_ss_contributor'
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('standard_scheme_contributors');
    }
};
