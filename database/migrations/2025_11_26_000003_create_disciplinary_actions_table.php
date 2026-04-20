<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disciplinary_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->tinyInteger('severity_level')->comment('1-5, 5 being most severe');
            $table->boolean('requires_approval')->default(false);
            $table->boolean('requires_parent_notification')->default(true);
            $table->integer('max_duration_days')->nullable()->comment('For suspensions');
            $table->enum('school_level', ['primary', 'junior', 'senior', 'all'])->default('all');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('severity_level');
            $table->index('active');
        });

        // Insert seed data
        $now = now();
        DB::table('disciplinary_actions')->insert([
            [
                'name' => 'Verbal Warning',
                'code' => 'VERBAL_WARN',
                'severity_level' => 1,
                'requires_approval' => false,
                'requires_parent_notification' => false,
                'max_duration_days' => null,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Written Warning',
                'code' => 'WRITTEN_WARN',
                'severity_level' => 2,
                'requires_approval' => false,
                'requires_parent_notification' => true,
                'max_duration_days' => null,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Detention',
                'code' => 'DETENTION',
                'severity_level' => 2,
                'requires_approval' => false,
                'requires_parent_notification' => true,
                'max_duration_days' => 1,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Community Service',
                'code' => 'COMMUNITY',
                'severity_level' => 2,
                'requires_approval' => false,
                'requires_parent_notification' => true,
                'max_duration_days' => 5,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Loss of Privileges',
                'code' => 'LOSS_PRIV',
                'severity_level' => 2,
                'requires_approval' => false,
                'requires_parent_notification' => true,
                'max_duration_days' => 14,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Parent Conference',
                'code' => 'PARENT_CONF',
                'severity_level' => 3,
                'requires_approval' => false,
                'requires_parent_notification' => true,
                'max_duration_days' => null,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'In-School Suspension',
                'code' => 'ISS',
                'severity_level' => 3,
                'requires_approval' => true,
                'requires_parent_notification' => true,
                'max_duration_days' => 3,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Out-of-School Suspension',
                'code' => 'OSS',
                'severity_level' => 4,
                'requires_approval' => true,
                'requires_parent_notification' => true,
                'max_duration_days' => 5,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Extended Suspension',
                'code' => 'EXT_SUSP',
                'severity_level' => 5,
                'requires_approval' => true,
                'requires_parent_notification' => true,
                'max_duration_days' => 14,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Expulsion Recommendation',
                'code' => 'EXPEL_REC',
                'severity_level' => 5,
                'requires_approval' => true,
                'requires_parent_notification' => true,
                'max_duration_days' => null,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Behavioral Contract',
                'code' => 'BEH_CONTRACT',
                'severity_level' => 3,
                'requires_approval' => true,
                'requires_parent_notification' => true,
                'max_duration_days' => null,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Counseling Referral',
                'code' => 'COUNSEL_REF',
                'severity_level' => 2,
                'requires_approval' => false,
                'requires_parent_notification' => true,
                'max_duration_days' => null,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinary_actions');
    }
};
