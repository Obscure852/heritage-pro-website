<?php

namespace Tests\Feature\Crm;

use App\Models\CrmUserPresence;
use App\Models\DiscussionMessage;
use App\Models\DiscussionThread;
use App\Models\DiscussionThreadParticipant;
use App\Models\Lead;
use App\Models\User;
use App\Services\Crm\DiscussionDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmShellUtilitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_the_new_crm_shell_utilities(): void
    {
        $admin = $this->createUser([
            'email' => 'shell-admin@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('Global Search...')
            ->assertSee('Online CRM Users')
            ->assertSee('crm-presence-sound-toggle', false)
            ->assertSee('crm-discussion-sound', false)
            ->assertSee('crm-presence-unread-badge', false)
            ->assertSee('crm-sidebar-discussions-badge', false)
            ->assertSee('Modules')
            ->assertSee('Public site');

        $this->actingAs($admin)
            ->get(route('crm.leads.index'))
            ->assertOk()
            ->assertSee('Apply filters')
            ->assertSee(route('crm.leads.create'), false);
    }

    public function test_global_search_returns_grouped_results_and_respects_rep_scope(): void
    {
        $rep = $this->createUser([
            'email' => 'rep-search@example.com',
            'role' => 'rep',
        ]);

        $otherRep = $this->createUser([
            'email' => 'other-rep-search@example.com',
            'role' => 'rep',
        ]);

        Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Heritage South Search',
            'status' => 'active',
        ]);

        Lead::query()->create([
            'owner_id' => $otherRep->id,
            'company_name' => 'Heritage North Search',
            'status' => 'active',
        ]);

        $this->actingAs($rep)
            ->getJson(route('crm.search', ['q' => 'Heritage']))
            ->assertOk()
            ->assertJsonFragment(['label' => 'Leads'])
            ->assertJsonFragment(['label' => 'Heritage South Search'])
            ->assertJsonMissing(['label' => 'Heritage North Search']);
    }

    public function test_module_launcher_is_role_aware(): void
    {
        $admin = $this->createUser([
            'email' => 'launcher-admin@example.com',
            'role' => 'admin',
        ]);

        $manager = $this->createUser([
            'email' => 'launcher-manager@example.com',
            'role' => 'manager',
        ]);

        $this->actingAs($admin)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee(route('crm.users.index'), false)
            ->assertSee(route('crm.settings.index'), false);

        $this->actingAs($manager)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertDontSee(route('crm.users.index'), false)
            ->assertDontSee(route('crm.settings.index'), false);
    }

    public function test_presence_heartbeat_and_launcher_only_show_online_crm_users(): void
    {
        $admin = $this->createUser([
            'email' => 'presence-admin@example.com',
            'role' => 'admin',
        ]);

        $manager = $this->createUser([
            'email' => 'presence-manager@example.com',
            'role' => 'manager',
            'name' => 'Online Manager',
        ]);

        $inactive = $this->createUser([
            'email' => 'inactive@example.com',
            'role' => 'rep',
            'name' => 'Inactive Rep',
            'active' => false,
        ]);

        $this->actingAs($manager)
            ->postJson(route('crm.presence.heartbeat'), [
                'path' => '/crm',
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        CrmUserPresence::query()->create([
            'user_id' => $inactive->id,
            'last_seen_at' => now(),
            'last_path' => '/crm',
        ]);

        $this->actingAs($admin)
            ->getJson(route('crm.presence.launcher'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Online Manager'])
            ->assertJsonMissing(['name' => 'Inactive Rep']);

        $this->assertDatabaseHas('crm_user_presence', [
            'user_id' => $manager->id,
        ]);
    }

    public function test_presence_unread_endpoint_returns_threads_from_all_discussion_channels_for_the_current_user(): void
    {
        $admin = $this->createUser([
            'email' => 'unread-admin@example.com',
            'role' => 'admin',
            'name' => 'Unread Admin',
        ]);

        $otherUser = $this->createUser([
            'email' => 'unread-other@example.com',
            'role' => 'manager',
            'name' => 'Unread Manager',
        ]);

        $thread = DiscussionThread::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $otherUser->id,
            'recipient_user_id' => $admin->id,
            'direct_participant_key' => collect([$admin->id, $otherUser->id])->sort()->implode(':'),
            'subject' => 'Unread direct thread',
            'channel' => 'app',
            'kind' => 'direct',
            'delivery_status' => 'sent',
            'status' => 'sent',
            'last_message_at' => now(),
        ]);

        DiscussionThreadParticipant::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $admin->id,
            'role' => 'member',
            'last_read_at' => null,
        ]);

        DiscussionThreadParticipant::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $otherUser->id,
            'role' => 'owner',
            'last_read_at' => now(),
        ]);

        $appMessage = DiscussionMessage::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $otherUser->id,
            'direction' => 'outbound',
            'channel' => 'app',
            'body' => 'Please check this update.',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);

        $emailThread = DiscussionThread::query()->create([
            'owner_id' => $otherUser->id,
            'initiated_by_id' => $otherUser->id,
            'recipient_user_id' => $admin->id,
            'subject' => 'Email renewal reminder',
            'channel' => 'email',
            'kind' => 'external_direct',
            'recipient_email' => $admin->email,
            'delivery_status' => 'queued',
            'status' => 'queued',
            'last_message_at' => now()->addMinute(),
        ]);

        $emailMessage = DiscussionMessage::query()->create([
            'thread_id' => $emailThread->id,
            'user_id' => $otherUser->id,
            'direction' => 'outbound',
            'channel' => 'email',
            'body' => 'Please review the renewal numbers before close of day.',
            'delivery_status' => 'queued',
            'sent_at' => now()->addMinute(),
        ]);

        $whatsAppThread = DiscussionThread::query()->create([
            'owner_id' => $otherUser->id,
            'initiated_by_id' => $otherUser->id,
            'recipient_user_id' => $admin->id,
            'subject' => 'WhatsApp handoff',
            'channel' => 'whatsapp',
            'kind' => 'external_direct',
            'recipient_phone' => '+26771234567',
            'delivery_status' => 'pending_integration',
            'status' => 'queued',
            'last_message_at' => now()->addMinutes(2),
        ]);

        $whatsAppMessage = DiscussionMessage::query()->create([
            'thread_id' => $whatsAppThread->id,
            'user_id' => $otherUser->id,
            'direction' => 'outbound',
            'channel' => 'whatsapp',
            'body' => 'Client asked for the latest admissions pricing sheet.',
            'delivery_status' => 'pending_integration',
            'sent_at' => now()->addMinutes(2),
        ]);

        $this->actingAs($admin)
            ->getJson(route('crm.presence.unread-count'))
            ->assertOk()
            ->assertJsonFragment(['count' => 3])
            ->assertJsonPath('discussion_sound_enabled', true)
            ->assertJsonPath('channel_counts.app', 1)
            ->assertJsonPath('channel_counts.email', 1)
            ->assertJsonPath('channel_counts.whatsapp', 1)
            ->assertJsonFragment([
                'id' => $thread->id,
                'thread_id' => $thread->id,
                'message_id' => $appMessage->id,
                'label' => 'Unread Manager',
                'url' => route('crm.discussions.app.threads.show', $thread),
                'activity_at' => optional($appMessage->sent_at)->toIso8601String(),
            ])
            ->assertJsonFragment([
                'id' => $emailThread->id,
                'thread_id' => $emailThread->id,
                'message_id' => $emailMessage->id,
                'label' => 'Email renewal reminder',
                'channel' => 'email',
                'channel_label' => 'Email',
                'url' => route('crm.discussions.email.direct.show', $emailThread),
                'activity_at' => optional($emailMessage->sent_at)->toIso8601String(),
            ])
            ->assertJsonFragment([
                'id' => $whatsAppThread->id,
                'thread_id' => $whatsAppThread->id,
                'message_id' => $whatsAppMessage->id,
                'label' => 'WhatsApp handoff',
                'channel' => 'whatsapp',
                'channel_label' => 'WhatsApp',
                'url' => route('crm.discussions.whatsapp.direct.show', $whatsAppThread),
                'activity_at' => optional($whatsAppMessage->sent_at)->toIso8601String(),
            ]);
    }

    public function test_presence_discussion_sound_preference_update_only_changes_the_current_user(): void
    {
        $actingUser = $this->createUser([
            'email' => 'sound-acting@example.com',
            'role' => 'admin',
            'crm_discussion_sound_enabled' => true,
        ]);

        $otherUser = $this->createUser([
            'email' => 'sound-other@example.com',
            'role' => 'manager',
            'crm_discussion_sound_enabled' => true,
        ]);

        $this->actingAs($actingUser)
            ->patchJson(route('crm.presence.discussion-sound.update'), [
                'crm_discussion_sound_enabled' => false,
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'discussion_sound_enabled' => false,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $actingUser->id,
            'crm_discussion_sound_enabled' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $otherUser->id,
            'crm_discussion_sound_enabled' => true,
        ]);
    }

    public function test_sent_whatsapp_thread_to_a_crm_user_creates_a_persistent_unread_alert_until_opened(): void
    {
        $sender = $this->createUser([
            'email' => 'sender-alert@example.com',
            'role' => 'admin',
            'name' => 'Sender Admin',
        ]);

        $recipient = $this->createUser([
            'email' => 'recipient-alert@example.com',
            'role' => 'manager',
            'name' => 'Recipient Manager',
            'phone' => '+26771000000',
        ]);

        $service = app(DiscussionDeliveryService::class);
        $thread = $service->saveExternalDirectDraft($sender, 'whatsapp', [
            'subject' => 'Admissions handoff',
            'recipient_type' => 'user',
            'recipient_user_id' => $recipient->id,
            'body' => 'Please pick up the WhatsApp follow-up from this point.',
            'attachments' => [],
        ]);

        $thread = $service->sendExternalDraft($thread, $sender);

        $this->assertDatabaseHas('crm_discussion_thread_participants', [
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'role' => 'owner',
        ]);

        $this->assertDatabaseHas('crm_discussion_thread_participants', [
            'thread_id' => $thread->id,
            'user_id' => $recipient->id,
            'role' => 'member',
        ]);

        $this->actingAs($recipient)
            ->getJson(route('crm.presence.unread-count'))
            ->assertOk()
            ->assertJsonFragment(['count' => 1])
            ->assertJsonPath('channel_counts.app', 0)
            ->assertJsonPath('channel_counts.email', 0)
            ->assertJsonPath('channel_counts.whatsapp', 1)
            ->assertJsonFragment([
                'id' => $thread->id,
                'label' => 'Admissions handoff',
                'channel' => 'whatsapp',
                'url' => route('crm.discussions.whatsapp.direct.show', $thread),
            ]);

        $this->actingAs($recipient)
            ->get(route('crm.discussions.whatsapp.direct.show', $thread))
            ->assertOk();

        $this->actingAs($recipient)
            ->getJson(route('crm.presence.unread-count'))
            ->assertOk()
            ->assertJsonFragment(['count' => 0])
            ->assertJsonPath('channel_counts.app', 0)
            ->assertJsonPath('channel_counts.email', 0)
            ->assertJsonPath('channel_counts.whatsapp', 0);
    }

    public function test_external_discussion_reply_rejects_mention_payloads(): void
    {
        $sender = $this->createUser([
            'email' => 'email-sender@example.com',
            'role' => 'admin',
            'name' => 'Email Sender',
        ]);

        $recipient = $this->createUser([
            'email' => 'email-recipient@example.com',
            'role' => 'manager',
            'name' => 'Email Recipient',
        ]);

        $mentionedUser = $this->createUser([
            'email' => 'email-mentioned@example.com',
            'role' => 'rep',
            'name' => 'Mention Target',
        ]);

        $thread = DiscussionThread::query()->create([
            'owner_id' => $sender->id,
            'initiated_by_id' => $sender->id,
            'recipient_user_id' => $recipient->id,
            'subject' => 'Outbound email thread',
            'channel' => 'email',
            'kind' => 'external_direct',
            'recipient_email' => $recipient->email,
            'delivery_status' => 'sent',
            'status' => 'sent',
            'last_message_at' => now(),
        ]);

        DiscussionThreadParticipant::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'role' => 'owner',
            'last_read_at' => now(),
        ]);

        DiscussionThreadParticipant::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $recipient->id,
            'role' => 'member',
            'last_read_at' => null,
        ]);

        DiscussionMessage::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'direction' => 'outbound',
            'channel' => 'email',
            'body' => 'Initial external email message.',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($sender)
            ->from(route('crm.discussions.email.direct.show', $thread))
            ->post(route('crm.discussions.email.direct.reply', $thread), [
                'body' => 'Follow up with @[Mention Target] included.',
                'mention_user_ids' => [$mentionedUser->id],
            ]);

        $response->assertRedirect(route('crm.discussions.email.direct.show', $thread));
        $response->assertSessionHasErrors(['mention_user_ids']);
        $this->assertDatabaseCount('crm_discussion_messages', 1);
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
