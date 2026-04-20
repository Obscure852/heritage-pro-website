<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Change column type first, then convert data
        Schema::table('klasses', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
        });

        DB::table('klasses')->where('type', '1')->update(['type' => 'Triple Award']);
        DB::table('klasses')->where('type', '0')->update(['type' => 'Double Award']);
    }

    public function down(): void {
        DB::table('klasses')->where('type', 'Triple Award')->update(['type' => '1']);
        DB::table('klasses')->where('type', 'Double Award')->update(['type' => '0']);
        DB::table('klasses')->where('type', 'Single Award')->update(['type' => null]);

        Schema::table('klasses', function (Blueprint $table) {
            $table->boolean('type')->nullable()->change();
        });
    }
};
