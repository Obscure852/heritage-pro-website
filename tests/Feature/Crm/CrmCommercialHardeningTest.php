<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmCommercialCurrency;
use App\Models\CrmInvoice;
use App\Models\CrmProduct;
use App\Models\CrmQuote;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmCommercialHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_store_rejects_missing_contact(): void
    {
        $rep = $this->createUser([
            'email' => 'hardening-rep@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Hardening Lead',
            'status' => 'active',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Hardening Customer',
            'status' => 'active',
        ]);

        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $this->actingAs($rep)
            ->from(route('crm.products.quotes.create'))
            ->post(route('crm.products.quotes.store'), [
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
                'contact_id' => null,
                'currency_id' => $currency->id,
                'subject' => 'Broken Quote',
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(7)->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [[
                    'product_id' => null,
                    'item_name' => 'Custom line',
                    'item_description' => 'Broken context line',
                    'unit_label' => 'unit',
                    'quantity' => '1',
                    'unit_price' => '100.00',
                    'tax_rate' => '0.00',
                    'discount_type' => 'none',
                    'discount_value' => '0.00',
                ]],
            ])
            ->assertRedirect(route('crm.products.quotes.create'))
            ->assertSessionHasErrors(['contact_id']);
    }

    public function test_quote_store_rejects_invalid_account_context_even_with_a_valid_contact(): void
    {
        $rep = $this->createUser([
            'email' => 'hardening-context-rep@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Hardening Context Lead',
            'status' => 'active',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Hardening Context Customer',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $rep->id,
            'lead_id' => $lead->id,
            'name' => 'Context Contact',
            'email' => 'context.contact@example.com',
            'is_primary' => true,
        ]);

        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $this->actingAs($rep)
            ->from(route('crm.products.quotes.create'))
            ->post(route('crm.products.quotes.store'), [
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Broken account context',
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(7)->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [[
                    'product_id' => null,
                    'item_name' => 'Custom line',
                    'item_description' => 'Broken context line',
                    'unit_label' => 'unit',
                    'quantity' => '1',
                    'unit_price' => '100.00',
                    'tax_rate' => '0.00',
                    'discount_type' => 'none',
                    'discount_value' => '0.00',
                ]],
            ])
            ->assertRedirect(route('crm.products.quotes.create'))
            ->assertSessionHasErrors(['account_context']);
    }

    public function test_new_quote_rejects_inactive_products_but_existing_quotes_can_keep_historical_inactive_lines(): void
    {
        $rep = $this->createUser([
            'email' => 'hardening-quote-rep@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Historical Quote Lead',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $rep->id,
            'lead_id' => $lead->id,
            'name' => 'Historical Quote Contact',
            'email' => 'historical.quote@example.com',
            'is_primary' => true,
        ]);

        $product = CrmProduct::query()->create([
            'code' => 'HARD-QUOTE',
            'name' => 'Historical Quote Product',
            'type' => 'license',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 1000,
            'default_tax_rate' => 14,
            'active' => false,
        ]);

        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $this->actingAs($rep)
            ->from(route('crm.products.quotes.create'))
            ->post(route('crm.products.quotes.store'), [
                'lead_id' => $lead->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Blocked inactive quote',
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(7)->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [[
                    'product_id' => $product->id,
                    'item_name' => '',
                    'item_description' => '',
                    'unit_label' => '',
                    'quantity' => '1',
                    'unit_price' => '1000.00',
                    'tax_rate' => '14.00',
                    'discount_type' => 'none',
                    'discount_value' => '0.00',
                ]],
            ])
            ->assertRedirect(route('crm.products.quotes.create'))
            ->assertSessionHasErrors(['items.0.product_id']);

        $product->update(['active' => true]);

        $this->actingAs($rep)
            ->post(route('crm.products.quotes.store'), [
                'lead_id' => $lead->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Historical quote',
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(7)->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [[
                    'product_id' => $product->id,
                    'item_name' => '',
                    'item_description' => '',
                    'unit_label' => '',
                    'quantity' => '1',
                    'unit_price' => '1000.00',
                    'tax_rate' => '14.00',
                    'discount_type' => 'none',
                    'discount_value' => '0.00',
                ]],
            ])
            ->assertRedirect();

        $quote = CrmQuote::query()->with('items')->firstOrFail();
        $product->update(['active' => false]);

        $this->actingAs($rep)
            ->get(route('crm.products.quotes.edit', $quote))
            ->assertOk()
            ->assertSee('[Inactive]');

        $this->actingAs($rep)
            ->patch(route('crm.products.quotes.update', $quote), [
                'lead_id' => $lead->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Historical quote updated',
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(14)->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [[
                    'product_id' => $product->id,
                    'item_name' => 'Historical Quote Product',
                    'item_description' => 'Historical product line',
                    'unit_label' => 'license',
                    'quantity' => '1',
                    'unit_price' => '1000.00',
                    'tax_rate' => '14.00',
                    'discount_type' => 'none',
                    'discount_value' => '0.00',
                ]],
            ])
            ->assertRedirect(route('crm.products.quotes.edit', $quote));

        $quote->refresh();

        $this->assertSame('Historical quote updated', $quote->subject);
        $this->assertDatabaseHas('crm_quote_items', [
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'source_type' => 'catalog',
        ]);
    }

    public function test_new_invoice_rejects_inactive_products_but_existing_invoices_can_keep_historical_inactive_lines(): void
    {
        $finance = $this->createUser([
            'email' => 'hardening-invoice-finance@example.com',
            'role' => 'finance',
        ]);

        $owner = $this->createUser([
            'email' => 'hardening-invoice-owner@example.com',
            'role' => 'rep',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Historical Invoice Customer',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'name' => 'Historical Invoice Contact',
            'email' => 'historical.invoice@example.com',
            'is_primary' => true,
        ]);

        $product = CrmProduct::query()->create([
            'code' => 'HARD-INVOICE',
            'name' => 'Historical Invoice Product',
            'type' => 'license',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 2000,
            'default_tax_rate' => 14,
            'active' => false,
        ]);

        $currency = CrmCommercialCurrency::query()->where('code', 'BWP')->firstOrFail();

        $this->actingAs($finance)
            ->from(route('crm.products.invoices.create'))
            ->post(route('crm.products.invoices.store'), [
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Blocked inactive invoice',
                'invoice_date' => now()->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [[
                    'product_id' => $product->id,
                    'item_name' => '',
                    'item_description' => '',
                    'unit_label' => '',
                    'quantity' => '1',
                    'unit_price' => '2000.00',
                    'tax_rate' => '14.00',
                    'discount_type' => 'none',
                    'discount_value' => '0.00',
                ]],
            ])
            ->assertRedirect(route('crm.products.invoices.create'))
            ->assertSessionHasErrors(['items.0.product_id']);

        $product->update(['active' => true]);

        $this->actingAs($finance)
            ->post(route('crm.products.invoices.store'), [
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Historical invoice',
                'invoice_date' => now()->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [[
                    'product_id' => $product->id,
                    'item_name' => '',
                    'item_description' => '',
                    'unit_label' => '',
                    'quantity' => '1',
                    'unit_price' => '2000.00',
                    'tax_rate' => '14.00',
                    'discount_type' => 'none',
                    'discount_value' => '0.00',
                ]],
            ])
            ->assertRedirect();

        $invoice = CrmInvoice::query()->with('items')->firstOrFail();
        $product->update(['active' => false]);

        $this->actingAs($finance)
            ->get(route('crm.products.invoices.edit', $invoice))
            ->assertOk()
            ->assertSee('[Inactive]');

        $this->actingAs($finance)
            ->patch(route('crm.products.invoices.update', $invoice), [
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'currency_id' => $currency->id,
                'subject' => 'Historical invoice updated',
                'invoice_date' => now()->toDateString(),
                'document_discount_type' => 'none',
                'document_discount_value' => '0',
                'items' => [[
                    'product_id' => $product->id,
                    'item_name' => 'Historical Invoice Product',
                    'item_description' => 'Historical invoice product line',
                    'unit_label' => 'license',
                    'quantity' => '1',
                    'unit_price' => '2000.00',
                    'tax_rate' => '14.00',
                    'discount_type' => 'none',
                    'discount_value' => '0.00',
                ]],
            ])
            ->assertRedirect(route('crm.products.invoices.edit', $invoice));

        $invoice->refresh();

        $this->assertSame('Historical invoice updated', $invoice->subject);
        $this->assertDatabaseHas('crm_invoice_items', [
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'source_type' => 'catalog',
        ]);
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
