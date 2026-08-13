<?php

namespace Tests\Feature;

use App\Mail\ClientSetupVerificationCodeMail;
use App\Services\ClientSetup\ClientSetupInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClientSetupWizardShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_client_sees_the_crm_themed_stage_rail_and_progress_state(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))
            ->assertOk()
            ->assertSee('Configuration stages')
            ->assertSee('Academic readiness')
            ->assertSee('Save and continue')
            ->assertSee('data-wizard-action-input', false)
            ->assertSee('data-wizard-submit-action="continue"', false)
            ->assertSee('Jump to stage')
            ->assertDontSee('Finance and integrations')
            ->assertSee('placeholder="Enter the legal institution name"', false)
            ->assertSee('placeholder="Enter your full name"', false);
    }

    public function test_verification_flow_reveals_one_code_step_at_a_time(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();

        $this->get(route('client-setup.entry', ['token' => $invitation['raw_token']]))
            ->assertOk()
            ->assertSee('Send a verification code')
            ->assertDontSee('Enter your verification code')
            ->assertSee('crm-verification-card');

        $this->post(route('client-setup.verification-code', ['token' => $invitation['raw_token']]))
            ->assertRedirect(route('client-setup.entry', ['token' => $invitation['raw_token']]));

        $response = $this->get(route('client-setup.entry', ['token' => $invitation['raw_token']]));

        $response->assertOk()
            ->assertSee('Enter your verification code')
            ->assertDontSee('Send a verification code')
            ->assertSee('crm-otp-inputs')
            ->assertSee('data-code-countdown');

        $this->assertSame(6, substr_count($response->getContent(), 'id="otp_digit_'));
    }

    public function test_save_and_continue_completes_the_current_stage_and_moves_forward(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);

        $this->patch(route('client-setup.stage.save', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]), [
            'data' => [
                'institution_legal_name' => 'Synthetic College',
                'institution_common_name' => 'Synthetic College',
                'prepared_by_name' => 'Synthetic Client',
                'prepared_by_position' => 'Implementation lead',
                'submission_date' => now()->toDateString(),
                'target_go_live' => now()->addMonth()->format('Y-m'),
                'authorized_submitter_confirmed' => '1',
                'privacy_requirements_acknowledged' => '1',
            ],
            'status' => 'in_progress',
            'action' => 'continue',
        ])->assertRedirect(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'institution',
        ]))->assertSessionHas(
            'client_setup_success',
            'Stage saved. You can safely leave and return later.'
        );

        $this->assertDatabaseHas('crm_client_setup_stage_progress', [
            'stage_key' => 'scope',
            'status' => 'complete',
        ]);
    }

    public function test_save_progress_stays_on_the_current_stage_and_shows_the_save_confirmation(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);

        $this->patch(route('client-setup.stage.save', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]), [
            'payload_json' => json_encode(['institution_legal_name' => 'Synthetic College']),
            'status' => 'in_progress',
            'action' => 'save',
        ])->assertRedirect(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))->assertSessionHas(
            'client_setup_success',
            'Stage saved. You can safely leave and return later.'
        );

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))->assertOk()->assertSee('Stage saved. You can safely leave and return later.');

        $this->assertDatabaseHas('crm_client_setup_stage_progress', [
            'stage_key' => 'scope',
            'status' => 'in_progress',
        ]);
    }

    public function test_required_future_stages_are_locked_but_optional_stages_remain_available(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'programmes',
        ]))->assertRedirect(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]));

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'finance',
        ]))->assertNotFound();
    }

    public function test_save_and_exit_forgets_the_verified_session(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);

        $this->patch(route('client-setup.stage.save', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]), [
            'payload_json' => json_encode(['institution_legal_name' => 'Synthetic College']),
            'status' => 'in_progress',
            'action' => 'exit',
        ])->assertRedirect(route('client-setup.exit', ['token' => $invitation['raw_token']]));

        $this->assertFalse(session()->has(config('client_setup.session_verified_invitation_key')));

        $this->get(route('client-setup.exit', ['token' => $invitation['raw_token']]))
            ->assertOk()
            ->assertSee('Your progress has been saved');
    }

    private function createInvitation(): array
    {
        return app(ClientSetupInvitationService::class)->create([
            'email' => 'wizard-' . uniqid() . '@example.com',
            'contact_name' => 'Synthetic Client',
        ]);
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
