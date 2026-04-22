<?php

namespace Tests\Feature\Crm;

use App\Models\CrmUserDepartment;
use App\Models\CrmUserPosition;
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
        $department = CrmUserDepartment::query()->create([
            'name' => 'Administration',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $position = CrmUserPosition::query()->create([
            'name' => 'Officer',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('crm.users.store'), [
                'name' => 'Sales Rep',
                'email' => 'sales.rep@example.com',
                'phone' => '+267 7111 0001',
                'id_number' => 'ID-0001',
                'date_of_birth' => '1990-05-10',
                'gender' => 'male',
                'nationality' => 'Botswana',
                'employment_status' => 'active',
                'department_id' => $department->id,
                'position_id' => $position->id,
                'reports_to_user_id' => $admin->id,
                'date_of_appointment' => '2024-01-02',
                'role' => 'rep',
                'active' => '1',
            ]);

        $createdUser = User::query()->where('email', 'sales.rep@example.com')->firstOrFail();

        $response->assertRedirect(route('crm.users.edit', ['user' => $createdUser, 'tab' => 'profile']));

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

    public function test_admin_can_update_an_existing_user_to_the_finance_role(): void
    {
        $admin = $this->createUser([
            'email' => 'finance-update-admin@example.com',
            'role' => 'admin',
        ]);

        $rep = $this->createUser([
            'email' => 'finance-update-rep@example.com',
            'role' => 'rep',
            'name' => 'Rep To Finance',
        ]);

        $this->actingAs($admin)
            ->patch(route('crm.users.roles.update', $rep), [
                'role' => 'finance',
                'module_permissions' => [
                    'dashboard' => 'view',
                    'customers' => null,
                    'contacts' => null,
                    'calendar' => 'edit',
                    'products' => 'admin',
                    'requests' => null,
                    'dev' => null,
                    'discussions' => null,
                    'integrations' => null,
                    'users' => null,
                    'settings' => 'edit',
                ],
            ])
            ->assertRedirect(route('crm.users.edit', ['user' => $rep, 'tab' => 'roles']));

        $this->assertDatabaseHas('users', [
            'id' => $rep->id,
            'role' => 'finance',
            'active' => true,
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

    public function test_manager_cannot_access_customer_onboarding_flow(): void
    {
        $manager = $this->createUser([
            'email' => 'manager-onboarding@example.com',
            'role' => 'manager',
        ]);

        $this->actingAs($manager)
            ->get(route('crm.customers.onboarding.create'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('crm.customers.index'))
            ->assertOk()
            ->assertDontSee('Import customer');

        $this->actingAs($manager)
            ->post(route('crm.customers.onboarding.store'), [
                'company_name' => 'Blocked Import Customer',
                'status' => 'active',
            ])
            ->assertForbidden();
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
