<?php

namespace Tests\Feature\Crm;

use App\Models\CrmUserPresence;
use App\Models\DiscussionMessage;
use App\Models\DiscussionThread;
use App\Models\DiscussionThreadParticipant;
use App\Models\Lead;
use App\Models\User;
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

    public function test_presence_unread_endpoint_returns_in_app_threads_for_the_current_user(): void
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

        DiscussionMessage::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $otherUser->id,
            'direction' => 'outbound',
            'channel' => 'app',
            'body' => 'Please check this update.',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson(route('crm.presence.unread-count'))
            ->assertOk()
            ->assertJsonFragment(['count' => 1])
            ->assertJsonFragment([
                'id' => $thread->id,
                'label' => 'Unread Manager',
                'url' => route('crm.discussions.app.threads.show', $thread),
            ]);
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
