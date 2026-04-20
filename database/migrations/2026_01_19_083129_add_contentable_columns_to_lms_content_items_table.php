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
        Schema::table('lms_content_items', function (Blueprint $table) {
            // Polymorphic relationship columns for content types (Video, Document, Quiz, etc.)
            $table->nullableMorphs('contentable');

            // Additional columns used by ContentController
            $table->string('type')->nullable()->after('content_type');
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('estimated_duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lms_content_items', function (Blueprint $table) {
            $table->dropMorphs('contentable');
            $table->dropColumn([
                'type',
                'content',
                'file_path',
                'external_url',
                'is_required',
                'estimated_duration',
            ]);
        });
    }
};
