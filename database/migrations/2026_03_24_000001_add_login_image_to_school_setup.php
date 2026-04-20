<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_setup', function (Blueprint $table) {
            $table->string('login_image_path')->nullable()->after('letterhead_path');
            $table->boolean('use_custom_login_image')->default(false)->after('login_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('school_setup', function (Blueprint $table) {
            $table->dropColumn(['login_image_path', 'use_custom_login_image']);
        });
    }
};
