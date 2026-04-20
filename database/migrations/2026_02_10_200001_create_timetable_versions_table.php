<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timetable_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('snapshot_data');
            $table->unsignedInteger('slot_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('published_at');
            $table->foreignId('published_by')->constrained('users');
            $table->timestamps();
            $table->unique(['timetable_id', 'version_number'], 'tt_versions_unique');
            $table->index('timetable_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('timetable_versions');
    }
};
