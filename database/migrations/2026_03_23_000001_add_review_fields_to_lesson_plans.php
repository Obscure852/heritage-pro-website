<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds supervisor and HOD review fields to the lesson_plans table.
 * Updates status workflow: planned → draft, adds new review statuses.
 *
 * New status values: draft, submitted, supervisor_reviewed, revision_required, approved, taught
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('lesson_plans', function (Blueprint $table) {
            // Supervisor review fields
            $table->unsignedBigInteger('supervisor_reviewed_by')->nullable()->after('reflection_notes');
            $table->timestamp('supervisor_reviewed_at')->nullable()->after('supervisor_reviewed_by');
            $table->text('supervisor_comments')->nullable()->after('supervisor_reviewed_at');

            // HOD review fields
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('supervisor_comments');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_comments')->nullable()->after('reviewed_at');

            // Foreign keys
            $table->foreign('supervisor_reviewed_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('reviewed_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });

        // Migrate existing 'planned' status to 'draft'
        DB::table('lesson_plans')
            ->where('status', 'planned')
            ->update(['status' => 'draft']);
    }

    public function down(): void {
        // Revert 'draft' back to 'planned'
        DB::table('lesson_plans')
            ->where('status', 'draft')
            ->update(['status' => 'planned']);

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->dropForeign(['supervisor_reviewed_by']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'supervisor_reviewed_by',
                'supervisor_reviewed_at',
                'supervisor_comments',
                'reviewed_by',
                'reviewed_at',
                'review_comments',
            ]);
        });
    }
};
