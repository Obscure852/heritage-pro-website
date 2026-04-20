<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student clearances track fee clearance status per year.
 * A student is "cleared" for a year if their annual balance is zero
 * or if an override has been granted.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_clearances')) {
            return;
        }
        Schema::create('student_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->boolean('override_granted')->default(false);
            $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('granted_at')->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // One clearance record per student per year
            $table->unique(['student_id', 'year']);
            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_clearances');
    }
};
