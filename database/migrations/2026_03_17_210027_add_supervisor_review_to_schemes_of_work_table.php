<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('schemes_of_work', function (Blueprint $table) {
            $table->unsignedBigInteger('supervisor_reviewed_by')->nullable()->after('reviewed_at');
            $table->timestamp('supervisor_reviewed_at')->nullable()->after('supervisor_reviewed_by');
            $table->text('supervisor_comments')->nullable()->after('supervisor_reviewed_at');

            $table->foreign('supervisor_reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('schemes_of_work', function (Blueprint $table) {
            $table->dropForeign(['supervisor_reviewed_by']);
            $table->dropColumn(['supervisor_reviewed_by', 'supervisor_reviewed_at', 'supervisor_comments']);
        });
    }
};
