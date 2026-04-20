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
        Schema::create('welfare_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->tinyInteger('confidentiality_level')->default(2)->comment('1=Public, 2=Restricted, 3=Confidential, 4=Highly Confidential');
            $table->boolean('requires_approval')->default(false);
            $table->string('approval_role', 50)->nullable()->comment('school_head, hod, etc.');
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('active');
        });

        // Insert seed data
        $now = now();
        DB::table('welfare_types')->insert([
            [
                'name' => 'Counseling',
                'code' => 'COUNSEL',
                'description' => 'Student counseling and guidance sessions',
                'confidentiality_level' => 4,
                'requires_approval' => false,
                'approval_role' => null,
                'icon' => 'heart',
                'color' => 'purple',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Disciplinary',
                'code' => 'DISCIP',
                'description' => 'Disciplinary incidents and actions',
                'confidentiality_level' => 2,
                'requires_approval' => true,
                'approval_role' => 'school_head',
                'icon' => 'alert-triangle',
                'color' => 'red',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Safeguarding',
                'code' => 'SAFEGUARD',
                'description' => 'Child protection and safeguarding concerns',
                'confidentiality_level' => 4,
                'requires_approval' => false,
                'approval_role' => null,
                'icon' => 'shield',
                'color' => 'blue',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Health Incident',
                'code' => 'HEALTH',
                'description' => 'Health and medical incidents',
                'confidentiality_level' => 3,
                'requires_approval' => false,
                'approval_role' => null,
                'icon' => 'activity',
                'color' => 'green',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bullying',
                'code' => 'BULLY',
                'description' => 'Bullying incidents and investigations',
                'confidentiality_level' => 3,
                'requires_approval' => true,
                'approval_role' => 'school_head',
                'icon' => 'users',
                'color' => 'orange',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Financial Assistance',
                'code' => 'FINANCE',
                'description' => 'Financial aid and assistance applications',
                'confidentiality_level' => 2,
                'requires_approval' => true,
                'approval_role' => 'school_head',
                'icon' => 'dollar-sign',
                'color' => 'teal',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Intervention Plan',
                'code' => 'INTERVENE',
                'description' => 'Behavioral and academic intervention plans',
                'confidentiality_level' => 2,
                'requires_approval' => true,
                'approval_role' => 'hod',
                'icon' => 'target',
                'color' => 'indigo',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Parent Communication',
                'code' => 'PARENT_COMM',
                'description' => 'Parent and guardian communications',
                'confidentiality_level' => 1,
                'requires_approval' => false,
                'approval_role' => null,
                'icon' => 'message-circle',
                'color' => 'gray',
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
        Schema::dropIfExists('welfare_types');
    }
};
