<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activity_staff_assignments')) {
            Schema::create('activity_staff_assignments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role', 50);
                $table->boolean('is_primary')->default(false);
                $table->boolean('active')->default(true);
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('removed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('activity_id')->references('id')->on('activities')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
                $table->index(['activity_id', 'active']);
                $table->index(['user_id', 'active']);
            });
        }

        if (!Schema::hasTable('activity_eligibility_targets')) {
            Schema::create('activity_eligibility_targets', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('target_type', 50);
                $table->unsignedBigInteger('target_id');
                $table->timestamps();

                $table->foreign('activity_id')->references('id')->on('activities')->cascadeOnDelete();
                $table->unique(['activity_id', 'target_type', 'target_id'], 'activity_eligibility_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_eligibility_targets');
        Schema::dropIfExists('activity_staff_assignments');
    }
};
