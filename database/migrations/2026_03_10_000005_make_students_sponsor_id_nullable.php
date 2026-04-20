<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        $this->dropSponsorForeignKey();

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE students MODIFY sponsor_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('sponsor_id')->nullable()->change();
            });
        }

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void {
        $this->dropSponsorForeignKey();

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE students MODIFY sponsor_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('sponsor_id')->nullable(false)->change();
            });
        }

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    private function dropSponsorForeignKey(): void {
        try {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['sponsor_id']);
            });
        } catch (\Throwable) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE students DROP FOREIGN KEY students_sponsor_id_foreign');
            }
        }
    }
};
