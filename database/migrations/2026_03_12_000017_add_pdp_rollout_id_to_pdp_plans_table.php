<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pdp_plans')) {
            return;
        }

        Schema::table('pdp_plans', function (Blueprint $table): void {
            if (!Schema::hasColumn('pdp_plans', 'pdp_rollout_id')) {
                $table->foreignId('pdp_rollout_id')
                    ->nullable()
                    ->after('pdp_template_id')
                    ->constrained('pdp_rollouts')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pdp_plans')) {
            return;
        }

        Schema::table('pdp_plans', function (Blueprint $table): void {
            if (Schema::hasColumn('pdp_plans', 'pdp_rollout_id')) {
                $table->dropConstrainedForeignId('pdp_rollout_id');
            }
        });
    }
};
