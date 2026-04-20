<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lms_quiz_questions', function (Blueprint $table) {
            $table->text('explanation')->nullable()->after('feedback_incorrect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_quiz_questions', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });
    }
};
