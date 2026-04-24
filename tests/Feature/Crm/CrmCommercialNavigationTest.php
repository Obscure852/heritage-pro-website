<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmInvoice;
use App\Models\CrmQuote;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmCommercialNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_workspace_is_visible_to_crm_roles_and_settings_visibility_is_role_aware(): void
    {
        $admin = $this->createUser([
            'email' => 'admin-navigation@example.com',
            'role' => 'admin',
        ]);

        $finance = $this->createUser([
            'email' => 'finance-navigation@example.com',
            'role' => 'finance',
        ]);

        $manager = $this->createUser([
            'email' => 'manager-navigation@example.com',
            'role' => 'manager',
        ]);

        $rep = $this->createUser([
            'email' => 'rep-navigation@example.com',
            'role' => 'rep',
        ]);

        $this->actingAs($admin)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee(route('crm.products.catalog.index'), false)
            ->assertSee(route('crm.settings.index'), false);

        $this->actingAs($finance)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee(route('crm.products.catalog.index'), false)
            ->assertSee(route('crm.products.settings'), false)
            ->assertDontSee(route('crm.settings.commercial'), false)
            ->assertDontSee(route('crm.users.index'), false);

        $this->actingAs($manager)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee(route('crm.products.catalog.index'), false)
            ->assertDontSee(route('crm.settings.index'), false)
            ->assertDontSee(route('crm.products.settings'), false);

        $this->actingAs($rep)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee(route('crm.products.catalog.index'), false)
            ->assertDontSee(route('crm.settings.index'), false)
            ->assertDontSee(route('crm.products.settings'), false);
    }

    public function test_rep_quote_and_invoice_indexes_only_show_owned_documents(): void
    {
        $rep = $this->createUser([
            'email' => 'rep-docs@example.com',
            'role' => 'rep',
        ]);

        $otherRep = $this->createUser([
            'email' => 'other-rep-docs@example.com',
            'role' => 'rep',
        ]);

        $repLead = Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Rep Account',
            'status' => 'active',
        ]);

        $otherLead = Lead::query()->create([
            'owner_id' => $otherRep->id,
            'company_name' => 'Other Account',
            'status' => 'active',
        ]);

        $repContact = Contact::query()->create([
            'owner_id' => $rep->id,
            'lead_id' => $repLead->id,
            'name' => 'Rep Contact',
            'is_primary' => true,
        ]);

        $otherContact = Contact::query()->create([
            'owner_id' => $otherRep->id,
            'lead_id' => $otherLead->id,
            'name' => 'Other Contact',
            'is_primary' => true,
        ]);

        $repCustomer = Customer::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Rep Customer',
            'status' => 'active',
        ]);

        $otherCustomer = Customer::query()->create([
            'owner_id' => $otherRep->id,
            'company_name' => 'Other Customer',
            'status' => 'active',
        ]);

        $repCustomerContact = Contact::query()->create([
            'owner_id' => $rep->id,
            'customer_id' => $repCustomer->id,
            'name' => 'Rep Customer Contact',
            'is_primary' => true,
        ]);

        $otherCustomerContact = Contact::query()->create([
            'owner_id' => $otherRep->id,
            'customer_id' => $otherCustomer->id,
            'name' => 'Other Customer Contact',
            'is_primary' => true,
        ]);

        CrmQuote::query()->create($this->quotePayload($rep->id, $repLead->id, $repContact->id, 'QT-OWN'));
        CrmQuote::query()->create($this->quotePayload($otherRep->id, $otherLead->id, $otherContact->id, 'QT-OTHER'));

        CrmInvoice::query()->create($this->invoicePayload($rep->id, $repCustomer->id, $repCustomerContact->id, 'INV-OWN'));
        CrmInvoice::query()->create($this->invoicePayload($otherRep->id, $otherCustomer->id, $otherCustomerContact->id, 'INV-OTHER'));

        $this->actingAs($rep)
            ->get(route('crm.products.quotes.index'))
            ->assertOk()
            ->assertSee('QT-OWN')
            ->assertDontSee('QT-OTHER');

        $this->actingAs($rep)
            ->get(route('crm.products.invoices.index'))
            ->assertOk()
            ->assertSee('INV-OWN')
            ->assertDontSee('INV-OTHER');
    }

    private function quotePayload(int $ownerId, int $leadId, int $contactId, string $number): array
    {
        return [
            'owner_id' => $ownerId,
            'lead_id' => $leadId,
            'contact_id' => $contactId,
            'quote_number' => $number,
            'status' => 'draft',
            'subject' => 'Quote ' . $number,
            'quote_date' => now()->toDateString(),
            'valid_until' => now()->addWeek()->toDateString(),
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
        ];
    }

    private function invoicePayload(int $ownerId, int $customerId, int $contactId, string $number): array
    {
        return [
            'owner_id' => $ownerId,
            'customer_id' => $customerId,
            'contact_id' => $contactId,
            'invoice_number' => $number,
            'status' => 'draft',
            'subject' => 'Invoice ' . $number,
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
        ];
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
