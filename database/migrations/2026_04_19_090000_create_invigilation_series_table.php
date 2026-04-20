<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invigilation_series', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20)->default('custom');
            $table->unsignedBigInteger('term_id');
            $table->string('status', 20)->default('draft');
            $table->string('eligibility_policy', 40)->default('any_teacher');
            $table->string('timetable_conflict_policy', 20)->default('ignore');
            $table->string('balancing_policy', 20)->default('balanced');
            $table->unsignedTinyInteger('default_required_invigilators')->default(1);
            $table->text('notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('term_id')->references('id')->on('terms')->cascadeOnDelete();
            $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index('term_id');
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invigilation_series');
    }
};
