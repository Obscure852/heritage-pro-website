<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('connect_id')->nullable()->after('sponsor_id');
            $table->index('connect_id');
        });

        $this->dropSponsorForeignKey();

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE admissions MODIFY sponsor_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('admissions', function (Blueprint $table) {
                $table->unsignedBigInteger('sponsor_id')->nullable()->change();
            });
        }

        Schema::table('admissions', function (Blueprint $table) {
            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $this->dropSponsorForeignKey();

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE admissions MODIFY sponsor_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('admissions', function (Blueprint $table) {
                $table->unsignedBigInteger('sponsor_id')->nullable(false)->change();
            });
        }

        Schema::table('admissions', function (Blueprint $table) {
            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
            $table->dropIndex(['connect_id']);
            $table->dropColumn('connect_id');
        });
    }

    private function dropSponsorForeignKey(): void
    {
        try {
            Schema::table('admissions', function (Blueprint $table) {
                $table->dropForeign(['sponsor_id']);
            });
        } catch (\Throwable) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE admissions DROP FOREIGN KEY admissions_sponsor_id_foreign');
            }
        }
    }
};
