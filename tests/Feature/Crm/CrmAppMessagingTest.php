<?php

namespace Tests\Feature\Crm;

use App\Models\CrmUserDepartment;
use App\Models\DiscussionCampaign;
use App\Models\DiscussionThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmAppMessagingTest extends TestCase
{
    use RefreshDatabase;

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
