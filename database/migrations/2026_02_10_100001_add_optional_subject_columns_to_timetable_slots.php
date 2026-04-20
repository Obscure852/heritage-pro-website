<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('timetable_slots', function (Blueprint $table) {
            // Make klass_subject_id nullable (optional subjects won't have one)
            $table->unsignedBigInteger('klass_subject_id')->nullable()->change();

            // Optional subject FK
            $table->foreignId('optional_subject_id')
                ->nullable()
                ->after('klass_subject_id')
                ->constrained('optional_subjects')
                ->nullOnDelete();

            // Coupling group key ties concurrent optional slots together
            $table->string('coupling_group_key', 50)
                ->nullable()
                ->after('block_id');

            $table->index('optional_subject_id');
            $table->index('coupling_group_key');
        });
    }

    public function down(): void {
        Schema::table('timetable_slots', function (Blueprint $table) {
            $table->dropForeign(['optional_subject_id']);
            $table->dropIndex(['coupling_group_key']);
            $table->dropColumn(['optional_subject_id', 'coupling_group_key']);

            $table->unsignedBigInteger('klass_subject_id')->nullable(false)->change();
        });
    }
};
