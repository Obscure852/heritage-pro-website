<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schemes_of_work', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('status');
            $table->timestamp('published_at')->nullable()->after('is_published');
            $table->unsignedBigInteger('published_by')->nullable()->after('published_at');

            $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['term_id', 'is_published'], 'schemes_term_published_idx');
        });
    }

    public function down(): void
    {
        Schema::table('schemes_of_work', function (Blueprint $table) {
            $table->dropIndex('schemes_term_published_idx');
            $table->dropForeign(['published_by']);
            $table->dropColumn(['is_published', 'published_at', 'published_by']);
        });
    }
};
