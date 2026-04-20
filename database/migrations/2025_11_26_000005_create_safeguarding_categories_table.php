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
        Schema::create('safeguarding_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->text('guidance_notes')->nullable();
            $table->boolean('immediate_action_required')->default(false);
            $table->boolean('notify_authorities')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('active');
        });

        // Insert seed data
        $now = now();
        DB::table('safeguarding_categories')->insert([
            [
                'name' => 'Physical Abuse',
                'code' => 'PHYS_ABUSE',
                'description' => 'Signs of physical harm or violence towards the child',
                'guidance_notes' => 'Document any visible injuries. Do not ask leading questions. Report immediately to DSL.',
                'immediate_action_required' => true,
                'notify_authorities' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Emotional Abuse',
                'code' => 'EMOT_ABUSE',
                'description' => 'Persistent emotional maltreatment affecting development',
                'guidance_notes' => 'Note behavioral changes over time. Maintain detailed records of observations.',
                'immediate_action_required' => false,
                'notify_authorities' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sexual Abuse',
                'code' => 'SEX_ABUSE',
                'description' => 'Sexual exploitation or inappropriate behavior towards child',
                'guidance_notes' => 'Never investigate yourself. Report immediately to DSL. Preserve any evidence.',
                'immediate_action_required' => true,
                'notify_authorities' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Neglect',
                'code' => 'NEGLECT',
                'description' => 'Failure to meet basic physical or emotional needs',
                'guidance_notes' => 'Document patterns of neglect. Note hygiene, nutrition, and supervision concerns.',
                'immediate_action_required' => false,
                'notify_authorities' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Domestic Violence Exposure',
                'code' => 'DOMESTIC',
                'description' => 'Child exposed to domestic violence at home',
                'guidance_notes' => 'Child may be reluctant to disclose. Provide safe space for conversation.',
                'immediate_action_required' => false,
                'notify_authorities' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Self-Harm',
                'code' => 'SELF_HARM',
                'description' => 'Student engaging in self-harming behaviors',
                'guidance_notes' => 'Refer to school counselor immediately. Do not promise confidentiality.',
                'immediate_action_required' => true,
                'notify_authorities' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Suicidal Ideation',
                'code' => 'SUICIDE',
                'description' => 'Student expressing suicidal thoughts or intentions',
                'guidance_notes' => 'Do not leave student alone. Contact parents/guardians and refer for immediate support.',
                'immediate_action_required' => true,
                'notify_authorities' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Online Safety',
                'code' => 'ONLINE',
                'description' => 'Online exploitation, grooming, or cyberbullying concerns',
                'guidance_notes' => 'Preserve digital evidence if possible. Report to appropriate authorities.',
                'immediate_action_required' => false,
                'notify_authorities' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Exploitation',
                'code' => 'EXPLOIT',
                'description' => 'Child criminal exploitation, county lines, or trafficking',
                'guidance_notes' => 'May involve coercion. Child may not recognize exploitation. Report to police.',
                'immediate_action_required' => true,
                'notify_authorities' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Radicalisation',
                'code' => 'RADICAL',
                'description' => 'Concerns about extremist views or radicalization',
                'guidance_notes' => 'Document concerns objectively. Refer through appropriate channels.',
                'immediate_action_required' => false,
                'notify_authorities' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'FGM Risk',
                'code' => 'FGM',
                'description' => 'Female genital mutilation risk or disclosure',
                'guidance_notes' => 'Mandatory reporting requirement. Document any disclosures verbatim.',
                'immediate_action_required' => true,
                'notify_authorities' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Forced Marriage',
                'code' => 'FORCED_MARR',
                'description' => 'Risk of forced marriage',
                'guidance_notes' => 'Do not inform family. One chance rule - take action immediately.',
                'immediate_action_required' => true,
                'notify_authorities' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'General Welfare Concern',
                'code' => 'GENERAL',
                'description' => 'General concern about child welfare not fitting other categories',
                'guidance_notes' => 'Document observations and consult with DSL for appropriate response.',
                'immediate_action_required' => false,
                'notify_authorities' => false,
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
        Schema::dropIfExists('safeguarding_categories');
    }
};
