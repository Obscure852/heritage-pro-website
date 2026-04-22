<?php

namespace Tests\Feature\Crm;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_requests_require_a_stage(): void
    {
        $admin = $this->createUser();
        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'Pilot Junior School',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->from(route('crm.requests.sales.create'))
            ->post(route('crm.requests.sales.store'), [
                'owner_id' => $admin->id,
                'lead_id' => $lead->id,
                'title' => 'Initial outreach',
            ])
            ->assertRedirect(route('crm.requests.sales.create'))
            ->assertSessionHasErrors('sales_stage_id');
    }

    public function test_support_requests_require_a_support_status(): void
    {
        $admin = $this->createUser([
            'email' => 'support-admin@example.com',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'Senior Support School',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->from(route('crm.requests.support.create'))
            ->post(route('crm.requests.support.store'), [
                'owner_id' => $admin->id,
                'customer_id' => $customer->id,
                'title' => 'Data fix request',
            ])
            ->assertRedirect(route('crm.requests.support.create'))
            ->assertSessionHasErrors('support_status');
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
