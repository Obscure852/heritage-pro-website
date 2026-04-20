<?php

namespace Tests\Feature\Staff;

use App\Http\Middleware\BlockNonAfricanCountries;
use App\Http\Middleware\EnsureProfileComplete;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\StaffDirectConversation;
use App\Models\StaffUserPresence;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaffDirectMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            BlockNonAfricanCountries::class,
            EnsureProfileComplete::class,
            AuthenticateSession::class,
            VerifyCsrfToken::class,
        ]);

        $this->seedMessagingSettings();
    }

    public function test_launcher_lists_online_staff_and_excludes_current_user(): void
    {
        $currentUser = $this->createStaffUser([
            'firstname' => 'Current',
            'lastname' => 'User',
        ]);
        $otherUser = $this->createStaffUser([
            'firstname' => 'Other',
            'lastname' => 'Colleague',
        ]);

        StaffUserPresence::create([
            'session_id' => 'other-session',
            'user_id' => $otherUser->id,
            'last_seen_at' => now(),
            'last_path' => 'dashboard',
        ]);

        $this->actingAs($currentUser)
            ->postJson(route('staff.messages.heartbeat'), [
                'last_path' => 'dashboard',
            ])
            ->assertOk();

        $response = $this->actingAs($currentUser)
            ->getJson(route('staff.messages.launcher'))
            ->assertOk();

        $this->assertDatabaseHas('staff_user_presence', [
            'user_id' => $currentUser->id,
        ]);
        $this->assertSame([$otherUser->id], $response->json('users.*.id'));
        $this->assertSame(1, $response->json('online_count'));
    }

    public function test_launcher_online_count_excludes_inactive_staff(): void
    {
        StaffUserPresence::query()->delete();

        $currentUser = $this->createStaffUser();
        $inactiveUser = $this->createStaffUser([
            'active' => false,
        ]);

        StaffUserPresence::create([
            'session_id' => 'inactive-session',
            'user_id' => $inactiveUser->id,
            'last_seen_at' => now(),
            'last_path' => 'dashboard',
        ]);

        $this->actingAs($currentUser)
            ->getJson(route('staff.messages.launcher'))
            ->assertOk()
            ->assertJson([
                'online_count' => 0,
                'users' => [],
            ]);
    }

    public function test_starting_same_conversation_reuses_existing_thread(): void
    {
        $sender = $this->createStaffUser();
        $recipient = $this->createStaffUser();

        $firstResponse = $this->actingAs($sender)
            ->postJson(route('staff.messages.start'), [
                'recipient_id' => $recipient->id,
            ])
            ->assertOk();

        $secondResponse = $this->actingAs($sender)
            ->postJson(route('staff.messages.start'), [
                'recipient_id' => $recipient->id,
            ])
            ->assertOk();

        $this->assertSame(
            $firstResponse->json('conversation_id'),
            $secondResponse->json('conversation_id')
        );
        $this->assertDatabaseCount('staff_direct_conversations', 1);
    }

    public function test_recipient_search_returns_online_and_offline_staff_and_excludes_ineligible_users(): void
    {
        $currentUser = $this->createStaffUser([
            'firstname' => 'Team',
            'lastname' => 'Current',
        ]);
        $existingConversationRecipient = $this->createStaffUser([
            'firstname' => 'Team',
            'lastname' => 'Existing',
        ]);
        $onlineRecipient = $this->createStaffUser([
            'firstname' => 'Team',
            'lastname' => 'Online',
        ]);
        $inactiveRecipient = $this->createStaffUser([
            'firstname' => 'Team',
            'lastname' => 'Inactive',
            'active' => false,
        ]);
        $deletedRecipient = $this->createStaffUser([
            'firstname' => 'Team',
            'lastname' => 'Deleted',
        ]);

        $deletedRecipient->delete();

        StaffDirectConversation::create([
            'user_one_id' => min($currentUser->id, $existingConversationRecipient->id),
            'user_two_id' => max($currentUser->id, $existingConversationRecipient->id),
        ])->messages()->create([
            'sender_id' => $existingConversationRecipient->id,
            'body' => 'Existing conversation message',
        ]);

        StaffUserPresence::create([
            'session_id' => 'online-recipient-session',
            'user_id' => $onlineRecipient->id,
            'last_seen_at' => now(),
            'last_path' => 'dashboard',
        ]);

        $response = $this->actingAs($currentUser)
            ->getJson(route('staff.messages.recipients', ['query' => 'Team']))
            ->assertOk();

        $this->assertSame(
            [$existingConversationRecipient->id, $onlineRecipient->id],
            $response->json('users.*.id')
        );
        $this->assertSame(
            false,
            $response->json('users.0.is_online')
        );
        $this->assertNotNull($response->json('users.0.conversation_id'));
        $this->assertSame(
            true,
            $response->json('users.1.is_online')
        );
        $this->assertNull($response->json('users.1.conversation_id'));
    }

    public function test_recipient_search_does_not_expose_hidden_empty_threads_as_existing_conversations(): void
    {
        $currentUser = $this->createStaffUser([
            'firstname' => 'Current',
            'lastname' => 'Searcher',
        ]);
        $emptyThreadRecipient = $this->createStaffUser([
            'firstname' => 'Casey',
            'lastname' => 'NoMessage',
        ]);

        StaffDirectConversation::create([
            'user_one_id' => min($currentUser->id, $emptyThreadRecipient->id),
            'user_two_id' => max($currentUser->id, $emptyThreadRecipient->id),
        ]);

        $response = $this->actingAs($currentUser)
            ->getJson(route('staff.messages.recipients', ['query' => 'Casey']))
            ->assertOk();

        $this->assertSame([$emptyThreadRecipient->id], $response->json('users.*.id'));
        $this->assertNull($response->json('users.0.conversation_id'));
    }

    public function test_only_participants_can_view_a_conversation(): void
    {
        $sender = $this->createStaffUser();
        $recipient = $this->createStaffUser();
        $outsider = $this->createStaffUser();

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
            'last_message_at' => now(),
        ]);

        $this->actingAs($outsider)
            ->get(route('staff.messages.conversation', $conversation))
            ->assertForbidden();
    }

    public function test_only_participants_can_fetch_conversation_updates(): void
    {
        $sender = $this->createStaffUser();
        $recipient = $this->createStaffUser();
        $outsider = $this->createStaffUser();

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);

        $this->actingAs($outsider)
            ->getJson(route('staff.messages.updates', $conversation))
            ->assertForbidden();
    }

    public function test_conversation_updates_return_only_new_messages_in_ascending_order_for_participant(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Update',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Update',
            'lastname' => 'Recipient',
        ]);

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);

        $firstMessage = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'First message',
        ]);
        $secondMessage = $conversation->messages()->create([
            'sender_id' => $recipient->id,
            'body' => 'Second message',
        ]);
        $thirdMessage = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'Third message',
        ]);

        $response = $this->actingAs($recipient)
            ->getJson(route('staff.messages.updates', [
                'conversation' => $conversation,
                'after_message_id' => $firstMessage->id,
            ]))
            ->assertOk();

        $this->assertSame(
            [$secondMessage->id, $thirdMessage->id],
            $response->json('messages.*.id')
        );
        $this->assertSame($thirdMessage->id, $response->json('latest_message_id'));
        $this->assertSame('Second message', $response->json('messages.0.body'));
        $this->assertSame('Third message', $response->json('messages.1.body'));
    }

    public function test_conversation_updates_mark_only_delivered_incoming_messages_as_read(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Live',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Live',
            'lastname' => 'Recipient',
        ]);

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);

        $firstMessage = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'Message one',
        ]);
        $secondMessage = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'Message two',
        ]);
        $thirdMessage = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'Message three',
        ]);

        $this->actingAs($recipient)
            ->getJson(route('staff.messages.updates', [
                'conversation' => $conversation,
                'after_message_id' => $firstMessage->id,
            ]))
            ->assertOk()
            ->assertJson([
                'messages' => [
                    ['id' => $secondMessage->id],
                    ['id' => $thirdMessage->id],
                ],
            ]);

        $conversation->refresh();

        $lastReadMessageId = $conversation->user_two_id === $recipient->id
            ? $conversation->user_two_last_read_message_id
            : $conversation->user_one_last_read_message_id;

        $this->assertSame($thirdMessage->id, $lastReadMessageId);

        $fourthMessage = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'Message four',
        ]);

        $this->actingAs($recipient)
            ->getJson(route('staff.messages.unread-count'))
            ->assertOk()
            ->assertJson([
                'count' => 1,
            ]);

        $this->assertDatabaseHas('staff_direct_messages', [
            'id' => $fourthMessage->id,
            'read_at' => null,
        ]);
    }

    public function test_conversation_updates_include_other_participant_last_read_message_id_for_seen_receipts(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Seen',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Seen',
            'lastname' => 'Recipient',
        ]);

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'Please confirm you saw this.',
        ]);

        $conversation->markAsReadFor($recipient, $message->id);

        $this->actingAs($sender)
            ->getJson(route('staff.messages.updates', [
                'conversation' => $conversation,
                'after_message_id' => $message->id,
            ]))
            ->assertOk()
            ->assertJson([
                'messages' => [],
                'other_participant_last_read_message_id' => $message->id,
            ]);
    }

    public function test_unread_count_changes_after_reply_and_conversation_open(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Alice',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Bob',
            'lastname' => 'Recipient',
        ]);

        $conversationId = $this->actingAs($sender)
            ->postJson(route('staff.messages.start'), [
                'recipient_id' => $recipient->id,
            ])
            ->assertOk()
            ->json('conversation_id');

        $conversation = StaffDirectConversation::findOrFail($conversationId);

        $this->actingAs($sender)
            ->post(route('staff.messages.reply', $conversation), [
                'body' => 'Hello Bob',
            ])
            ->assertRedirect(route('staff.messages.conversation', $conversation));

        $this->actingAs($recipient)
            ->getJson(route('staff.messages.unread-count'))
            ->assertOk()
            ->assertJson([
                'count' => 1,
            ]);

        $this->actingAs($recipient)
            ->get(route('staff.messages.conversation', $conversation))
            ->assertOk();

        $this->actingAs($recipient)
            ->getJson(route('staff.messages.unread-count'))
            ->assertOk()
            ->assertJson([
                'count' => 0,
            ]);
    }

    public function test_json_reply_returns_created_message_payload_without_redirect(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Json',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Json',
            'lastname' => 'Recipient',
        ]);

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);

        $response = $this->actingAs($sender)
            ->postJson(route('staff.messages.reply', $conversation), [
                'body' => 'Live reply payload',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => [
                    'body' => 'Live reply payload',
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->full_name,
                    'is_sent_by_current_user' => true,
                    'is_seen_by_other_participant' => false,
                ],
            ]);

        $this->assertSame(
            $response->json('message.id'),
            $response->json('latest_message_id')
        );
    }

    public function test_conversation_page_shows_seen_receipt_for_read_sent_messages(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Receipt',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Receipt',
            'lastname' => 'Recipient',
        ]);

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'This one should show as seen.',
        ]);

        $conversation->markAsReadFor($recipient, $message->id);

        $this->actingAs($sender)
            ->get(route('staff.messages.conversation', $conversation))
            ->assertOk()
            ->assertSee('data-receipt-status="seen"', false)
            ->assertSee('Seen');
    }

    public function test_conversation_opens_on_latest_messages_page(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Alice',
            'lastname' => 'Latest',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Bob',
            'lastname' => 'History',
        ]);

        $conversationId = $this->actingAs($sender)
            ->postJson(route('staff.messages.start'), [
                'recipient_id' => $recipient->id,
            ])
            ->assertOk()
            ->json('conversation_id');

        $conversation = StaffDirectConversation::findOrFail($conversationId);

        foreach (range(1, 55) as $index) {
            $conversation->messages()->create([
                'sender_id' => $index % 2 === 0 ? $sender->id : $recipient->id,
                'body' => sprintf('Message #%03d', $index),
                'created_at' => now()->addSeconds($index),
                'updated_at' => now()->addSeconds($index),
            ]);
        }

        $response = $this->actingAs($sender)
            ->get(route('staff.messages.conversation', $conversation))
            ->assertOk();

        $response->assertSee('Message #055');
        $response->assertDontSee('Message #001');
    }

    public function test_conversation_updates_report_other_participant_presence(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Presence',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Presence',
            'lastname' => 'Recipient',
        ]);

        StaffUserPresence::create([
            'session_id' => 'presence-sender-session',
            'user_id' => $sender->id,
            'last_seen_at' => now(),
            'last_path' => '/staff/messages',
        ]);

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);

        $this->actingAs($recipient)
            ->getJson(route('staff.messages.updates', $conversation))
            ->assertOk()
            ->assertJson([
                'participant_online' => true,
                'participant_last_seen_label' => 'Active now',
            ]);
    }

    public function test_opening_a_thread_without_sending_does_not_add_it_to_inbox(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Silent',
            'lastname' => 'Starter',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Quiet',
            'lastname' => 'Recipient',
        ]);

        $this->actingAs($sender)
            ->postJson(route('staff.messages.start'), [
                'recipient_id' => $recipient->id,
            ])
            ->assertOk();

        $this->actingAs($sender)
            ->get(route('staff.messages.inbox'))
            ->assertOk()
            ->assertSee('No conversations yet. Click New Message to search any staff member, or start from the online staff list.');

        $this->actingAs($recipient)
            ->get(route('staff.messages.inbox'))
            ->assertOk()
            ->assertSee('No conversations yet. Click New Message to search any staff member, or start from the online staff list.');
    }

    public function test_quick_send_to_offline_staff_creates_message_and_thread_in_both_inboxes(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Quick',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Offline',
            'lastname' => 'Recipient',
        ]);

        $response = $this->actingAs($sender)
            ->postJson(route('staff.messages.start'), [
                'recipient_id' => $recipient->id,
                'body' => 'Hello even while you are offline.',
            ])
            ->assertOk()
            ->assertJson([
                'message_sent' => true,
            ]);

        $conversation = StaffDirectConversation::findOrFail($response->json('conversation_id'));

        $this->assertDatabaseHas('staff_direct_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => 'Hello even while you are offline.',
        ]);

        $this->actingAs($sender)
            ->get(route('staff.messages.inbox'))
            ->assertOk()
            ->assertSee('Offline Recipient')
            ->assertSee('Hello even while you are offline.');

        $this->actingAs($recipient)
            ->get(route('staff.messages.inbox'))
            ->assertOk()
            ->assertSee('Quick Sender')
            ->assertSee('Hello even while you are offline.');
    }

    public function test_quick_send_validation_failure_does_not_leave_an_empty_conversation(): void
    {
        $sender = $this->createStaffUser();
        $recipient = $this->createStaffUser();

        $this->actingAs($sender)
            ->postJson(route('staff.messages.start'), [
                'recipient_id' => $recipient->id,
                'body' => '   ',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'body',
            ]);

        $this->assertDatabaseCount('staff_direct_conversations', 0);
        $this->assertDatabaseCount('staff_direct_messages', 0);
    }

    public function test_quick_send_reopens_archived_conversation_for_both_participants(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Archived',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Archived',
            'lastname' => 'Recipient',
        ]);

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
            'is_archived_by_user_one' => true,
            'is_archived_by_user_two' => true,
        ]);

        $conversation->messages()->create([
            'sender_id' => $recipient->id,
            'body' => 'Older archived message.',
        ]);

        $this->actingAs($sender)
            ->postJson(route('staff.messages.start'), [
                'recipient_id' => $recipient->id,
                'body' => 'Reopening this conversation.',
            ])
            ->assertOk()
            ->assertJson([
                'conversation_id' => $conversation->id,
                'message_sent' => true,
            ]);

        $conversation->refresh();

        $this->assertFalse($conversation->is_archived_by_user_one);
        $this->assertFalse($conversation->is_archived_by_user_two);
        $this->assertDatabaseHas('staff_direct_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => 'Reopening this conversation.',
        ]);
    }

    public function test_soft_deleted_participant_still_renders_existing_conversation_history(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Visible',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Archived',
            'lastname' => 'Colleague',
        ]);

        $conversationId = $this->actingAs($sender)
            ->postJson(route('staff.messages.start'), [
                'recipient_id' => $recipient->id,
            ])
            ->assertOk()
            ->json('conversation_id');

        $conversation = StaffDirectConversation::findOrFail($conversationId);

        $this->actingAs($sender)
            ->post(route('staff.messages.reply', $conversation), [
                'body' => 'History should remain visible.',
            ])
            ->assertRedirect(route('staff.messages.conversation', $conversation));

        $recipientName = $recipient->full_name;
        $recipient->delete();

        $this->actingAs($sender)
            ->get(route('staff.messages.inbox'))
            ->assertOk()
            ->assertSee($recipientName);

        $this->actingAs($sender)
            ->get(route('staff.messages.conversation', $conversation))
            ->assertOk()
            ->assertSee($recipientName)
            ->assertSee('History should remain visible.');
    }

    public function test_messages_sent_after_read_snapshot_stay_unread_even_with_same_second_timestamp(): void
    {
        $sender = $this->createStaffUser([
            'firstname' => 'Timing',
            'lastname' => 'Sender',
        ]);
        $recipient = $this->createStaffUser([
            'firstname' => 'Timing',
            'lastname' => 'Recipient',
        ]);

        $conversation = StaffDirectConversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);

        $sharedTimestamp = now()->startOfSecond();

        $firstMessage = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'First visible message',
        ]);
        $firstMessage->forceFill([
            'created_at' => $sharedTimestamp,
            'updated_at' => $sharedTimestamp,
        ])->saveQuietly();

        $conversation->refresh();

        $this->actingAs($recipient)
            ->get(route('staff.messages.conversation', $conversation))
            ->assertOk();

        $conversation->refresh();

        $secondMessage = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => 'Second same-second message',
        ]);
        $secondMessage->forceFill([
            'created_at' => $sharedTimestamp,
            'updated_at' => $sharedTimestamp,
        ])->saveQuietly();

        $conversation->refresh();

        $this->assertSame($firstMessage->id, $conversation->user_two_id === $recipient->id
            ? $conversation->user_two_last_read_message_id
            : $conversation->user_one_last_read_message_id);

        $this->actingAs($recipient)
            ->getJson(route('staff.messages.unread-count'))
            ->assertOk()
            ->assertJson([
                'count' => 1,
            ]);
    }

    public function test_direct_messaging_routes_are_blocked_when_feature_is_disabled(): void
    {
        $this->seedMessagingSettings([
            'features.staff_direct_messages_enabled' => '0',
        ]);

        $user = $this->createStaffUser();

        $this->actingAs($user)
            ->getJson(route('staff.messages.unread-count'))
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Staff direct messaging is disabled in Communications Setup.',
            ]);
    }

    public function test_launcher_can_be_disabled_without_blocking_inbox_access(): void
    {
        $this->seedMessagingSettings([
            'features.staff_direct_messages_enabled' => '1',
            'features.staff_presence_launcher_enabled' => '0',
        ]);

        $user = $this->createStaffUser();
        $otherUser = $this->createStaffUser([
            'firstname' => 'Visible',
            'lastname' => 'Inbox',
        ]);

        StaffUserPresence::create([
            'session_id' => 'visible-inbox-session',
            'user_id' => $otherUser->id,
            'last_seen_at' => now(),
            'last_path' => 'dashboard',
        ]);

        $this->actingAs($user)
            ->postJson(route('staff.messages.heartbeat'), [
                'last_path' => 'dashboard',
            ])
            ->assertOk();

        $this->actingAs($user)
            ->get(route('staff.messages.inbox'))
            ->assertOk()
            ->assertSee('Visible Inbox')
            ->assertSee('topbar launcher is disabled', false);

        $this->actingAs($user)
            ->getJson(route('staff.messages.launcher'))
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Staff presence launcher is disabled in Communications Setup.',
            ]);
    }

    protected function createStaffUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'firstname' => 'Staff',
            'lastname' => 'User' . random_int(1000, 9999),
            'email' => 'staff' . random_int(1000, 9999) . '@example.com',
            'username' => 'staff' . random_int(1000, 9999),
            'avatar' => null,
            'active' => true,
            'password' => bcrypt('password'),
        ], $attributes));
    }

    protected function seedMessagingSettings(array $overrides = []): void
    {
        $settings = [
            'features.staff_direct_messages_enabled' => [
                'value' => '1',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable Staff Direct Messaging',
                'description' => 'Allow or block internal staff direct messaging system-wide.',
                'validation_rules' => 'required|boolean',
                'display_order' => 900,
            ],
            'features.staff_presence_launcher_enabled' => [
                'value' => '1',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable Online Staff Launcher',
                'description' => 'Show or hide the quiet online-staff launcher in the staff topbar.',
                'validation_rules' => 'required|boolean',
                'display_order' => 901,
            ],
            'internal_messaging.online_window_minutes' => [
                'value' => '2',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Online Window (minutes)',
                'description' => 'How long a staff heartbeat remains valid before the user appears offline.',
                'validation_rules' => 'required|integer|min:1|max:60',
                'display_order' => 1,
            ],
            'internal_messaging.launcher_poll_seconds' => [
                'value' => '45',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Launcher Poll Interval (seconds)',
                'description' => 'How often the quiet launcher refreshes presence and unread counts.',
                'validation_rules' => 'required|integer|min:15|max:300',
                'display_order' => 2,
            ],
            'internal_messaging.conversation_poll_seconds' => [
                'value' => '5',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Conversation Poll Interval (seconds)',
                'description' => 'How often an open direct-message conversation checks for new messages.',
                'validation_rules' => 'required|integer|min:3|max:30',
                'display_order' => 3,
            ],
        ];

        foreach ($overrides as $key => $value) {
            if (isset($settings[$key])) {
                $settings[$key]['value'] = (string) $value;
            }
        }

        foreach ($settings as $key => $setting) {
            DB::table('s_m_s_api_settings')->updateOrInsert(
                ['key' => $key],
                array_merge($setting, [
                    'key' => $key,
                    'is_editable' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        app(SettingsService::class)->refresh();
    }
}
