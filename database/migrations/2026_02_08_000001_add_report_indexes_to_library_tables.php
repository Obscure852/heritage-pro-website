<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('library_transactions', function (Blueprint $table) {
            $table->index('checkout_date');
            $table->index('return_date');
        });
        Schema::table('library_fines', function (Blueprint $table) {
            $table->index('fine_date');
        });
    }

    public function down(): void {
        Schema::table('library_transactions', function (Blueprint $table) {
            $table->dropIndex(['checkout_date']);
            $table->dropIndex(['return_date']);
        });
        Schema::table('library_fines', function (Blueprint $table) {
            $table->dropIndex(['fine_date']);
        });
    }
};
