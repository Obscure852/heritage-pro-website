<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intervention_plan_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_plan_id')->constrained('intervention_plans')->cascadeOnDelete();

            // Term context
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            $table->date('review_date');
            $table->foreignId('reviewed_by')->constrained('users')->restrictOnDelete();

            // Progress assessment
            $table->enum('overall_progress', [
                'significant',
                'moderate',
                'minimal',
                'none',
                'regression'
            ]);
            $table->json('goals_progress')->nullable()->comment('Progress on individual goals');

            // Notes
            $table->text('observations')->nullable();
            $table->text('successes')->nullable();
            $table->text('challenges')->nullable();

            // Adjustments
            $table->text('adjustments_made')->nullable();
            $table->text('new_strategies')->nullable();

            // Recommendation
            $table->enum('recommendation', [
                'continue',
                'modify',
                'extend',
                'conclude',
                'escalate'
            ]);
            $table->date('next_review_date')->nullable();

            // Attendees
            $table->json('attendees')->nullable()->comment('Array of staff user IDs');
            $table->boolean('parent_attended')->default(false);
            $table->boolean('student_attended')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['intervention_plan_id', 'term_id'], 'ipr_plan_term_idx');
            $table->index('review_date');
            $table->index('overall_progress');
            $table->index('recommendation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervention_plan_reviews');
    }
};
