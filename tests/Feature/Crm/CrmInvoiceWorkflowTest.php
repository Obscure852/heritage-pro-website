<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmCommercialCurrency;
use App\Models\CrmInvoice;
use App\Models\CrmProduct;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use App\Services\Crm\CommercialDocumentCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmInvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_can_create_invoice_for_lead_with_catalog_and_custom_lines(): void
    {
        $finance = $this->createUser([
            'email' => 'finance-invoice-lead@example.com',
            'role' => 'finance',
        ]);

        $repOwner = $this->createUser([
            'email' => 'rep-invoice-lead@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $repOwner->id,
            'company_name' => 'Lead Invoice Account',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $repOwner->id,
            'lead_id' => $lead->id,
            'name' => 'Lead Invoice Contact',
            'email' => 'lead.invoice.contact@example.com',
            'is_primary' => true,
        ]);

        $salesStage = SalesStage::query()->firstOrCreate([
            'slug' => 'proposal',
        ], [
            'name' => 'Proposal',
            'position' => 1,
            'is_active' => true,
            'is_won' => false,
            'is_lost' => false,
        ]);

        $crmRequest = CrmRequest::query()->create([
            'owner_id' => $repOwner->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'sales_stage_id' => $salesStage->id,
            'type' => 'sales',
            'title' => 'Lead Invoice Opportunity',
            'outcome' => 'pending',
        ]);

        $product = CrmProduct::query()->create([
            'code' => 'LIC-ENTERPRISE',
            'name' => 'Enterprise License',
            'type' => 'license',
            'description' => 'Annual enterprise subscription',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 1500,
            'default_tax_rate' => 14,
            'active' => true,
        ]);

        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $payload = [
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'request_id' => $crmRequest->id,
            'currency_id' => $currency->id,
            'subject' => 'Enterprise Renewal Invoice',
            'invoice_date' => now()->toDateString(),
            'document_discount_type' => 'percent',
            'document_discount_value' => '10',
            'notes' => 'Internal billing note',
            'terms' => 'Net 14 days',
            'items' => [
                [
                    'product_id' => $product->id,
                    'item_name' => '',
                    'item_description' => '',
                    'unit_label' => '',
                    'quantity' => '2',
                    'unit_price' => '1500.00',
                    'tax_rate' => '14.00',
                    'discount_type' => 'percent',
                    'discount_value' => '5',
                ],
                [
                    'product_id' => null,
                    'item_name' => 'Implementation Workshop',
                    'item_description' => 'One-off setup support',
                    'unit_label' => 'session',
                    'quantity' => '1',
                    'unit_price' => '500.00',
                    'tax_rate' => '0.00',
                    'discount_type' => 'fixed',
                    'discount_value' => '50.00',
                ],
            ],
        ];

        $this->actingAs($finance)
            ->post(route('crm.products.invoices.store'), $payload)
            ->assertRedirect();

        $invoice = CrmInvoice::query()->with('items')->firstOrFail();

        $calculator = app(CommercialDocumentCalculator::class);
        $expected = $calculator->calculate([
            [
                'quantity' => 2,
                'unit_price' => 1500,
                'discount_type' => 'percent',
                'discount_value' => 5,
                'tax_rate' => 14,
            ],
            [
                'quantity' => 1,
                'unit_price' => 500,
                'discount_type' => 'fixed',
                'discount_value' => 50,
                'tax_rate' => 0,
            ],
        ], 'percent', 10, 2);

        $this->assertSame('draft', $invoice->status);
        $this->assertNotNull($invoice->invoice_number);
        $this->assertSame($repOwner->id, $invoice->owner_id);
        $this->assertSame($lead->id, $invoice->lead_id);
        $this->assertNull($invoice->customer_id);
        $this->assertSame($contact->id, $invoice->contact_id);
        $this->assertSame($crmRequest->id, $invoice->request_id);
        $this->assertSame('BWP', $invoice->currency_code);
        $this->assertSame('Enterprise Renewal Invoice', $invoice->subject);
        $this->assertCount(2, $invoice->items);
        $this->assertEquals($expected['subtotal_amount'], (float) $invoice->subtotal_amount);
        $this->assertEquals($expected['tax_amount'], (float) $invoice->tax_amount);
        $this->assertEquals($expected['total_amount'], (float) $invoice->total_amount);

        $this->assertDatabaseHas('crm_invoice_items', [
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'item_name' => 'Enterprise License',
            'unit_label' => 'license',
            'discount_type' => 'percent',
        ]);

        $this->assertDatabaseHas('crm_invoice_items', [
            'invoice_id' => $invoice->id,
            'product_id' => null,
            'source_type' => 'custom',
            'item_name' => 'Implementation Workshop',
            'unit_label' => 'session',
            'discount_type' => 'fixed',
        ]);
    }

    public function test_finance_can_update_customer_invoice_without_mutating_snapshotted_price_from_catalog_changes(): void
    {
        $finance = $this->createUser([
            'email' => 'finance-invoice-customer@example.com',
            'role' => 'finance',
        ]);

        $repOwner = $this->createUser([
            'email' => 'rep-invoice-customer@example.com',
            'role' => 'rep',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $repOwner->id,
            'company_name' => 'Customer Invoice Account',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $repOwner->id,
            'customer_id' => $customer->id,
            'name' => 'Customer Invoice Contact',
            'email' => 'customer.invoice.contact@example.com',
            'is_primary' => true,
        ]);

        $product = CrmProduct::query()->create([
            'code' => 'SUPPORT-PLAN',
            'name' => 'Support Plan',
            'type' => 'support',
            'description' => 'Annual support package',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'package',
            'default_unit_price' => 1000,
            'default_tax_rate' => 0,
            'active' => true,
        ]);

        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $this->actingAs($finance)
            ->post(route('crm.products.invoices.store'), [
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Support Renewal Invoice',
                'invoice_date' => now()->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'item_name' => 'Support Plan',
                        'item_description' => 'Annual support package',
                        'unit_label' => 'package',
                        'quantity' => '1',
                        'unit_price' => '1000.00',
                        'tax_rate' => '0.00',
                        'discount_type' => 'none',
                        'discount_value' => '0.00',
                    ],
                ],
            ])
            ->assertRedirect();

        $invoice = CrmInvoice::query()->with('items')->firstOrFail();
        $originalItem = $invoice->items->firstOrFail();

        $this->assertSame('1000.00', number_format((float) $originalItem->unit_price, 2, '.', ''));

        $product->update([
            'default_unit_price' => 3250,
            'default_tax_rate' => 12,
        ]);

        $this->assertSame('1000.00', number_format((float) $invoice->fresh()->items()->first()->unit_price, 2, '.', ''));

        $this->actingAs($finance)
            ->patch(route('crm.products.invoices.update', $invoice), [
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Support Renewal Invoice Updated',
                'invoice_date' => now()->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'item_name' => 'Support Plan',
                        'item_description' => 'Annual support package',
                        'unit_label' => 'package',
                        'quantity' => '2',
                        'unit_price' => '1000.00',
                        'tax_rate' => '0.00',
                        'discount_type' => 'none',
                        'discount_value' => '0.00',
                    ],
                ],
            ])
            ->assertRedirect(route('crm.products.invoices.edit', $invoice));

        $updatedInvoice = $invoice->fresh()->load('items');
        $updatedItem = $updatedInvoice->items->firstOrFail();

        $this->assertSame('Support Renewal Invoice Updated', $updatedInvoice->subject);
        $this->assertSame('1000.00', number_format((float) $updatedItem->unit_price, 2, '.', ''));
        $this->assertSame('2000.00', number_format((float) $updatedInvoice->total_amount, 2, '.', ''));
    }

    public function test_invoice_status_transitions_are_finance_owned_and_follow_expected_lifecycle(): void
    {
        $finance = $this->createUser([
            'email' => 'finance-invoice-status@example.com',
            'role' => 'finance',
        ]);

        $invoice = $this->createDraftInvoice($this->createUser([
            'email' => 'rep-invoice-status-owner@example.com',
            'role' => 'rep',
        ]));

        $this->actingAs($finance)
            ->patch(route('crm.products.invoices.status', $invoice), [
                'status' => 'sent',
            ])
            ->assertStatus(422);

        $this->actingAs($finance)
            ->patch(route('crm.products.invoices.status', $invoice), [
                'status' => 'issued',
            ])
            ->assertRedirect(route('crm.products.invoices.show', $invoice));

        $invoice->refresh();
        $this->assertSame('issued', $invoice->status);
        $this->assertNotNull($invoice->issued_at);

        $this->actingAs($finance)
            ->get(route('crm.products.invoices.edit', $invoice))
            ->assertForbidden();

        $this->actingAs($finance)
            ->patch(route('crm.products.invoices.status', $invoice), [
                'status' => 'sent',
            ])
            ->assertRedirect(route('crm.products.invoices.show', $invoice));

        $invoice->refresh();
        $this->assertSame('sent', $invoice->status);
        $this->assertNotNull($invoice->shared_at);

        $this->actingAs($finance)
            ->patch(route('crm.products.invoices.status', $invoice), [
                'status' => 'void',
            ])
            ->assertRedirect(route('crm.products.invoices.show', $invoice));

        $invoice->refresh();
        $this->assertSame('void', $invoice->status);
        $this->assertNotNull($invoice->voided_at);
    }

    public function test_rep_can_view_owned_invoice_but_cannot_create_edit_or_issue_it(): void
    {
        $rep = $this->createUser([
            'email' => 'rep-owned-invoice@example.com',
            'role' => 'rep',
        ]);

        $invoice = $this->createDraftInvoice($rep);

        $this->actingAs($rep)
            ->get(route('crm.products.invoices.show', $invoice))
            ->assertOk();

        $this->actingAs($rep)
            ->get(route('crm.products.invoices.create'))
            ->assertForbidden();

        $this->actingAs($rep)
            ->get(route('crm.products.invoices.edit', $invoice))
            ->assertForbidden();

        $this->actingAs($rep)
            ->patch(route('crm.products.invoices.status', $invoice), [
                'status' => 'issued',
            ])
            ->assertForbidden();
    }

    public function test_manager_can_view_invoice_but_cannot_edit_or_issue_it(): void
    {
        $manager = $this->createUser([
            'email' => 'manager-owned-invoice@example.com',
            'role' => 'manager',
        ]);

        $repOwner = $this->createUser([
            'email' => 'rep-manager-visible-invoice@example.com',
            'role' => 'rep',
        ]);

        $invoice = $this->createDraftInvoice($repOwner);

        $this->actingAs($manager)
            ->get(route('crm.products.invoices.show', $invoice))
            ->assertOk();

        $this->actingAs($manager)
            ->get(route('crm.products.invoices.edit', $invoice))
            ->assertForbidden();

        $this->actingAs($manager)
            ->patch(route('crm.products.invoices.status', $invoice), [
                'status' => 'issued',
            ])
            ->assertForbidden();
    }

    private function createDraftInvoice(User $owner): CrmInvoice
    {
        $customer = Customer::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Draft Invoice Customer ' . uniqid(),
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'name' => 'Draft Invoice Contact ' . uniqid(),
            'is_primary' => true,
        ]);

        $invoice = CrmInvoice::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'invoice_number' => 'INV-' . strtoupper(substr(uniqid(), -5)),
            'status' => 'draft',
            'subject' => 'Draft Invoice',
            'invoice_date' => now()->toDateString(),
            'currency_code' => 'BWP',
            'currency_symbol' => 'P',
            'currency_position' => 'before',
            'currency_precision' => 2,
            'document_discount_type' => 'none',
            'document_discount_value' => 0,
            'document_discount_amount' => 0,
            'subtotal_amount' => 1000,
            'tax_amount' => 0,
            'total_amount' => 1000,
        ]);

        $invoice->items()->create([
            'source_type' => 'custom',
            'position' => 1,
            'item_name' => 'Draft Invoice Line',
            'unit_label' => 'unit',
            'quantity' => 1,
            'unit_price' => 1000,
            'gross_amount' => 1000,
            'discount_type' => 'none',
            'discount_value' => 0,
            'discount_amount' => 0,
            'net_amount' => 1000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total_amount' => 1000,
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
