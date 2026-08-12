<?php

namespace Tests\Feature;

use App\Mail\ClientSetupInvitationMail;
use App\Models\CrmClientSetupEvent;
use App\Models\CrmClientSetupStageProgress;
use App\Models\CrmClientSetupSubmission;
use App\Models\User;
use App\Services\ClientSetup\ClientSetupAccessService;
use App\Services\ClientSetup\ClientSetupAttachmentService;
use App\Services\ClientSetup\ClientSetupInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\TestCase;

class ClientSetupQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_conditional_fields_are_rendered_with_progressive_disclosure_metadata(): void
    {
        $invitation = $this->createVerifiedInvitation();

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'finance',
        ]))
            ->assertOk()
            ->assertSee('data-conditional-field')
            ->assertSee('client_setup_finance_deferred_owner')
            ->assertSee('syncConditionalFields');
    }

    public function test_campus_structure_variation_is_optional_and_collapsible(): void
    {
        $invitation = $this->createVerifiedInvitation();

        CrmClientSetupStageProgress::query()->create([
            'submission_id' => $invitation['submission']->id,
            'stage_key' => 'scope',
            'status' => 'complete',
            'completed_at' => now(),
            'last_saved_at' => now(),
        ]);

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'institution',
        ]))
            ->assertOk()
            ->assertSee('data-collapsible-field', false)
            ->assertSee('Does the academic structure vary by campus?')
            ->assertSee('>(Optional)</span>', false)
            ->assertSee('Yes, the academic structure varies by campus')
            ->assertSee('data-conditional-field', false)
            ->assertSee('data-repeatable-label="Academic periods"', false)
            ->assertSee('data-repeatable-required="false"', false)
            ->assertDontSee('Academic periods 1')
            ->assertSee('Markbook lock rule <span class="crm-wizard-requirement-label">(Optional)</span>', false)
            ->assertSee('placeholder="e.g. REG-2026-001"', false)
            ->assertSee('placeholder="e.g. Faculty → department → programme → level → semester → module"', false)
            ->assertSee('>Choose academic year pattern</option>', false);
    }

    public function test_responsible_contacts_explain_the_required_academic_lead(): void
    {
        $invitation = $this->createVerifiedInvitation();

        CrmClientSetupStageProgress::query()->create([
            'submission_id' => $invitation['submission']->id,
            'stage_key' => 'scope',
            'status' => 'complete',
            'completed_at' => now(),
            'last_saved_at' => now(),
        ]);

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'institution',
        ]))
            ->assertOk()
            ->assertSee('Responsible contacts')
            ->assertSee('Required: include at least one contact with the role Academic lead.');
    }

    public function test_nqf_level_has_an_accessible_ncqf_info_popover(): void
    {
        $invitation = $this->createVerifiedInvitation();

        foreach (['scope', 'institution'] as $stageKey) {
            CrmClientSetupStageProgress::query()->create([
                'submission_id' => $invitation['submission']->id,
                'stage_key' => $stageKey,
                'status' => 'complete',
                'completed_at' => now(),
                'last_saved_at' => now(),
            ]);
        }

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'programmes',
        ]))
            ->assertOk()
            ->assertSee('More information about NQF level', false)
            ->assertSee('Botswana NCQF guide', false)
            ->assertSee('Certificate III — Level 3', false)
            ->assertSee('Diploma — Level 6', false)
            ->assertSee('Bachelor’s degree — Level 7', false)
            ->assertSee('role="tooltip"', false)
            ->assertSee('Faculty', false)
            ->assertDontSee('Faculty/school owner', false)
            ->assertSee('Programme purpose and outcomes')
            ->assertSee('(Optional)')
            ->assertSee('placeholder="e.g. January and August"', false)
            ->assertSee('placeholder="e.g. minimum of 30 points"', false)
            ->assertSee('placeholder="e.g. 120 credits required for graduation"', false)
            ->assertSee('List the months or dates when students can start this programme', false);
    }

    public function test_curriculum_scope_and_handoff_is_optional_and_collapsible(): void
    {
        $invitation = $this->createVerifiedInvitation();

        foreach (['scope', 'institution', 'programmes'] as $stageKey) {
            CrmClientSetupStageProgress::query()->create([
                'submission_id' => $invitation['submission']->id,
                'stage_key' => $stageKey,
                'status' => 'complete',
                'completed_at' => now(),
                'last_saved_at' => now(),
            ]);
        }

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'curriculum',
        ]))
            ->assertOk()
            ->assertSee('data-collapsible-field', false)
            ->assertSee('Curriculum scope and handoff')
            ->assertSee('(Optional)')
            ->assertSee('Curriculum configuration is in scope for this implementation.')
            ->assertSee('If curriculum is out of scope, explain the implementation handoff or migration plan.')
            ->assertSee('data-repeatable-label="Curriculum versions"', false)
            ->assertSee('data-repeatable-required="false"', false);
    }

    public function test_resume_and_verification_inputs_explain_expected_values(): void
    {
        $this->get(route('client-setup.resume'))
            ->assertOk()
            ->assertSee('placeholder="e.g. administrator@school.org"', false);

        $invitation = $this->createInvitation();
        $this->post(route('client-setup.verification-code', ['token' => $invitation['raw_token']]));

        $this->get(route('client-setup.entry', ['token' => $invitation['raw_token']]))
            ->assertOk()
            ->assertSee('placeholder="0"', false)
            ->assertSee('aria-label="Digit 1"', false);
    }

    public function test_wizard_exposes_accessible_stage_context_and_repeatable_controls(): void
    {
        $invitation = $this->createVerifiedInvitation();

        CrmClientSetupStageProgress::query()->create([
            'submission_id' => $invitation['submission']->id,
            'stage_key' => 'scope',
            'status' => 'complete',
            'completed_at' => now(),
            'last_saved_at' => now(),
        ]);

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'institution',
        ]))
            ->assertOk()
            ->assertSee('data-wizard-main-heading')
            ->assertSee('data-wizard-announcement')
            ->assertSee('aria-label="Academic setup progress"', false)
            ->assertSee('data-repeatable-row-heading')
            ->assertSee('aria-required="true"', false)
            ->assertSee('updateRepeatableCollection');
    }

    public function test_wizard_form_elements_use_the_input_radius(): void
    {
        $invitation = $this->createVerifiedInvitation();

        CrmClientSetupStageProgress::query()->create([
            'submission_id' => $invitation['submission']->id,
            'stage_key' => 'scope',
            'status' => 'complete',
            'completed_at' => now(),
            'last_saved_at' => now(),
        ]);

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'institution',
        ]))
            ->assertOk()
            ->assertSee('--crm-wizard-control-radius: 4px', false)
            ->assertSee('[data-wizard-form] .form-control', false)
            ->assertSee('[data-wizard-form] .crm-wizard-repeatable-card', false)
            ->assertSee('border-radius: var(--crm-wizard-control-radius)', false);
    }

    public function test_public_users_cannot_open_crm_client_setup_routes(): void
    {
        $this->get(route('crm.client-setup.index'))
            ->assertRedirect(route('login'));
    }

    public function test_crm_client_setup_tables_expose_keyboard_scroll_regions(): void
    {
        $admin = $this->createUser();
        $invitation = $this->createInvitation(['assigned_to_id' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('crm.client-setup.index'))
            ->assertOk()
            ->assertSee('role="region" aria-label="Client setup submission inbox" tabindex="0"', false)
            ->assertSee('title="Delete client setup"', false);

        $this->actingAs($admin)
            ->get(route('crm.client-setup.show', $invitation['submission']))
            ->assertOk()
            ->assertSee('role="region" aria-label="Optional implementation scope" tabindex="0"', false)
            ->assertSee('class="btn btn-light crm-btn-light crm-review-control-button"', false)
            ->assertSee('class="btn btn-primary crm-review-control-button"', false)
            ->assertSee('data-bs-target="#crm-client-setup-note-modal"', false)
            ->assertSee('data-bs-target="#crm-client-setup-change-modal"', false)
            ->assertSee('id="crm-client-setup-note-modal"', false)
            ->assertSee('id="crm-client-setup-change-modal"', false)
            ->assertSee('class="crm-modal-form"', false)
            ->assertSee('aria-label="Delete"', false)
            ->assertSee('bx-trash');
    }

    public function test_crm_submission_data_is_grouped_into_wizard_category_tabs(): void
    {
        $admin = $this->createUser();
        $invitation = $this->createInvitation(['assigned_to_id' => $admin->id]);
        $invitation['submission']->forceFill([
            'payload' => [
                'scope' => [
                    'institution_legal_name' => 'Tabbed College',
                    'prepared_by_name' => 'Review Client',
                ],
                'institution' => [
                    'registration_number' => 'REG-001',
                ],
            ],
            'completed_stages' => ['scope'],
        ])->save();
        $invitation['submission']->stageProgress()->create([
            'stage_key' => 'scope',
            'status' => 'complete',
            'completed_at' => now(),
            'last_saved_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('crm.client-setup.show', $invitation['submission']));

        $response->assertOk()
            ->assertSee('Wizard categories')
            ->assertSee('Institution data')
            ->assertSee('role="tablist" aria-label="Institution data categories"', false)
            ->assertSee('id="submission-tab-scope"', false)
            ->assertSee('id="submission-panel-institution"', false)
            ->assertSee('Tabbed College')
            ->assertSee('REG-001')
            ->assertSee('data-submission-tab')
            ->assertSee('activateTab');

        $this->assertSame(11, substr_count($response->getContent(), 'role="tab"'));
    }

    public function test_client_setup_audit_events_cannot_be_updated(): void
    {
        $invitation = $this->createInvitation();
        $event = $invitation['submission']->events()->create([
            'invitation_id' => $invitation['invitation']->id,
            'actor_type' => 'client',
            'event_type' => 'quality_probe',
            'metadata' => ['source' => 'test'],
            'occurred_at' => now(),
        ]);

        $this->expectException(LogicException::class);
        $event->event_type = 'tampered';
        $event->save();

        $this->assertSame('quality_probe', $event->fresh()->event_type);
    }

    public function test_client_setup_audit_events_cannot_be_deleted(): void
    {
        $invitation = $this->createInvitation();
        $event = $invitation['submission']->events()->create([
            'invitation_id' => $invitation['invitation']->id,
            'actor_type' => 'client',
            'event_type' => 'quality_probe',
            'metadata' => ['source' => 'test'],
            'occurred_at' => now(),
        ]);

        $this->expectException(LogicException::class);
        $event->delete();

        $this->assertInstanceOf(CrmClientSetupEvent::class, $event->fresh());
    }

    public function test_unassigned_crm_representatives_cannot_open_another_submission(): void
    {
        $rep = $this->createUser(['role' => 'rep']);
        $owner = $this->createUser(['role' => 'admin']);
        $submission = CrmClientSetupSubmission::query()->create([
            'assigned_to_id' => $owner->id,
            'schema_version' => '1.0',
            'status' => 'draft',
            'academic_status' => 'not_started',
            'payload' => [],
            'completed_stages' => [],
            'last_activity_at' => now(),
        ]);

        $this->actingAs($rep)
            ->get(route('crm.client-setup.show', $submission))
            ->assertForbidden();
    }

    public function test_pending_private_attachments_cannot_be_downloaded(): void
    {
        Mail::fake();
        Storage::fake('documents');
        $admin = $this->createUser();
        $invitation = $this->createInvitation(['assigned_to_id' => $admin->id]);
        $attachment = app(ClientSetupAttachmentService::class)->store(
            $invitation['submission'],
            $invitation['invitation'],
            UploadedFile::fake()->create('private-policy.pdf', 20, 'application/pdf'),
            'policy',
            'required'
        );

        $this->actingAs($admin)
            ->get(route('crm.client-setup.attachment.download', [
                'submission' => $invitation['submission'],
                'attachment' => $attachment,
            ]))
            ->assertStatus(423);

        $other = $this->createInvitation(['assigned_to_id' => $admin->id]);
        $this->actingAs($admin)
            ->get(route('crm.client-setup.attachment.download', [
                'submission' => $other['submission'],
                'attachment' => $attachment,
            ]))
            ->assertNotFound();
    }

    public function test_invalid_upload_types_are_rejected_before_private_storage(): void
    {
        Mail::fake();
        Storage::fake('documents');
        $invitation = $this->createVerifiedInvitation();

        $this->from(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'evidence_signoff',
        ]))->post(route('client-setup.attachment-upload', [
            'token' => $invitation['raw_token'],
        ]), [
            'category' => 'malware sample',
            'requirement' => 'optional',
            'attachment' => UploadedFile::fake()->create('payload.exe', 10, 'application/x-msdownload'),
        ])->assertRedirect()
            ->assertSessionHasErrors('attachment');

        $this->assertDatabaseCount('crm_client_setup_attachments', 0);
    }

    public function test_verification_code_rate_limit_is_active(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('client-setup.verification-code', [
                'token' => $invitation['raw_token'],
            ]));
        }

        $this->post(route('client-setup.verification-code', [
            'token' => $invitation['raw_token'],
        ]))->assertStatus(429);
    }

    public function test_invitation_email_subject_and_persisted_log_do_not_contain_raw_token(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();

        app(ClientSetupInvitationService::class)->sendInvitation(
            $invitation['invitation'],
            $invitation['raw_token']
        );

        Mail::assertSent(ClientSetupInvitationMail::class, function (ClientSetupInvitationMail $mail) use ($invitation): bool {
            return ! str_contains($mail->subject, $invitation['raw_token']);
        });

        $notificationPayload = \App\Models\CrmClientSetupNotification::query()
            ->where('event_key', 'invitation_sent')
            ->firstOrFail()
            ->payload;

        $this->assertStringNotContainsString($invitation['raw_token'], json_encode($notificationPayload));
    }

    private function createVerifiedInvitation(): array
    {
        $invitation = $this->createInvitation();
        app(ClientSetupAccessService::class)->markVerified($invitation['invitation']);

        return $invitation;
    }

    private function createInvitation(array $attributes = []): array
    {
        return app(ClientSetupInvitationService::class)->create(array_merge([
            'email' => 'quality-' . uniqid() . '@example.com',
            'contact_name' => 'Quality Client',
        ], $attributes));
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Quality User',
            'email' => 'quality-user-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }
}
