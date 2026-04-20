<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('scheme_of_work_entries', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }

    public function down(): void {
        Schema::table('scheme_of_work_entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration')->nullable()->after('learning_objectives');
        });
    }
};
