<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmCommercialCurrency;
use App\Models\CrmProduct;
use App\Models\CrmQuote;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use App\Services\Crm\CommercialDocumentCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmQuoteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_rep_can_create_quote_for_lead_with_catalog_and_custom_lines(): void
    {
        $rep = $this->createUser([
            'email' => 'rep-quote-lead@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Lead Quote Account',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $rep->id,
            'lead_id' => $lead->id,
            'name' => 'Lead Quote Contact',
            'email' => 'lead.quote.contact@example.com',
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
            'owner_id' => $rep->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'sales_stage_id' => $salesStage->id,
            'type' => 'sales',
            'title' => 'Lead Quote Opportunity',
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
            'subject' => 'Enterprise Quote',
            'quote_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'document_tax_rate' => '14.00',
            'document_discount_type' => 'percent',
            'document_discount_value' => '10',
            'notes' => 'Internal note',
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

        $this->actingAs($rep)
            ->post(route('crm.products.quotes.store'), $payload)
            ->assertRedirect();

        $quote = CrmQuote::query()->with('items')->firstOrFail();

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
        ], 'percent', 10, 2, 'document', 14);

        $this->assertSame('draft', $quote->status);
        $this->assertSame('document', $quote->tax_scope);
        $this->assertSame('14.00', number_format((float) $quote->document_tax_rate, 2, '.', ''));
        $this->assertNotNull($quote->quote_number);
        $this->assertSame($lead->id, $quote->lead_id);
        $this->assertNull($quote->customer_id);
        $this->assertSame($contact->id, $quote->contact_id);
        $this->assertSame($crmRequest->id, $quote->request_id);
        $this->assertSame('BWP', $quote->currency_code);
        $this->assertSame('Enterprise Quote', $quote->subject);
        $this->assertCount(2, $quote->items);
        $this->assertEquals($expected['subtotal_amount'], (float) $quote->subtotal_amount);
        $this->assertEquals($expected['tax_amount'], (float) $quote->tax_amount);
        $this->assertEquals($expected['total_amount'], (float) $quote->total_amount);

        $this->assertDatabaseHas('crm_quote_items', [
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'item_name' => 'Enterprise License',
            'unit_label' => 'license',
            'discount_type' => 'percent',
        ]);

        $this->assertDatabaseHas('crm_quote_items', [
            'quote_id' => $quote->id,
            'product_id' => null,
            'source_type' => 'custom',
            'item_name' => 'Implementation Workshop',
            'unit_label' => 'session',
            'discount_type' => 'fixed',
            'tax_rate' => '14.00',
        ]);
    }

    public function test_rep_can_create_quote_directly_for_a_contact(): void
    {
        $rep = $this->createUser([
            'email' => 'rep-quote-contact@example.com',
            'role' => 'rep',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $rep->id,
            'name' => 'Direct Quote Contact',
            'email' => 'direct.quote.contact@example.com',
            'is_primary' => false,
        ]);

        $salesStage = SalesStage::query()->firstOrCreate([
            'slug' => 'direct-proposal',
        ], [
            'name' => 'Direct Proposal',
            'position' => 2,
            'is_active' => true,
            'is_won' => false,
            'is_lost' => false,
        ]);

        $crmRequest = CrmRequest::query()->create([
            'owner_id' => $rep->id,
            'contact_id' => $contact->id,
            'sales_stage_id' => $salesStage->id,
            'type' => 'sales',
            'title' => 'Direct Contact Opportunity',
            'outcome' => 'pending',
        ]);

        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $this->actingAs($rep)
            ->post(route('crm.products.quotes.store'), [
                'contact_id' => $contact->id,
                'request_id' => $crmRequest->id,
                'currency_id' => $currency->id,
                'subject' => 'Direct Contact Quote',
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(10)->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [[
                    'product_id' => null,
                    'item_name' => 'Advisory Session',
                    'item_description' => 'Direct commercial engagement',
                    'unit_label' => 'session',
                    'quantity' => '1',
                    'unit_price' => '750.00',
                    'tax_rate' => '14.00',
                    'discount_type' => 'none',
                    'discount_value' => '0.00',
                ]],
            ])
            ->assertRedirect();

        $quote = CrmQuote::query()->with('items')->firstOrFail();

        $this->assertSame($rep->id, $quote->owner_id);
        $this->assertNull($quote->lead_id);
        $this->assertNull($quote->customer_id);
        $this->assertSame($contact->id, $quote->contact_id);
        $this->assertSame($crmRequest->id, $quote->request_id);
        $this->assertSame('line', $quote->tax_scope);
        $this->assertSame('Direct Contact Quote', $quote->subject);
        $this->assertSame('855.00', number_format((float) $quote->total_amount, 2, '.', ''));
    }

    public function test_rep_can_update_customer_quote_without_mutating_snapshotted_price_from_catalog_changes(): void
    {
        $rep = $this->createUser([
            'email' => 'rep-quote-customer@example.com',
            'role' => 'rep',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Customer Quote Account',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $rep->id,
            'customer_id' => $customer->id,
            'name' => 'Customer Quote Contact',
            'email' => 'customer.quote.contact@example.com',
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
            'cpi_increase_rate' => 5,
            'default_tax_rate' => 0,
            'active' => true,
        ]);

        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $this->actingAs($rep)
            ->post(route('crm.products.quotes.store'), [
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Support Renewal',
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(7)->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'item_name' => 'Support Plan',
                        'item_description' => 'Annual support package',
                        'unit_label' => 'package',
                        'quantity' => '1',
                        'unit_price' => '1050.00',
                        'tax_rate' => '0.00',
                        'discount_type' => 'none',
                        'discount_value' => '0.00',
                    ],
                ],
            ])
            ->assertRedirect();

        $quote = CrmQuote::query()->with('items')->firstOrFail();
        $originalItem = $quote->items->firstOrFail();

        $this->assertSame('1050.00', number_format((float) $originalItem->unit_price, 2, '.', ''));
        $this->assertSame('line', $quote->tax_scope);

        $product->update([
            'default_unit_price' => 3250,
            'cpi_increase_rate' => 8,
            'default_tax_rate' => 12,
        ]);

        $this->assertSame('1050.00', number_format((float) $quote->fresh()->items()->first()->unit_price, 2, '.', ''));

        $this->actingAs($rep)
            ->patch(route('crm.products.quotes.update', $quote), [
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Support Renewal Updated',
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(14)->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'item_name' => 'Support Plan',
                        'item_description' => 'Annual support package',
                        'unit_label' => 'package',
                        'quantity' => '2',
                        'unit_price' => '1050.00',
                        'tax_rate' => '0.00',
                        'discount_type' => 'none',
                        'discount_value' => '0.00',
                    ],
                ],
            ])
            ->assertRedirect(route('crm.products.quotes.edit', $quote));

        $updatedQuote = $quote->fresh()->load('items');
        $updatedItem = $updatedQuote->items->firstOrFail();

        $this->assertSame('Support Renewal Updated', $updatedQuote->subject);
        $this->assertSame('1050.00', number_format((float) $updatedItem->unit_price, 2, '.', ''));
        $this->assertSame('2100.00', number_format((float) $updatedQuote->total_amount, 2, '.', ''));
    }

    public function test_quote_status_transitions_follow_the_expected_lifecycle(): void
    {
        $rep = $this->createUser([
            'email' => 'rep-quote-status@example.com',
            'role' => 'rep',
        ]);

        $quote = $this->createDraftQuote($rep);

        $this->actingAs($rep)
            ->patch(route('crm.products.quotes.status', $quote), [
                'status' => 'accepted',
            ])
            ->assertStatus(422);

        $this->actingAs($rep)
            ->patch(route('crm.products.quotes.status', $quote), [
                'status' => 'sent',
            ])
            ->assertRedirect(route('crm.products.quotes.show', $quote));

        $quote->refresh();
        $this->assertSame('sent', $quote->status);
        $this->assertNotNull($quote->shared_at);

        $this->actingAs($rep)
            ->patch(route('crm.products.quotes.status', $quote), [
                'status' => 'accepted',
            ])
            ->assertRedirect(route('crm.products.quotes.show', $quote));

        $quote->refresh();
        $this->assertSame('accepted', $quote->status);
        $this->assertNotNull($quote->accepted_at);

        $this->actingAs($rep)
            ->get(route('crm.products.quotes.edit', $quote))
            ->assertForbidden();
    }

    public function test_rep_cannot_access_another_reps_quote(): void
    {
        $rep = $this->createUser([
            'email' => 'rep-owner@example.com',
            'role' => 'rep',
        ]);

        $otherRep = $this->createUser([
            'email' => 'rep-blocked@example.com',
            'role' => 'rep',
        ]);

        $quote = $this->createDraftQuote($otherRep);

        $this->actingAs($rep)
            ->get(route('crm.products.quotes.show', $quote))
            ->assertForbidden();

        $this->actingAs($rep)
            ->get(route('crm.products.quotes.edit', $quote))
            ->assertForbidden();
    }

    private function createDraftQuote(User $owner): CrmQuote
    {
        $lead = Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Draft Quote Lead ' . uniqid(),
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'name' => 'Draft Quote Contact ' . uniqid(),
            'is_primary' => true,
        ]);

        $quote = CrmQuote::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'quote_number' => 'QT-' . strtoupper(substr(uniqid(), -5)),
            'status' => 'draft',
            'subject' => 'Draft Quote',
            'quote_date' => now()->toDateString(),
            'valid_until' => now()->addDays(7)->toDateString(),
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

        $quote->items()->create([
            'source_type' => 'custom',
            'position' => 1,
            'item_name' => 'Draft Line',
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

        return $quote;
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
