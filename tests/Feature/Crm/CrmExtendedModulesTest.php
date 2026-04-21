<?php

namespace Tests\Feature\Crm;

use App\Models\DiscussionMessage;
use App\Models\DiscussionThread;
use App\Models\DevelopmentRequest;
use App\Models\Integration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmExtendedModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_integration_profiles(): void
    {
        $admin = $this->createUser([
            'email' => 'integrations-admin@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('crm.integrations.store'), [
                'owner_id' => $admin->id,
                'name' => 'Unified School API',
                'kind' => 'school_api',
                'status' => 'active',
                'school_code' => 'UNIFIED',
                'base_url' => 'https://api.example.test',
                'auth_type' => 'Bearer token',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('crm_integrations', [
            'name' => 'Unified School API',
            'kind' => 'school_api',
            'status' => 'active',
        ]);
    }

    public function test_users_can_log_development_requests(): void
    {
        $rep = $this->createUser([
            'email' => 'rep@example.com',
            'role' => 'rep',
        ]);

        $this->actingAs($rep)
            ->post(route('crm.dev.store'), [
                'title' => 'Improve attendance export',
                'description' => 'Client needs a cleaner class-level attendance export.',
                'priority' => 'high',
                'status' => 'backlog',
                'target_module' => 'Attendance',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('crm_development_requests', [
            'title' => 'Improve attendance export',
            'priority' => 'high',
            'owner_id' => $rep->id,
        ]);
    }

    public function test_users_can_start_app_discussions(): void
    {
        $sender = $this->createUser([
            'email' => 'sender@example.com',
            'role' => 'rep',
        ]);

        $recipient = $this->createUser([
            'email' => 'recipient@example.com',
            'role' => 'manager',
        ]);

        $this->actingAs($sender)
            ->post(route('crm.discussions.store'), [
                'subject' => 'Customer follow-up',
                'channel' => 'app',
                'recipient_user_id' => $recipient->id,
                'body' => 'Please review the renewal conversation before tomorrow.',
            ])
            ->assertRedirect();

        $thread = DiscussionThread::query()->first();
        $message = DiscussionMessage::query()->first();

        $this->assertNotNull($thread);
        $this->assertNotNull($message);
        $this->assertSame('Customer follow-up', $thread->subject);
        $this->assertSame('app', $thread->channel);
        $this->assertSame('sent', $thread->delivery_status);
        $this->assertSame($sender->id, $message->user_id);
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
