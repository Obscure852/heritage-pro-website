<?php

namespace Tests\Feature;

use App\Mail\ClientSetupInvitationMail;
use App\Mail\ClientSetupNotificationMail;
use App\Models\CrmClientSetupNotification;
use App\Models\CrmClientSetupSubmission;
use App\Models\User;
use App\Services\ClientSetup\ClientSetupInvitationService;
use App\Services\ClientSetup\ClientSetupNotificationService;
use App\Services\ClientSetup\ClientSetupReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClientSetupOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_link_request_rotates_the_token_without_email_enumeration(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();

        $this->get(route('client-setup.resume'))
            ->assertOk()
            ->assertSee('Request a new setup link');

        $this->post(route('client-setup.resume.request'), [
            'email' => $invitation['invitation']->email,
        ])->assertRedirect(route('client-setup.resume'));

        Mail::assertSent(ClientSetupInvitationMail::class, function (ClientSetupInvitationMail $mail) use ($invitation): bool {
            return $mail->invitation->id === $invitation['invitation']->id
                && $mail->setupUrl !== $invitation['url'];
        });

        $invitationNotification = CrmClientSetupNotification::query()
            ->where('event_key', 'invitation_sent')
            ->firstOrFail();
        $this->assertStringNotContainsString($invitation['raw_token'], json_encode($invitationNotification->payload));

        $this->assertDatabaseHas('crm_client_setup_events', [
            'submission_id' => $invitation['submission']->id,
            'event_type' => 'resume_link_requested',
            'actor_type' => 'client',
        ]);

        $this->post(route('client-setup.resume.request'), [
            'email' => 'not-found@example.com',
        ])->assertRedirect(route('client-setup.resume'));
    }

    public function test_verification_code_request_does_not_emit_submission_completion_notifications(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();

        $this->post(route('client-setup.verification-code', ['token' => $invitation['raw_token']]))
            ->assertRedirect(route('client-setup.entry', ['token' => $invitation['raw_token']]));

        $this->assertSame(0, CrmClientSetupNotification::query()->whereIn('event_key', [
            'supplemental_received',
            'final_submission_received',
        ])->count());
    }

    public function test_workflow_notifications_are_logged_and_sent_to_client_and_owner(): void
    {
        Mail::fake();
        $owner = $this->createUser();
        $invitation = $this->createInvitation(['assigned_to_id' => $owner->id]);

        $notifications = app(ClientSetupNotificationService::class)->notifySubmission(
            $invitation['submission'],
            'academic_submitted',
            ['context_key' => 'revision-1']
        );

        $this->assertCount(2, $notifications);
        $this->assertSame(2, CrmClientSetupNotification::query()->where('event_key', 'academic_submitted')->count());
        $this->assertSame(2, CrmClientSetupNotification::query()->where('status', 'sent')->count());
        Mail::assertSent(ClientSetupNotificationMail::class, 2);

        app(ClientSetupNotificationService::class)->notifySubmission(
            $invitation['submission'],
            'academic_submitted',
            ['context_key' => 'revision-1']
        );

        $this->assertSame(2, CrmClientSetupNotification::query()->where('event_key', 'academic_submitted')->count());
    }

    public function test_reminder_command_is_repeatable_and_retries_a_failed_delivery(): void
    {
        Mail::fake();
        $owner = $this->createUser();
        $invitation = $this->createInvitation(['assigned_to_id' => $owner->id]);
        $now = Carbon::parse('2026-08-15 09:00:00');

        $invitation['submission']->forceFill([
            'last_activity_at' => $now->copy()->subDays(5),
        ])->save();

        $this->artisan('client-setup:reminders', ['--now' => $now->toDateTimeString()])
            ->assertExitCode(0);

        $this->assertSame(2, CrmClientSetupNotification::query()->where('event_key', 'draft_reminder')->count());
        $this->assertSame(2, CrmClientSetupNotification::query()->where('event_key', 'draft_reminder')->where('status', 'sent')->count());

        $this->artisan('client-setup:reminders', ['--now' => $now->toDateTimeString()])
            ->assertExitCode(0);

        $this->assertSame(2, CrmClientSetupNotification::query()->where('event_key', 'draft_reminder')->count());

        $failed = CrmClientSetupNotification::query()->where('event_key', 'draft_reminder')->firstOrFail();
        $failed->forceFill([
            'status' => 'failed',
            'attempts' => 1,
            'available_at' => $now->copy()->subMinute(),
        ])->save();

        $summary = app(ClientSetupNotificationService::class)->retryDue($now);

        $this->assertSame(1, $summary['attempted']);
        $this->assertSame(1, $summary['sent']);
        $this->assertSame('sent', $failed->fresh()->status);
    }

    public function test_approval_sends_client_and_crm_notifications(): void
    {
        Mail::fake();
        $owner = $this->createUser();
        $invitation = $this->createInvitation(['assigned_to_id' => $owner->id]);
        $submission = $invitation['submission']->forceFill(['status' => 'complete_submission'])->save();
        $submission = $invitation['submission']->fresh();

        app(ClientSetupReviewService::class)->changeStatus($submission, 'approved', $owner);

        $this->assertSame(2, CrmClientSetupNotification::query()->where('event_key', 'approved')->count());
        Mail::assertSent(ClientSetupNotificationMail::class, 2);
    }

    private function createInvitation(array $attributes = []): array
    {
        return app(ClientSetupInvitationService::class)->create(array_merge([
            'email' => 'operations-' . uniqid() . '@example.com',
            'contact_name' => 'Operations Client',
        ], $attributes));
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Operations Owner',
            'email' => 'operations-owner-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }
}
