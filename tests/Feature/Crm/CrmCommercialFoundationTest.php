<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmCommercialSetting;
use App\Models\CrmCommercialCurrency;
use App\Models\CrmUserDepartment;
use App\Models\CrmUserPosition;
use App\Models\CrmInvoice;
use App\Models\CrmProduct;
use App\Models\CrmQuote;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmCommercialFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_finance_crm_users(): void
    {
        $admin = $this->createUser([
            'email' => 'admin-commercial@example.com',
            'role' => 'admin',
        ]);
        $department = CrmUserDepartment::query()->create([
            'name' => 'Finance',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $position = CrmUserPosition::query()->create([
            'name' => 'Accountant',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('crm.users.store'), [
                'name' => 'Finance User',
                'email' => 'finance.user@example.com',
                'phone' => '+267 7222 1000',
                'id_number' => 'FIN-1000',
                'date_of_birth' => '1989-04-12',
                'gender' => 'female',
                'nationality' => 'Botswana',
                'employment_status' => 'active',
                'department_id' => $department->id,
                'position_id' => $position->id,
                'reports_to_user_id' => $admin->id,
                'date_of_appointment' => '2023-11-01',
                'role' => 'finance',
                'active' => '1',
            ]);

        $user = User::query()->where('email', 'finance.user@example.com')->firstOrFail();

        $response->assertRedirect(route('crm.users.edit', ['user' => $user, 'tab' => 'profile']));

        $this->assertDatabaseHas('users', [
            'email' => 'finance.user@example.com',
            'role' => 'finance',
            'active' => true,
        ]);
    }

    public function test_finance_users_can_access_crm_dashboard_and_commercial_settings_but_not_admin_users_area(): void
    {
        $finance = $this->createUser([
            'email' => 'finance-dashboard@example.com',
            'role' => 'finance',
        ]);

        $this->actingAs($finance)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee(route('crm.products.catalog.index'), false)
            ->assertSee(route('crm.settings.commercial'), false)
            ->assertDontSee(route('crm.users.index'), false);

        $this->actingAs($finance)
            ->get(route('crm.users.index'))
            ->assertForbidden();

        $this->actingAs($finance)
            ->get(route('crm.settings.index'))
            ->assertRedirect(route('crm.settings.commercial'));

        $this->actingAs($finance)
            ->get(route('crm.settings.commercial'))
            ->assertOk()
            ->assertSee('Commercial');
    }

    public function test_commercial_foundation_seeds_defaults_and_exposes_expected_relationships(): void
    {
        $owner = $this->createUser([
            'email' => 'commercial-owner@example.com',
            'role' => 'manager',
        ]);

        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->first();
        $settings = CrmCommercialSetting::query()->with('defaultCurrency')->first();

        $this->assertNotNull($currency);
        $this->assertNotNull($settings);
        $this->assertSame($currency?->id, $settings?->default_currency_id);
        $this->assertSame('QT', $settings?->quote_prefix);
        $this->assertSame('INV', $settings?->invoice_prefix);

        $lead = Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Commercial Lead',
            'status' => 'active',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Commercial Customer',
            'status' => 'active',
        ]);

        $leadContact = Contact::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'name' => 'Lead Contact',
            'email' => 'lead.contact@example.com',
            'is_primary' => true,
        ]);

        $customerContact = Contact::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'name' => 'Customer Contact',
            'email' => 'customer.contact@example.com',
            'is_primary' => true,
        ]);

        $salesStage = SalesStage::query()->firstOrFail();

        $salesRequest = CrmRequest::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'contact_id' => $leadContact->id,
            'sales_stage_id' => $salesStage->id,
            'type' => 'sales',
            'title' => 'Commercial Request',
            'outcome' => 'pending',
        ]);

        $product = CrmProduct::query()->create([
            'code' => 'PRO-LIC',
            'name' => 'Pro License',
            'type' => 'license',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 2500,
            'default_tax_rate' => 14,
            'active' => true,
        ]);

        $quote = CrmQuote::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'contact_id' => $leadContact->id,
            'request_id' => $salesRequest->id,
            'quote_number' => 'QT-00001',
            'status' => 'draft',
            'subject' => 'Commercial Quote',
            'quote_date' => now()->toDateString(),
            'valid_until' => now()->addWeek()->toDateString(),
            'currency_code' => $currency->code,
            'currency_symbol' => $currency->symbol,
            'currency_position' => $currency->symbol_position,
            'currency_precision' => $currency->precision,
            'document_discount_type' => 'none',
            'document_discount_value' => 0,
            'document_discount_amount' => 0,
            'subtotal_amount' => 2500,
            'tax_amount' => 350,
            'total_amount' => 2850,
        ]);

        $quote->items()->create([
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'position' => 1,
            'item_name' => $product->name,
            'unit_label' => 'license',
            'quantity' => 1,
            'unit_price' => 2500,
            'gross_amount' => 2500,
            'discount_type' => 'none',
            'discount_value' => 0,
            'discount_amount' => 0,
            'net_amount' => 2500,
            'tax_rate' => 14,
            'tax_amount' => 350,
            'total_amount' => 2850,
        ]);

        $invoice = CrmInvoice::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'contact_id' => $customerContact->id,
            'invoice_number' => 'INV-00001',
            'status' => 'draft',
            'subject' => 'Commercial Invoice',
            'invoice_date' => now()->toDateString(),
            'currency_code' => $currency->code,
            'currency_symbol' => $currency->symbol,
            'currency_position' => $currency->symbol_position,
            'currency_precision' => $currency->precision,
            'document_discount_type' => 'none',
            'document_discount_value' => 0,
            'document_discount_amount' => 0,
            'subtotal_amount' => 2500,
            'tax_amount' => 350,
            'total_amount' => 2850,
        ]);

        $invoice->items()->create([
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'position' => 1,
            'item_name' => $product->name,
            'unit_label' => 'license',
            'quantity' => 1,
            'unit_price' => 2500,
            'gross_amount' => 2500,
            'discount_type' => 'none',
            'discount_value' => 0,
            'discount_amount' => 0,
            'net_amount' => 2500,
            'tax_rate' => 14,
            'tax_amount' => 350,
            'total_amount' => 2850,
        ]);

        $this->assertTrue($lead->quotes()->whereKey($quote->id)->exists());
        $this->assertTrue($customer->invoices()->whereKey($invoice->id)->exists());
        $this->assertTrue($leadContact->quotes()->whereKey($quote->id)->exists());
        $this->assertTrue($customerContact->invoices()->whereKey($invoice->id)->exists());
        $this->assertTrue($salesRequest->quotes()->whereKey($quote->id)->exists());
        $this->assertTrue($product->quoteItems()->where('quote_id', $quote->id)->exists());
        $this->assertTrue($product->invoiceItems()->where('invoice_id', $invoice->id)->exists());
        $this->assertSame($owner->id, $quote->owner?->id);
        $this->assertSame($owner->id, $invoice->owner?->id);
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
