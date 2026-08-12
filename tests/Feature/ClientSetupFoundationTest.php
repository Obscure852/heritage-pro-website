<?php

namespace Tests\Feature;

use App\Mail\ClientSetupVerificationCodeMail;
use App\Mail\ClientSetupInvitationMail;
use App\Models\CrmClientSetupInvitation;
use App\Services\ClientSetup\ClientSetupInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClientSetupFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_token_is_hashed_and_public_entry_requires_verification(): void
    {
        $invitation = $this->createInvitation();

        $this->assertDatabaseMissing('crm_client_setup_invitations', [
            'token_hash' => $invitation['raw_token'],
        ]);

        $this->get(route('client-setup.entry', ['token' => $invitation['raw_token']]))
            ->assertOk()
            ->assertSee('Verify your client setup access');

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))->assertRedirect(route('client-setup.entry', ['token' => $invitation['raw_token']]));
    }

    public function test_invitation_can_be_sent_without_persisting_the_raw_token(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();

        app(ClientSetupInvitationService::class)->sendInvitation(
            $invitation['invitation'],
            $invitation['raw_token']
        );

        Mail::assertSent(ClientSetupInvitationMail::class, function (ClientSetupInvitationMail $mail) use ($invitation): bool {
            return $mail->invitation->is($invitation['invitation'])
                && str_contains($mail->setupUrl, $invitation['raw_token']);
        });

        $this->assertDatabaseMissing('crm_client_setup_invitations', [
            'token_hash' => $invitation['raw_token'],
        ]);
    }

    public function test_verification_code_is_emailed_and_opens_a_session_bound_draft(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();

        $this->post(route('client-setup.verification-code', ['token' => $invitation['raw_token']]))
            ->assertRedirect(route('client-setup.entry', ['token' => $invitation['raw_token']]));

        $code = null;

        Mail::assertSent(ClientSetupVerificationCodeMail::class, function (ClientSetupVerificationCodeMail $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        $this->assertIsString($code);
        $this->assertSame(6, strlen($code));

        $this->post(route('client-setup.verify', ['token' => $invitation['raw_token']]), [
            'code' => $code,
        ])->assertRedirect(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]));

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))->assertOk()->assertSee('Draft foundation');

        $this->assertDatabaseHas('crm_client_setup_events', [
            'event_type' => 'invitation_verified',
            'actor_type' => 'client',
        ]);
    }

    public function test_verified_client_can_save_stage_payload_and_resume_it(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);

        $payload = [
            'institution_legal_name' => 'Synthetic College',
            'authorized_submitter_confirmed' => true,
        ];

        $this->from(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))->patch(route('client-setup.stage.save', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]), [
            'payload' => $payload,
            'status' => 'complete',
        ])->assertRedirect(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]));

        $this->assertDatabaseHas('crm_client_setup_stage_progress', [
            'stage_key' => 'scope',
            'status' => 'complete',
        ]);

        $this->assertDatabaseHas('crm_client_setup_revisions', [
            'revision_number' => 1,
            'source' => 'client_stage_save',
            'stage_key' => 'scope',
        ]);

        $this->assertDatabaseHas('crm_client_setup_events', [
            'event_type' => 'stage_saved',
            'stage_key' => 'scope',
        ]);

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))->assertOk()->assertSee('Synthetic College');
    }

    public function test_invalid_verification_codes_are_capped(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();

        $this->post(route('client-setup.verification-code', ['token' => $invitation['raw_token']]));

        for ($attempt = 1; $attempt <= config('client_setup.verification_max_attempts'); $attempt++) {
            $this->post(route('client-setup.verify', ['token' => $invitation['raw_token']]), [
                'code' => '000000',
            ])->assertRedirect(route('client-setup.entry', ['token' => $invitation['raw_token']]));
        }

        $this->assertDatabaseHas('crm_client_setup_invitations', [
            'id' => $invitation['invitation']->id,
            'verification_attempts' => config('client_setup.verification_max_attempts'),
        ]);

        $this->assertFalse(session()->has(config('client_setup.session_verified_invitation_key')));
    }

    public function test_expired_and_revoked_invitations_cannot_be_opened(): void
    {
        $expired = $this->createInvitation([
            'expires_at' => now()->subMinute(),
        ]);

        $this->get(route('client-setup.entry', ['token' => $expired['raw_token']]))->assertNotFound();

        $active = $this->createInvitation();
        app(ClientSetupInvitationService::class)->revoke($active['invitation']);

        $this->get(route('client-setup.entry', ['token' => $active['raw_token']]))->assertNotFound();
    }

    private function createInvitation(array $attributes = []): array
    {
        return app(ClientSetupInvitationService::class)->create(array_merge([
            'email' => 'client-' . uniqid() . '@example.com',
            'contact_name' => 'Synthetic Client',
        ], $attributes));
    }

    private function verifyInvitation(array $invitation): void
    {
        $this->post(route('client-setup.verification-code', ['token' => $invitation['raw_token']]));

        $code = null;

        Mail::assertSent(ClientSetupVerificationCodeMail::class, function (ClientSetupVerificationCodeMail $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        $this->post(route('client-setup.verify', ['token' => $invitation['raw_token']]), [
            'code' => $code,
        ])->assertRedirect();
    }
}
