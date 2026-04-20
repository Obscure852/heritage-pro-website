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
        Schema::table('lms_modules', function (Blueprint $table) {
            $table->foreignId('prerequisite_module_id')->nullable()->after('is_locked')
                ->constrained('lms_modules')->onDelete('set null');
            $table->boolean('require_sequential_completion')->default(false)->after('prerequisite_module_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lms_modules', function (Blueprint $table) {
            $table->dropForeign(['prerequisite_module_id']);
            $table->dropColumn(['prerequisite_module_id', 'require_sequential_completion']);
        });
    }
};
