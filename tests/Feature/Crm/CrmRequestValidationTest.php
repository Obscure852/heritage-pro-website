<?php

namespace Tests\Feature\Crm;

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
            ->from(route('crm.requests.index'))
            ->post(route('crm.requests.store'), [
                'owner_id' => $admin->id,
                'lead_id' => $lead->id,
                'type' => 'sales',
                'title' => 'Initial outreach',
            ])
            ->assertRedirect(route('crm.requests.index'))
            ->assertSessionHasErrors('sales_stage_id');
    }

    public function test_support_requests_require_a_support_status(): void
    {
        $admin = $this->createUser([
            'email' => 'support-admin@example.com',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'Senior Support School',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->from(route('crm.requests.index'))
            ->post(route('crm.requests.store'), [
                'owner_id' => $admin->id,
                'lead_id' => $lead->id,
                'type' => 'support',
                'title' => 'Data fix request',
            ])
            ->assertRedirect(route('crm.requests.index'))
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
