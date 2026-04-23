<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('crm_leave_types') && ! Schema::hasColumn('crm_leave_types', 'gender_restriction')) {
            Schema::table('crm_leave_types', function (Blueprint $table) {
                $table->string('gender_restriction', 20)->nullable()->after('carry_over_limit');
            });

            // Set gender restrictions for seeded types
            DB::table('crm_leave_types')->where('code', 'ML')->update(['gender_restriction' => 'female']);
            DB::table('crm_leave_types')->where('code', 'PL')->update(['gender_restriction' => 'male']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_leave_types') && Schema::hasColumn('crm_leave_types', 'gender_restriction')) {
            Schema::table('crm_leave_types', function (Blueprint $table) {
                $table->dropColumn('gender_restriction');
            });
        }
    }
};
