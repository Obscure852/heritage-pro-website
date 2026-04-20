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
        Schema::table('psle_grades', function (Blueprint $table) {
            $table->string('religious_and_moral_education_grade', 191)->nullable()->after('capa_grade');
            $table->index('religious_and_moral_education_grade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('psle_grades', function (Blueprint $table) {
            $table->dropIndex(['religious_and_moral_education_grade']);
            $table->dropColumn('religious_and_moral_education_grade');
        });
    }
};
