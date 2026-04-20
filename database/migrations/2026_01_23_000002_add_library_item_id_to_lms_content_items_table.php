<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('lms_content_items', function (Blueprint $table) {
            $table->foreignId('library_item_id')
                ->nullable()
                ->after('contentable_type')
                ->constrained('lms_library_items')
                ->nullOnDelete();
            $table->index('library_item_id', 'lms_content_items_library_item_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('lms_content_items', function (Blueprint $table) {
            $table->dropForeign(['library_item_id']);
            $table->dropIndex('lms_content_items_library_item_id_index');
            $table->dropColumn('library_item_id');
        });
    }
};
