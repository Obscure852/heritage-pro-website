<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmInvoice;
use App\Models\CrmProduct;
use App\Models\CrmQuote;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmGlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_includes_products_quotes_and_invoices_and_respects_rep_scope(): void
    {
        $rep = $this->createUser([
            'email' => 'commercial-search-rep@example.com',
            'role' => 'rep',
        ]);

        $otherRep = $this->createUser([
            'email' => 'commercial-search-other@example.com',
            'role' => 'rep',
        ]);

        CrmProduct::query()->create([
            'code' => 'SEARCH-LICENSE',
            'name' => 'Commercial Search License',
            'type' => 'license',
            'description' => 'Searchable product record',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 1000,
            'default_tax_rate' => 14,
            'active' => true,
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Commercial Search Lead',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $rep->id,
            'lead_id' => $lead->id,
            'name' => 'Commercial Search Contact',
            'email' => 'commercial.search@example.com',
            'is_primary' => true,
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Commercial Search Customer',
            'status' => 'active',
        ]);

        CrmQuote::query()->create([
            'owner_id' => $rep->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'quote_number' => 'QT-SEARCH-REP',
            'status' => 'draft',
            'subject' => 'Commercial Search Quote',
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
            'tax_amount' => 140,
            'total_amount' => 1140,
        ]);

        CrmInvoice::query()->create([
            'owner_id' => $rep->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'invoice_number' => 'INV-SEARCH-REP',
            'status' => 'issued',
            'subject' => 'Commercial Search Invoice',
            'invoice_date' => now()->toDateString(),
            'currency_code' => 'BWP',
            'currency_symbol' => 'P',
            'currency_position' => 'before',
            'currency_precision' => 2,
            'document_discount_type' => 'none',
            'document_discount_value' => 0,
            'document_discount_amount' => 0,
            'subtotal_amount' => 1000,
            'tax_amount' => 140,
            'total_amount' => 1140,
        ]);

        $otherLead = Lead::query()->create([
            'owner_id' => $otherRep->id,
            'company_name' => 'Commercial Search Hidden Lead',
            'status' => 'active',
        ]);

        $otherContact = Contact::query()->create([
            'owner_id' => $otherRep->id,
            'lead_id' => $otherLead->id,
            'name' => 'Commercial Hidden Contact',
            'email' => 'commercial.hidden@example.com',
            'is_primary' => true,
        ]);

        $otherCustomer = Customer::query()->create([
            'owner_id' => $otherRep->id,
            'company_name' => 'Commercial Search Hidden Customer',
            'status' => 'active',
        ]);

        CrmQuote::query()->create([
            'owner_id' => $otherRep->id,
            'lead_id' => $otherLead->id,
            'contact_id' => $otherContact->id,
            'quote_number' => 'QT-SEARCH-HIDDEN',
            'status' => 'draft',
            'subject' => 'Commercial Search Hidden Quote',
            'quote_date' => now()->toDateString(),
            'valid_until' => now()->addDays(7)->toDateString(),
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
        ]);

        CrmInvoice::query()->create([
            'owner_id' => $otherRep->id,
            'customer_id' => $otherCustomer->id,
            'contact_id' => $otherContact->id,
            'invoice_number' => 'INV-SEARCH-HIDDEN',
            'status' => 'issued',
            'subject' => 'Commercial Search Hidden Invoice',
            'invoice_date' => now()->toDateString(),
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
        ]);

        $this->actingAs($rep)
            ->getJson(route('crm.search', ['q' => 'Search']))
            ->assertOk()
            ->assertJsonFragment(['label' => 'Products'])
            ->assertJsonFragment(['label' => 'Quotes'])
            ->assertJsonFragment(['label' => 'Invoices'])
            ->assertJsonFragment(['label' => 'Commercial Search License'])
            ->assertJsonFragment(['label' => 'QT-SEARCH-REP'])
            ->assertJsonFragment(['label' => 'INV-SEARCH-REP'])
            ->assertJsonMissing(['label' => 'QT-SEARCH-HIDDEN'])
            ->assertJsonMissing(['label' => 'INV-SEARCH-HIDDEN']);
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
