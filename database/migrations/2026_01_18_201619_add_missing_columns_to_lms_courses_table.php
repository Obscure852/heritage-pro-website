<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            // Add missing columns
            $table->string('code', 50)->unique()->after('id');
            $table->json('learning_objectives')->nullable()->after('description');
            $table->text('prerequisites_text')->nullable()->after('learning_objectives');
            $table->integer('max_students')->nullable()->after('end_date');
            $table->string('enrollment_key', 50)->nullable()->after('allow_self_enrollment');
            $table->foreignId('created_by')->nullable()->after('adaptive_learning_enabled')->constrained('users')->onDelete('set null');
            $table->timestamp('published_at')->nullable()->after('created_by');
        });

        Schema::table('lms_courses', function (Blueprint $table) {
            $table->renameColumn('allow_self_enrollment', 'self_enrollment');
        });

        Schema::table('lms_courses', function (Blueprint $table) {
            $table->renameColumn('passing_score', 'passing_grade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->renameColumn('self_enrollment', 'allow_self_enrollment');
        });

        Schema::table('lms_courses', function (Blueprint $table) {
            $table->renameColumn('passing_grade', 'passing_score');
        });

        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        $columns = [
            'code',
            'learning_objectives',
            'prerequisites_text',
            'max_students',
            'enrollment_key',
            'created_by',
            'published_at',
        ];

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            foreach ($columns as $column) {
                Schema::table('lms_courses', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }

            return;
        }

        Schema::table('lms_courses', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
