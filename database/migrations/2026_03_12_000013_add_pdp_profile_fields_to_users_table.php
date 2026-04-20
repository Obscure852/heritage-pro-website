<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'personal_payroll_number')) {
                $table->string('personal_payroll_number')->nullable()->after('area_of_work');
                $table->index('personal_payroll_number');
            }

            if (!Schema::hasColumn('users', 'date_of_appointment')) {
                $table->date('date_of_appointment')->nullable()->after('personal_payroll_number');
                $table->index('date_of_appointment');
            }

            if (!Schema::hasColumn('users', 'earning_band')) {
                $table->string('earning_band')->nullable()->after('date_of_appointment');
                $table->index('earning_band');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            foreach (['personal_payroll_number', 'date_of_appointment', 'earning_band'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
