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
        Schema::create('disciplinary_incident_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->enum('severity', ['minor', 'moderate', 'major', 'severe']);
            $table->foreignId('default_action_id')->nullable()->constrained('disciplinary_actions')->nullOnDelete();
            $table->boolean('requires_approval')->default(false);
            $table->enum('school_level', ['primary', 'junior', 'senior', 'all'])->default('all');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('severity');
            $table->index('active');
        });

        // Get action IDs for default actions
        $verbalWarnId = DB::table('disciplinary_actions')->where('code', 'VERBAL_WARN')->value('id');
        $writtenWarnId = DB::table('disciplinary_actions')->where('code', 'WRITTEN_WARN')->value('id');
        $detentionId = DB::table('disciplinary_actions')->where('code', 'DETENTION')->value('id');
        $parentConfId = DB::table('disciplinary_actions')->where('code', 'PARENT_CONF')->value('id');
        $issId = DB::table('disciplinary_actions')->where('code', 'ISS')->value('id');
        $ossId = DB::table('disciplinary_actions')->where('code', 'OSS')->value('id');

        // Insert seed data
        $now = now();
        DB::table('disciplinary_incident_types')->insert([
            [
                'name' => 'Late to School',
                'code' => 'LATE',
                'severity' => 'minor',
                'default_action_id' => $verbalWarnId,
                'requires_approval' => false,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Late to Class',
                'code' => 'LATE_CLASS',
                'severity' => 'minor',
                'default_action_id' => $verbalWarnId,
                'requires_approval' => false,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Uniform Violation',
                'code' => 'UNIFORM',
                'severity' => 'minor',
                'default_action_id' => $verbalWarnId,
                'requires_approval' => false,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Disruptive Behavior',
                'code' => 'DISRUPT',
                'severity' => 'minor',
                'default_action_id' => $writtenWarnId,
                'requires_approval' => false,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Incomplete Homework',
                'code' => 'HOMEWORK',
                'severity' => 'minor',
                'default_action_id' => $verbalWarnId,
                'requires_approval' => false,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Disrespect to Staff',
                'code' => 'DISRESPECT',
                'severity' => 'moderate',
                'default_action_id' => $detentionId,
                'requires_approval' => false,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Cheating',
                'code' => 'CHEAT',
                'severity' => 'moderate',
                'default_action_id' => $parentConfId,
                'requires_approval' => true,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Truancy',
                'code' => 'TRUANCY',
                'severity' => 'moderate',
                'default_action_id' => $parentConfId,
                'requires_approval' => true,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Fighting',
                'code' => 'FIGHT',
                'severity' => 'major',
                'default_action_id' => $issId,
                'requires_approval' => true,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bullying',
                'code' => 'BULLY',
                'severity' => 'major',
                'default_action_id' => $issId,
                'requires_approval' => true,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Theft',
                'code' => 'THEFT',
                'severity' => 'major',
                'default_action_id' => $ossId,
                'requires_approval' => true,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Vandalism',
                'code' => 'VANDAL',
                'severity' => 'major',
                'default_action_id' => $ossId,
                'requires_approval' => true,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Substance Possession',
                'code' => 'SUBSTANCE',
                'severity' => 'severe',
                'default_action_id' => $ossId,
                'requires_approval' => true,
                'school_level' => 'junior',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Substance Use',
                'code' => 'SUBSTANCE_USE',
                'severity' => 'severe',
                'default_action_id' => $ossId,
                'requires_approval' => true,
                'school_level' => 'junior',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Weapon Possession',
                'code' => 'WEAPON',
                'severity' => 'severe',
                'default_action_id' => $ossId,
                'requires_approval' => true,
                'school_level' => 'all',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sexual Misconduct',
                'code' => 'SEXUAL',
                'severity' => 'severe',
                'default_action_id' => $ossId,
                'requires_approval' => true,
                'school_level' => 'junior',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Assault',
                'code' => 'ASSAULT',
                'severity' => 'severe',
                'default_action_id' => $ossId,
                'requires_approval' => true,
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
        Schema::dropIfExists('disciplinary_incident_types');
    }
};
