<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('scheme_of_work_entries', function (Blueprint $table) {
            $table->dropColumn([
                'teaching_activities',
                'learning_activities',
                'resources',
                'assessment_methods',
                'homework',
                'references_text',
                'remarks',
            ]);
        });

        Schema::table('scheme_of_work_entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration')->nullable()->after('learning_objectives');
        });
    }

    public function down(): void {
        Schema::table('scheme_of_work_entries', function (Blueprint $table) {
            $table->dropColumn('duration');
        });

        Schema::table('scheme_of_work_entries', function (Blueprint $table) {
            $table->text('teaching_activities')->nullable()->after('learning_objectives');
            $table->text('learning_activities')->nullable()->after('teaching_activities');
            $table->text('resources')->nullable()->after('learning_activities');
            $table->text('assessment_methods')->nullable()->after('resources');
            $table->text('homework')->nullable()->after('assessment_methods');
            $table->text('references_text')->nullable()->after('homework');
            $table->text('remarks')->nullable()->after('references_text');
        });
    }
};
