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
        Schema::create('welfare_case_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_case_id')->constrained('welfare_cases')->cascadeOnDelete();

            // Term context
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            $table->dateTime('note_date');
            $table->enum('note_type', [
                'update',
                'observation',
                'action',
                'follow_up',
                'escalation',
                'closure'
            ]);

            $table->text('content');
            $table->boolean('is_confidential')->default(false);

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['welfare_case_id', 'term_id'], 'wcn_case_term_idx');
            $table->index('note_date');
            $table->index('note_type');
            $table->index('is_confidential');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_case_notes');
    }
};
