<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
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

class CrmCommercialIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_customer_and_request_pages_render_related_commercial_documents(): void
    {
        $admin = $this->createUser([
            'email' => 'integration-admin@example.com',
            'role' => 'admin',
        ]);

        $owner = $this->createUser([
            'email' => 'integration-owner@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Integration Lead Account',
            'status' => 'active',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'company_name' => 'Integration Customer Account',
            'status' => 'active',
        ]);

        $leadContact = Contact::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'name' => 'Lead Integration Contact',
            'email' => 'lead.integration@example.com',
            'is_primary' => true,
        ]);

        $customerContact = Contact::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'name' => 'Customer Integration Contact',
            'email' => 'customer.integration@example.com',
            'is_primary' => true,
        ]);

        $salesStage = SalesStage::query()->firstOrCreate([
            'slug' => 'integration-proposal',
        ], [
            'name' => 'Integration Proposal',
            'position' => 1,
            'is_active' => true,
            'is_won' => false,
            'is_lost' => false,
        ]);

        $crmRequest = CrmRequest::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'contact_id' => $leadContact->id,
            'sales_stage_id' => $salesStage->id,
            'type' => 'sales',
            'title' => 'Integration Sales Request',
            'outcome' => 'pending',
        ]);

        $leadQuote = $this->createQuote([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'contact_id' => $leadContact->id,
            'request_id' => $crmRequest->id,
            'quote_number' => 'QT-INTEGRATION-LEAD',
            'subject' => 'Integration Lead Quote',
        ]);

        $leadInvoice = $this->createInvoice([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'contact_id' => $leadContact->id,
            'request_id' => $crmRequest->id,
            'invoice_number' => 'INV-INTEGRATION-LEAD',
            'subject' => 'Integration Lead Invoice',
            'status' => 'issued',
            'issued_at' => now(),
        ]);

        $customerQuote = $this->createQuote([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'contact_id' => $customerContact->id,
            'quote_number' => 'QT-INTEGRATION-CUSTOMER',
            'subject' => 'Integration Customer Quote',
        ]);

        $customerInvoice = $this->createInvoice([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'contact_id' => $customerContact->id,
            'invoice_number' => 'INV-INTEGRATION-CUSTOMER',
            'subject' => 'Integration Customer Invoice',
            'status' => 'issued',
            'issued_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('crm.leads.show', $lead))
            ->assertOk()
            ->assertSee('Related quotes and invoices')
            ->assertSee('QT-INTEGRATION-LEAD')
            ->assertSee('INV-INTEGRATION-LEAD')
            ->assertSee(route('crm.products.quotes.pdf.open', $leadQuote), false)
            ->assertSee(route('crm.products.invoices.pdf.open', $leadInvoice), false);

        $this->actingAs($admin)
            ->get(route('crm.customers.show', $customer))
            ->assertOk()
            ->assertSee('Related quotes and invoices')
            ->assertSee('QT-INTEGRATION-CUSTOMER')
            ->assertSee('INV-INTEGRATION-CUSTOMER')
            ->assertSee(route('crm.products.quotes.pdf.open', $customerQuote), false)
            ->assertSee(route('crm.products.invoices.pdf.open', $customerInvoice), false);

        $this->actingAs($admin)
            ->get(route('crm.requests.show', $crmRequest))
            ->assertOk()
            ->assertSee('Related quotes and invoices')
            ->assertSee('QT-INTEGRATION-LEAD')
            ->assertSee('INV-INTEGRATION-LEAD')
            ->assertSee(route('crm.products.quotes.pdf.download', $leadQuote), false)
            ->assertSee(route('crm.products.invoices.pdf.download', $leadInvoice), false);
    }

    private function createQuote(array $attributes = []): CrmQuote
    {
        $product = CrmProduct::query()->create([
            'code' => 'INTEGRATION-QUOTE-' . uniqid(),
            'name' => 'Integration Quote Product',
            'type' => 'license',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 1200,
            'default_tax_rate' => 14,
            'active' => true,
        ]);

        $quote = CrmQuote::query()->create(array_merge([
            'owner_id' => null,
            'lead_id' => null,
            'customer_id' => null,
            'contact_id' => null,
            'request_id' => null,
            'quote_number' => 'QT-' . strtoupper(substr(uniqid(), -6)),
            'status' => 'draft',
            'subject' => 'Integration Quote',
            'quote_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'currency_code' => 'BWP',
            'currency_symbol' => 'P',
            'currency_position' => 'before',
            'currency_precision' => 2,
            'document_discount_type' => 'none',
            'document_discount_value' => 0,
            'document_discount_amount' => 0,
            'subtotal_amount' => 1200,
            'tax_amount' => 168,
            'total_amount' => 1368,
        ], $attributes));

        $quote->items()->create([
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'position' => 1,
            'item_name' => $product->name,
            'unit_label' => 'license',
            'quantity' => 1,
            'unit_price' => 1200,
            'gross_amount' => 1200,
            'discount_type' => 'none',
            'discount_value' => 0,
            'discount_amount' => 0,
            'net_amount' => 1200,
            'tax_rate' => 14,
            'tax_amount' => 168,
            'total_amount' => 1368,
        ]);

        return $quote;
    }

    private function createInvoice(array $attributes = []): CrmInvoice
    {
        $product = CrmProduct::query()->create([
            'code' => 'INTEGRATION-INVOICE-' . uniqid(),
            'name' => 'Integration Invoice Product',
            'type' => 'license',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 1800,
            'default_tax_rate' => 14,
            'active' => true,
        ]);

        $invoice = CrmInvoice::query()->create(array_merge([
            'owner_id' => null,
            'lead_id' => null,
            'customer_id' => null,
            'contact_id' => null,
            'request_id' => null,
            'invoice_number' => 'INV-' . strtoupper(substr(uniqid(), -6)),
            'status' => 'draft',
            'subject' => 'Integration Invoice',
            'invoice_date' => now()->toDateString(),
            'currency_code' => 'BWP',
            'currency_symbol' => 'P',
            'currency_position' => 'before',
            'currency_precision' => 2,
            'document_discount_type' => 'none',
            'document_discount_value' => 0,
            'document_discount_amount' => 0,
            'subtotal_amount' => 1800,
            'tax_amount' => 252,
            'total_amount' => 2052,
            'issued_at' => null,
        ], $attributes));

        $invoice->items()->create([
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'position' => 1,
            'item_name' => $product->name,
            'unit_label' => 'license',
            'quantity' => 1,
            'unit_price' => 1800,
            'gross_amount' => 1800,
            'discount_type' => 'none',
            'discount_value' => 0,
            'discount_amount' => 0,
            'net_amount' => 1800,
            'tax_rate' => 14,
            'tax_amount' => 252,
            'total_amount' => 2052,
        ]);

        return $invoice;
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
