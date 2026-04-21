<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\DevelopmentRequest;
use App\Models\DiscussionThread;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmPageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_all_primary_crm_pages(): void
    {
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->get(route('crm.dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.leads.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.customers.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.contacts.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.requests.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.dev.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.integrations.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.settings.index'))
            ->assertOk();
    }

    public function test_admin_can_render_create_edit_and_show_pages_for_crm_modules(): void
    {
        $admin = $this->createUser();

        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'North Campus',
            'status' => 'active',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $lead->id,
            'company_name' => 'South Campus',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $admin->id,
            'customer_id' => $customer->id,
            'name' => 'Pat Doe',
            'is_primary' => true,
        ]);

        $salesStage = SalesStage::query()->create([
            'name' => 'Proposal',
            'slug' => 'proposal',
            'position' => 1,
            'is_active' => true,
            'is_won' => false,
            'is_lost' => false,
        ]);

        $crmRequest = CrmRequest::query()->create([
            'owner_id' => $admin->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'sales_stage_id' => $salesStage->id,
            'type' => 'sales',
            'title' => 'Initial Proposal',
        ]);

        $devRequest = DevelopmentRequest::query()->create([
            'owner_id' => $admin->id,
            'customer_id' => $customer->id,
            'title' => 'Analytics Export',
            'description' => 'Need a new export.',
            'priority' => 'medium',
            'status' => 'backlog',
        ]);

        $integration = Integration::query()->create([
            'owner_id' => $admin->id,
            'name' => 'Sandbox API',
            'kind' => 'school_api',
            'status' => 'active',
        ]);

        $discussion = DiscussionThread::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $admin->id,
            'subject' => 'Internal handoff',
            'channel' => 'app',
            'delivery_status' => 'sent',
            'last_message_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('crm.users.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.users.edit', $admin))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.leads.create'))
            ->assertOk()
            ->assertSee('<ol class="breadcrumb m-0">', false)
            ->assertSee('Create Lead')
            ->assertSee('placeholder="Enter institution name"', false)
            ->assertSee('placeholder="Add lead notes, call context, or qualification details"', false);

        $this->actingAs($admin)
            ->get(route('crm.leads.edit', $lead))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.leads.show', $lead))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.customers.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.customers.edit', $customer))
            ->assertOk()
            ->assertSee(route('crm.customers.index'), false)
            ->assertSee('id="customer_status"', false)
            ->assertDontSee('id="status"', false);

        $this->actingAs($admin)
            ->get(route('crm.customers.show', $customer))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.contacts.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.contacts.edit', $contact))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.contacts.show', $contact))
            ->assertOk()
            ->assertSee('<li class="breadcrumb-item active">Contacts</li>', false);

        $this->actingAs($admin)
            ->get(route('crm.requests.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.requests.edit', $crmRequest))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.requests.show', $crmRequest))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.dev.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.dev.edit', $devRequest))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.dev.show', $devRequest))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.show', $discussion))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.integrations.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.integrations.edit', $integration))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.integrations.show', $integration))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.settings.sales-stages.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.settings.sales-stages.edit', $salesStage))
            ->assertOk();
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'CRM Admin',
            'email' => 'admin-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }
}
