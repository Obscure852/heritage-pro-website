<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        $dropColumns = ['duration_minutes', 'prerequisite_knowledge', 'differentiation'];

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            foreach ($dropColumns as $column) {
                Schema::table('lesson_plans', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        } else {
            Schema::table('lesson_plans', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->renameColumn('introduction', 'content');
        });

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->renameColumn('development', 'activities');
        });

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->renameColumn('conclusion', 'teaching_learning_aids');
        });

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->renameColumn('assessment', 'lesson_evaluation');
        });
    }

    public function down(): void {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->renameColumn('content', 'introduction');
        });

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->renameColumn('activities', 'development');
        });

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->renameColumn('teaching_learning_aids', 'conclusion');
        });

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->renameColumn('lesson_evaluation', 'assessment');
        });

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->integer('duration_minutes')->nullable()->after('period');
            $table->text('prerequisite_knowledge')->nullable()->after('learning_objectives');
            $table->text('differentiation')->nullable()->after('lesson_evaluation');
        });
    }
};
