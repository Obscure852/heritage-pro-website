<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::table('klass_subject', function (Blueprint $table) {
            $table->unsignedBigInteger('assistant_user_id')->nullable()->after('user_id');
            $table->foreign('assistant_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('optional_subjects', function (Blueprint $table) {
            $table->unsignedBigInteger('assistant_user_id')->nullable()->after('user_id');
            $table->foreign('assistant_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down() {
        Schema::table('klass_subject', function (Blueprint $table) {
            $table->dropForeign(['assistant_user_id']);
            $table->dropColumn('assistant_user_id');
        });

        Schema::table('optional_subjects', function (Blueprint $table) {
            $table->dropForeign(['assistant_user_id']);
            $table->dropColumn('assistant_user_id');
        });
    }
};
