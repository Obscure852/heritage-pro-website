<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmRequest;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
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
