<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmLeadConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_conversion_preserves_contacts_and_request_history(): void
    {
        $admin = $this->createUser();

        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'Unified College',
            'industry' => 'Education',
            'status' => 'qualified',
            'email' => 'info@unifiedcollege.example',
            'phone' => '+267 000 0000',
            'country' => 'Botswana',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $lead->id,
            'name' => 'Operations Director',
            'email' => 'ops@unifiedcollege.example',
            'is_primary' => true,
        ]);

        $stage = SalesStage::query()->orderBy('position')->firstOrFail();

        $request = CrmRequest::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'sales_stage_id' => $stage->id,
            'type' => 'sales',
            'title' => 'Campus rollout discussion',
            'outcome' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('crm.leads.convert', $lead))
            ->assertRedirect();

        $lead->refresh();
        $contact->refresh();
        $request->refresh();

        $customer = $lead->customers()->first();

        $this->assertNotNull($customer);
        $this->assertSame('converted', $lead->status);
        $this->assertNotNull($lead->converted_at);
        $this->assertNull($contact->lead_id);
        $this->assertSame($customer->id, $contact->customer_id);
        $this->assertSame($lead->id, $request->lead_id);
        $this->assertSame($customer->id, $request->customer_id);
    }

    public function test_admin_can_import_customer_and_create_source_lead(): void
    {
        $admin = $this->createUser([
            'email' => 'import-admin@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('crm.customers.onboarding.store'), [
                'owner_id' => $admin->id,
                'company_name' => 'Legacy Campus',
                'industry' => 'Education',
                'email' => 'legacy@example.com',
                'phone' => '+267 111 2222',
                'country' => 'Botswana',
                'status' => 'onboarding',
                'purchased_at' => '2026-04-01',
                'notes' => 'Imported from legacy account list.',
            ])
            ->assertRedirect();

        $customer = Customer::query()->where('company_name', 'Legacy Campus')->firstOrFail();
        $lead = Lead::query()->findOrFail($customer->lead_id);

        $this->assertSame('Legacy Campus', $lead->company_name);
        $this->assertSame('converted', $lead->status);
        $this->assertNotNull($lead->converted_at);
        $this->assertSame('onboarding', $customer->status);
        $this->assertSame($lead->id, $customer->lead_id);
    }

    public function test_unconverted_lead_cannot_be_marked_converted_through_edit_form(): void
    {
        $admin = $this->createUser([
            'email' => 'status-admin@example.com',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'Strict Lead',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->from(route('crm.leads.edit', $lead))
            ->patch(route('crm.leads.update', $lead), [
                'owner_id' => $admin->id,
                'company_name' => 'Strict Lead',
                'status' => 'converted',
            ])
            ->assertRedirect(route('crm.leads.edit', $lead))
            ->assertSessionHasErrors('status');
    }

    public function test_converted_lead_with_active_customer_cannot_be_deleted(): void
    {
        $admin = $this->createUser([
            'email' => 'delete-converted@example.com',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'Protected Lead',
            'status' => 'converted',
            'converted_at' => now(),
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $lead->id,
            'company_name' => 'Protected Customer',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->delete(route('crm.leads.destroy', $lead))
            ->assertRedirect(route('crm.leads.show', $lead));

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'lead_id' => $lead->id,
        ]);
    }

    public function test_customer_origin_backfill_migration_creates_source_leads_for_legacy_customers(): void
    {
        $admin = $this->createUser([
            'email' => 'backfill-admin@example.com',
        ]);

        $customerId = DB::table('customers')->insertGetId([
            'owner_id' => $admin->id,
            'lead_id' => null,
            'company_name' => 'Legacy Customer Without Lead',
            'industry' => 'Education',
            'website' => 'https://legacy.example.com',
            'email' => 'legacy-customer@example.com',
            'phone' => '+267 555 0000',
            'country' => 'Botswana',
            'status' => 'active',
            'purchased_at' => '2026-03-01 00:00:00',
            'notes' => 'Imported before lead-origin enforcement.',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $migration = require database_path('migrations/2026_04_21_170500_backfill_customer_origin_leads.php');
        $migration->up();

        $customer = Customer::query()->findOrFail($customerId);
        $lead = Lead::query()->findOrFail($customer->lead_id);

        $this->assertSame('Legacy Customer Without Lead', $lead->company_name);
        $this->assertSame('converted', $lead->status);
        $this->assertNotNull($lead->converted_at);
        $this->assertSame('Legacy Customer Without Lead', $customer->company_name);
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
