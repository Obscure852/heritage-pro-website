<?php

namespace Tests\Feature\Crm;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmUsersAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_crm_users(): void
    {
        $admin = $this->createUser([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('crm.users.store'), [
                'name' => 'Sales Rep',
                'email' => 'sales.rep@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'rep',
                'active' => '1',
            ])
            ->assertRedirect(route('crm.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'sales.rep@example.com',
            'role' => 'rep',
            'active' => true,
        ]);
    }

    public function test_manager_cannot_access_user_or_settings_admin_areas(): void
    {
        $manager = $this->createUser([
            'email' => 'manager@example.com',
            'role' => 'manager',
        ]);

        $this->actingAs($manager)
            ->get(route('crm.users.index'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('crm.settings.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_sales_stages(): void
    {
        $admin = $this->createUser([
            'email' => 'settings-admin@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('crm.settings.sales-stages.store'), [
                'name' => 'Contract Review',
                'position' => 9,
                'is_active' => '1',
            ])
            ->assertRedirect(route('crm.settings.sales-stages'));

        $this->assertDatabaseHas('sales_stages', [
            'name' => 'Contract Review',
            'slug' => 'contract-review',
        ]);
    }

    public function test_admin_can_hard_delete_other_crm_users_but_not_themselves(): void
    {
        $admin = $this->createUser([
            'email' => 'delete-admin@example.com',
            'role' => 'admin',
        ]);

        $otherUser = $this->createUser([
            'email' => 'delete-target@example.com',
            'role' => 'rep',
        ]);

        $this->actingAs($admin)
            ->delete(route('crm.users.destroy', $otherUser))
            ->assertRedirect(route('crm.users.index'));

        $this->assertDatabaseMissing('users', [
            'id' => $otherUser->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('crm.users.destroy', $admin))
            ->assertRedirect(route('crm.users.edit', $admin));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_can_delete_sales_stages(): void
    {
        $admin = $this->createUser([
            'email' => 'delete-stage-admin@example.com',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('crm.settings.sales-stages.store'), [
                'name' => 'Delete This Stage',
                'position' => 3,
                'is_active' => '1',
            ]);

        $stageId = (int) \App\Models\SalesStage::query()
            ->where('name', 'Delete This Stage')
            ->value('id');

        $this->actingAs($admin)
            ->delete(route('crm.settings.sales-stages.destroy', $stageId))
            ->assertRedirect(route('crm.settings.sales-stages'));

        $this->assertDatabaseMissing('sales_stages', [
            'id' => $stageId,
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
