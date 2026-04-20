<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        // Add author_type to threads for polymorphic relationship
        Schema::table('lms_discussion_threads', function (Blueprint $table) {
            $table->string('author_type')->default('App\\Models\\Student')->after('author_id');
        });

        // Add author_type to posts for polymorphic relationship
        Schema::table('lms_discussion_posts', function (Blueprint $table) {
            $table->string('author_type')->default('App\\Models\\Student')->after('author_id');
        });

        // Update existing records to have correct author_type
        DB::table('lms_discussion_threads')
            ->whereNull('author_type')
            ->orWhere('author_type', '')
            ->update(['author_type' => 'App\\Models\\Student']);

        DB::table('lms_discussion_posts')
            ->whereNull('author_type')
            ->orWhere('author_type', '')
            ->update(['author_type' => 'App\\Models\\Student']);

        if (!$isSqlite) {
            Schema::table('lms_discussion_threads', function (Blueprint $table) {
                $table->dropForeign(['author_id']);
            });

            Schema::table('lms_discussion_posts', function (Blueprint $table) {
                $table->dropForeign(['author_id']);
            });
        }

        // Add indexes for polymorphic relationships
        Schema::table('lms_discussion_threads', function (Blueprint $table) {
            $table->index(['author_type', 'author_id'], 'threads_author_morph_index');
        });

        Schema::table('lms_discussion_posts', function (Blueprint $table) {
            $table->index(['author_type', 'author_id'], 'posts_author_morph_index');
        });
    }

    public function down(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        // Remove polymorphic indexes
        Schema::table('lms_discussion_threads', function (Blueprint $table) {
            $table->dropIndex('threads_author_morph_index');
        });

        Schema::table('lms_discussion_posts', function (Blueprint $table) {
            $table->dropIndex('posts_author_morph_index');
        });

        if (!$isSqlite) {
            Schema::table('lms_discussion_threads', function (Blueprint $table) {
                $table->foreign('author_id')->references('id')->on('students')->cascadeOnDelete();
                $table->dropColumn('author_type');
            });

            Schema::table('lms_discussion_posts', function (Blueprint $table) {
                $table->foreign('author_id')->references('id')->on('students')->cascadeOnDelete();
                $table->dropColumn('author_type');
            });

            return;
        }

        Schema::table('lms_discussion_threads', function (Blueprint $table) {
            $table->dropColumn('author_type');
        });

        Schema::table('lms_discussion_posts', function (Blueprint $table) {
            $table->dropColumn('author_type');
        });
    }
};
