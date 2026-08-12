<?php

namespace Tests\Feature\Crm;

use App\Mail\ClientSetupInvitationMail;
use App\Mail\ClientSetupVerificationCodeMail;
use App\Models\CrmClientSetupChangeRequest;
use App\Models\CrmClientSetupSubmission;
use App\Services\ClientSetup\ClientSetupInvitationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClientSetupReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_crm_user_can_review_and_progress_a_submission(): void
    {
        Mail::fake();
        $admin = $this->createUser();

        $response = $this->actingAs($admin)->post(route('crm.client-setup.store'), [
            'email' => 'review-client@example.com',
            'contact_name' => 'Review Client',
        ]);

        $submission = CrmClientSetupSubmission::query()->firstOrFail();

        $response->assertRedirect(route('crm.client-setup.show', $submission));
        Mail::assertSent(ClientSetupInvitationMail::class);
        $this->assertDatabaseHas('crm_client_setup_notifications', [
            'submission_id' => $submission->id,
            'event_key' => 'draft_created',
            'audience' => 'crm',
        ]);

        $this->actingAs($admin)->get(route('crm.client-setup.index'))
            ->assertOk()
            ->assertSee('review-client@example.com');

        $this->actingAs($admin)->get(route('crm.client-setup.show', $submission))
            ->assertOk()
            ->assertSee('Submission state')
            ->assertSee('Wizard categories')
            ->assertDontSee('Required stage review');

        $this->actingAs($admin)->post(route('crm.client-setup.notes.store', $submission), [
            'body' => 'Confirm the implementation calendar after academic review.',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('crm.client-setup.change-requests.store', $submission), [
            'stage_key' => 'scope',
            'field_key' => 'scope.institution_legal_name',
            'body' => 'Please confirm the registered institution name.',
        ])->assertRedirect();

        $changeRequest = CrmClientSetupChangeRequest::query()->firstOrFail();
        $this->assertDatabaseHas('crm_client_setup_submissions', [
            'id' => $submission->id,
            'status' => 'changes_requested',
        ]);

        $this->actingAs($admin)->patch(route('crm.client-setup.change-requests.resolve', [$submission, $changeRequest]))
            ->assertRedirect();

        $this->actingAs($admin)->patch(route('crm.client-setup.status', $submission), [
            'status' => 'under_review',
        ])->assertRedirect();

        $this->assertDatabaseHas('crm_client_setup_submissions', [
            'id' => $submission->id,
            'status' => 'under_review',
        ]);
        $this->assertDatabaseHas('crm_client_setup_events', [
            'submission_id' => $submission->id,
            'event_type' => 'review_status_changed',
        ]);
    }

    public function test_admin_can_delete_a_client_setup_record_permanently(): void
    {
        Mail::fake();
        $admin = $this->createUser();
        $invitation = app(ClientSetupInvitationService::class)->create([
            'email' => 'delete-client@example.com',
            'contact_name' => 'Delete Client',
        ], $admin);
        $submission = $invitation['submission'];

        $this->actingAs($admin)
            ->delete(route('crm.client-setup.destroy', $submission))
            ->assertRedirect(route('crm.client-setup.index'));

        $this->assertDatabaseMissing('crm_client_setup_submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('crm_client_setup_invitations', ['id' => $invitation['invitation']->id]);
    }

    public function test_non_admin_users_cannot_delete_client_setup_records(): void
    {
        $manager = $this->createUser(['role' => 'manager']);
        $submission = CrmClientSetupSubmission::query()->create([
            'status' => 'draft',
            'academic_status' => 'not_started',
            'payload' => [],
            'completed_stages' => [],
            'last_activity_at' => now(),
        ]);

        $this->actingAs($manager)
            ->delete(route('crm.client-setup.destroy', $submission))
            ->assertForbidden();

        $this->assertDatabaseHas('crm_client_setup_submissions', ['id' => $submission->id]);
    }

    public function test_rep_can_view_only_assigned_submissions(): void
    {
        $rep = $this->createUser(['role' => 'rep']);
        $other = $this->createUser(['role' => 'admin']);
        $submission = CrmClientSetupSubmission::query()->create([
            'status' => 'draft',
            'academic_status' => 'not_started',
            'payload' => [],
            'completed_stages' => [],
            'assigned_to_id' => $other->id,
            'last_activity_at' => now(),
        ]);

        $this->actingAs($rep)->get(route('crm.client-setup.index'))->assertOk()->assertDontSee($submission->uuid);
        $this->actingAs($rep)->get(route('crm.client-setup.show', $submission))->assertForbidden();
    }

    public function test_client_can_respond_to_an_open_change_request_through_the_verified_link(): void
    {
        Mail::fake();
        $admin = $this->createUser();
        $invitation = app(ClientSetupInvitationService::class)->create([
            'email' => 'client-response@example.com',
            'contact_name' => 'Response Client',
        ], $admin);
        $changeRequest = $invitation['submission']->changeRequests()->create([
            'user_id' => $admin->id,
            'stage_key' => 'scope',
            'field_key' => 'scope.institution_legal_name',
            'body' => 'Please confirm the registered name.',
            'status' => 'open',
        ]);

        $this->post(route('client-setup.verification-code', ['token' => $invitation['raw_token']]));
        $code = null;
        Mail::assertSent(ClientSetupVerificationCodeMail::class, function (ClientSetupVerificationCodeMail $mail) use (&$code): bool {
            $code = $mail->code;
            return true;
        });
        $this->post(route('client-setup.verify', ['token' => $invitation['raw_token']]), ['code' => $code]);

        $this->post(route('client-setup.change-request.respond', [
            'token' => $invitation['raw_token'],
            'changeRequest' => $changeRequest,
        ]), ['client_response' => 'The registered name is confirmed.'])
            ->assertRedirect();

        $this->assertDatabaseHas('crm_client_setup_change_requests', [
            'id' => $changeRequest->id,
            'client_response' => 'The registered name is confirmed.',
        ]);
        $this->assertDatabaseHas('crm_client_setup_events', [
            'submission_id' => $invitation['submission']->id,
            'event_type' => 'client_change_response_received',
        ]);
    }

    public function test_crm_user_can_compare_revisions_and_see_removed_values(): void
    {
        $admin = $this->createUser();
        $submission = CrmClientSetupSubmission::query()->create([
            'status' => 'draft',
            'academic_status' => 'in_progress',
            'payload' => [],
            'completed_stages' => [],
            'assigned_to_id' => $admin->id,
            'last_activity_at' => now(),
        ]);

        $submission->revisions()->create([
            'revision_number' => 1,
            'source' => 'client_stage_save',
            'stage_key' => 'scope',
            'payload' => [
                'scope' => [
                    'institution_legal_name' => 'Old College Name',
                    'legacy_code' => 'REMOVE-ME',
                    'campuses' => [['name' => 'Main campus']],
                ],
            ],
            'changed_keys' => ['scope.institution_legal_name', 'scope.legacy_code'],
        ]);
        $submission->revisions()->create([
            'revision_number' => 2,
            'source' => 'client_stage_save',
            'stage_key' => 'scope',
            'payload' => [
                'scope' => [
                    'institution_legal_name' => 'New College Name',
                    'campuses' => [['name' => 'Main campus'], ['name' => 'North campus']],
                ],
            ],
            'changed_keys' => ['scope.institution_legal_name', 'scope.campuses'],
        ]);

        $this->actingAs($admin)
            ->get(route('crm.client-setup.revisions.compare', [
                'submission' => $submission,
                'from' => 1,
                'to' => 2,
            ]))
            ->assertOk()
            ->assertSee('scope.institution_legal_name')
            ->assertSee('Old College Name')
            ->assertSee('New College Name')
            ->assertSee('scope.legacy_code')
            ->assertSee('REMOVE-ME')
            ->assertSee('(removed)')
            ->assertDontSee('scope.unrelated');
    }

    public function test_revision_comparison_is_scoped_to_the_submission_and_owner(): void
    {
        $rep = $this->createUser(['role' => 'rep']);
        $other = $this->createUser(['role' => 'admin']);
        $submission = CrmClientSetupSubmission::query()->create([
            'status' => 'draft',
            'academic_status' => 'in_progress',
            'payload' => [],
            'completed_stages' => [],
            'assigned_to_id' => $other->id,
            'last_activity_at' => now(),
        ]);
        $submission->revisions()->create([
            'revision_number' => 1,
            'source' => 'client_stage_save',
            'payload' => ['scope' => ['name' => 'Private institution']],
        ]);
        $submission->revisions()->create([
            'revision_number' => 2,
            'source' => 'client_stage_save',
            'payload' => ['scope' => ['name' => 'Changed institution']],
        ]);

        $this->actingAs($rep)
            ->get(route('crm.client-setup.revisions.compare', [
                'submission' => $submission,
                'from' => 1,
                'to' => 2,
            ]))
            ->assertForbidden();
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'CRM User',
            'email' => 'user-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }
}
