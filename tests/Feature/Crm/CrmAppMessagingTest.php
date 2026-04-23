<?php

namespace Tests\Feature\Crm;

use App\Models\CrmUserDepartment;
use App\Models\DiscussionCampaign;
use App\Models\DiscussionThread;
use App\Models\DiscussionThreadParticipant;
use App\Models\User;
use App\Services\Crm\DiscussionDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAppMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_app_message_page_uses_custom_users_copy(): void
    {
        $sender = $this->createUser([
            'email' => 'app-group-copy@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($sender)
            ->get(route('crm.discussions.app.bulk.create'))
            ->assertOk()
            ->assertSee('Custom users')
            ->assertSee('Search internal users by name or email')
            ->assertDontSee('Custom contacts');
    }

    public function test_bulk_app_message_creates_a_group_thread_from_selected_users_and_departments(): void
    {
        $sender = $this->createUser([
            'email' => 'app-group-owner@example.com',
            'role' => 'admin',
        ]);

        $department = CrmUserDepartment::query()->create([
            'name' => 'Finance',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $departmentUser = $this->createUser([
            'email' => 'app-group-department@example.com',
            'role' => 'manager',
            'department_id' => $department->id,
        ]);

        $duplicateDepartmentUser = $this->createUser([
            'email' => 'app-group-duplicate@example.com',
            'role' => 'rep',
            'department_id' => $department->id,
        ]);

        $customUser = $this->createUser([
            'email' => 'app-group-custom@example.com',
            'role' => 'rep',
        ]);

        $response = $this->actingAs($sender)->post(route('crm.discussions.app.bulk.store'), [
            'subject' => 'Finance approvals',
            'body' => 'Please use this thread for approval updates and attachments.',
            'notes' => 'Created from the new app bulk flow.',
            'recipient_user_ids' => [$duplicateDepartmentUser->id, $customUser->id],
            'department_ids' => [$department->id],
            'intent' => 'send',
        ]);

        $campaign = DiscussionCampaign::query()->firstOrFail();
        $thread = DiscussionThread::query()
            ->with(['participants', 'messages'])
            ->where('channel', 'app')
            ->where('kind', 'group')
            ->firstOrFail();

        $response->assertRedirect(route('crm.discussions.app.threads.show', $thread));

        $this->assertSame($thread->id, $campaign->thread_id);
        $this->assertSame('sent', $campaign->status);
        $this->assertSame([$department->id], $campaign->audience_snapshot['requested']['department_ids'] ?? []);
        $this->assertCount(1, $campaign->audience_snapshot['departments'] ?? []);
        $this->assertCount(1, $thread->messages);
        $this->assertSame('Please use this thread for approval updates and attachments.', $thread->messages->first()->body);

        $participantIds = $thread->participants
            ->pluck('user_id')
            ->map(fn ($value) => (int) $value)
            ->sort()
            ->values()
            ->all();

        $this->assertSame(
            collect([$sender->id, $departmentUser->id, $duplicateDepartmentUser->id, $customUser->id])->sort()->values()->all(),
            $participantIds
        );

        $this->assertCount(3, $campaign->recipients);
    }

    public function test_bulk_app_message_rejects_non_crm_users(): void
    {
        $sender = $this->createUser([
            'email' => 'app-group-validator@example.com',
            'role' => 'admin',
        ]);

        $outsideUser = $this->createUser([
            'email' => 'outside-app-user@example.com',
            'role' => 'teacher',
        ]);

        $response = $this->actingAs($sender)->from(route('crm.discussions.app.bulk.create'))
            ->post(route('crm.discussions.app.bulk.store'), [
                'subject' => 'Invalid thread',
                'body' => 'This should fail because the recipient is not a CRM user.',
                'recipient_user_ids' => [$outsideUser->id],
                'intent' => 'send',
            ]);

        $response->assertRedirect(route('crm.discussions.app.bulk.create'));
        $response->assertSessionHasErrors(['recipient_user_ids.0']);
        $this->assertDatabaseCount('crm_discussion_campaigns', 0);
    }

    public function test_company_chat_mentions_create_unread_alerts_and_render_highlighted_mentions(): void
    {
        $sender = $this->createUser([
            'email' => 'app-company-owner@example.com',
            'role' => 'admin',
            'name' => 'Sender Admin',
        ]);

        $mentionedUser = $this->createUser([
            'email' => 'app-company-mentioned@example.com',
            'role' => 'manager',
            'name' => 'Mentioned Manager',
        ]);

        $response = $this->actingAs($sender)->post(route('crm.discussions.app.company-chat.messages.store'), [
            'body' => 'Please coordinate with @Mentioned Manager on the rollout.',
            'mention_user_ids' => [$mentionedUser->id],
        ]);

        $thread = DiscussionThread::query()
            ->where('channel', 'app')
            ->where('kind', 'company_chat')
            ->firstOrFail();

        $message = $thread->messages()->latest('id')->firstOrFail();

        $response->assertRedirect(route('crm.discussions.app.threads.show', $thread));
        $this->assertDatabaseHas('crm_discussion_message_mentions', [
            'message_id' => $message->id,
            'user_id' => $mentionedUser->id,
        ]);
        $this->assertDatabaseHas('crm_discussion_thread_participants', [
            'thread_id' => $thread->id,
            'user_id' => $mentionedUser->id,
            'role' => 'member',
        ]);

        $this->actingAs($mentionedUser)
            ->getJson(route('crm.presence.unread-count'))
            ->assertOk()
            ->assertJsonFragment(['count' => 1])
            ->assertJsonPath('channel_counts.app', 1)
            ->assertJsonPath('channel_counts.email', 0)
            ->assertJsonPath('channel_counts.whatsapp', 0)
            ->assertJsonFragment([
                'label' => 'Company Chat',
                'activity_reason' => 'mentioned_you',
                'activity_reason_label' => 'Mentioned you',
            ]);

        $this->actingAs($mentionedUser)
            ->get(route('crm.discussions.app.threads.show', $thread))
            ->assertOk()
            ->assertSee('@Mentioned Manager')
            ->assertSee('crm-discussion-mention is-personal', false);

        $this->actingAs($sender)
            ->get(route('crm.discussions.app.threads.show', $thread))
            ->assertOk()
            ->assertSee('Seen by 1');
    }

    public function test_direct_messages_render_seen_state_after_counterpart_reads(): void
    {
        $sender = $this->createUser([
            'email' => 'app-direct-seen-sender@example.com',
            'role' => 'admin',
            'name' => 'Direct Sender',
        ]);

        $recipient = $this->createUser([
            'email' => 'app-direct-seen-recipient@example.com',
            'role' => 'manager',
            'name' => 'Direct Recipient',
        ]);

        $service = app(DiscussionDeliveryService::class);
        $thread = $service->startOrResumeDirectThread($sender, $recipient);
        $message = $service->storeAppMessage($thread, $sender, 'Checking CRM seen state.');

        DiscussionThreadParticipant::query()
            ->where('thread_id', $thread->id)
            ->where('user_id', $recipient->id)
            ->update([
                'last_read_at' => $message->sent_at->copy()->addMinute(),
            ]);

        $this->actingAs($sender)
            ->get(route('crm.discussions.app.threads.show', $thread))
            ->assertOk()
            ->assertSee('Seen');
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'CRM User',
            'email' => 'crm-user-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'rep',
            'active' => true,
        ], $attributes));
    }
}
