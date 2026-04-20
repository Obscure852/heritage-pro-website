<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('senior_admission_placement_criteria', function (Blueprint $table) {
            $table->string('science_ceiling_grade')->nullable()->after('mathematics_worst_grade');
            $table->string('promotion_pathway')->nullable()->after('science_ceiling_grade');
        });
    }

    public function down(): void
    {
        Schema::table('senior_admission_placement_criteria', function (Blueprint $table) {
            $table->dropColumn(['science_ceiling_grade', 'promotion_pathway']);
        });
    }
};
