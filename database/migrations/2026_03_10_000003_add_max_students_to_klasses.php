<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('klasses', function (Blueprint $table) {
            $table->unsignedInteger('max_students')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('klasses', function (Blueprint $table) {
            $table->dropColumn('max_students');
        });
    }
};
