<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmCommercialDocumentArtifact;
use App\Models\CrmInvoice;
use App\Models\CrmProduct;
use App\Models\CrmQuote;
use App\Models\Customer;
use App\Models\DiscussionThread;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrmCommercialDocumentDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_quote_pdf_routes_generate_a_private_artifact_and_other_reps_are_denied(): void
    {
        Storage::fake('documents');

        $owner = $this->createUser([
            'email' => 'quote-owner@example.com',
            'role' => 'rep',
        ]);

        $otherRep = $this->createUser([
            'email' => 'quote-other@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Quote Delivery Lead',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'name' => 'Quote Delivery Contact',
            'email' => 'quote.delivery@example.com',
            'is_primary' => true,
        ]);

        $quote = $this->createQuote([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'quote_number' => 'QT-DELIVERY-001',
        ]);

        $openResponse = $this->actingAs($owner)
            ->get(route('crm.products.quotes.pdf.open', $quote));

        $openResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $openResponse->headers->get('content-type'));
        $this->assertStringContainsString('inline;', (string) $openResponse->headers->get('content-disposition'));

        $artifact = CrmCommercialDocumentArtifact::query()->where('quote_id', $quote->id)->firstOrFail();

        Storage::disk('documents')->assertExists($artifact->path);
        $this->assertSame('application/pdf', $artifact->mime_type);
        $this->assertSame($owner->id, $artifact->generated_by_id);

        $downloadResponse = $this->actingAs($owner)
            ->get(route('crm.products.quotes.pdf.download', $quote));

        $downloadResponse->assertOk();
        $this->assertStringContainsString('attachment;', (string) $downloadResponse->headers->get('content-disposition'));

        $this->actingAs($otherRep)
            ->get(route('crm.products.quotes.pdf.open', $quote))
            ->assertForbidden();
    }

    public function test_authorized_invoice_pdf_routes_generate_a_private_artifact_and_other_reps_are_denied(): void
    {
        Storage::fake('documents');

        $owner = $this->createUser([
            'email' => 'invoice-owner@example.com',
            'role' => 'rep',
        ]);

        $otherRep = $this->createUser([
            'email' => 'invoice-other@example.com',
            'role' => 'rep',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Invoice Delivery Customer',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'name' => 'Invoice Delivery Contact',
            'email' => 'invoice.delivery@example.com',
            'is_primary' => true,
        ]);

        $invoice = $this->createInvoice([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'invoice_number' => 'INV-DELIVERY-001',
        ]);

        $openResponse = $this->actingAs($owner)
            ->get(route('crm.products.invoices.pdf.open', $invoice));

        $openResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $openResponse->headers->get('content-type'));
        $this->assertStringContainsString('inline;', (string) $openResponse->headers->get('content-disposition'));

        $artifact = CrmCommercialDocumentArtifact::query()->where('invoice_id', $invoice->id)->firstOrFail();

        Storage::disk('documents')->assertExists($artifact->path);
        $this->assertSame('application/pdf', $artifact->mime_type);
        $this->assertSame($owner->id, $artifact->generated_by_id);

        $downloadResponse = $this->actingAs($owner)
            ->get(route('crm.products.invoices.pdf.download', $invoice));

        $downloadResponse->assertOk();
        $this->assertStringContainsString('attachment;', (string) $downloadResponse->headers->get('content-disposition'));

        $this->actingAs($otherRep)
            ->get(route('crm.products.invoices.pdf.open', $invoice))
            ->assertForbidden();
    }

    public function test_quote_share_creates_a_discussion_marks_the_quote_sent_and_refreshes_the_artifact(): void
    {
        Storage::fake('documents');

        $rep = $this->createUser([
            'email' => 'quote-share-rep@example.com',
            'role' => 'rep',
        ]);

        $manager = $this->createUser([
            'email' => 'quote-share-manager@example.com',
            'role' => 'manager',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $rep->id,
            'company_name' => 'Quote Share Lead',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $rep->id,
            'lead_id' => $lead->id,
            'name' => 'Quote Share Contact',
            'email' => 'quote.share@example.com',
            'is_primary' => true,
        ]);

        $quote = $this->createQuote([
            'owner_id' => $rep->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'quote_number' => 'QT-SHARE-001',
            'status' => 'draft',
        ]);

        $this->actingAs($rep)
            ->get(route('crm.products.quotes.share.create', $quote))
            ->assertOk()
            ->assertSee('Share document')
            ->assertSee(e(route('crm.discussions.app.direct.create', ['source_type' => 'quote', 'source_id' => $quote->id])), false)
            ->assertSee(e(route('crm.discussions.email.direct.create', ['source_type' => 'quote', 'source_id' => $quote->id])), false)
            ->assertSee(e(route('crm.discussions.whatsapp.direct.create', ['source_type' => 'quote', 'source_id' => $quote->id])), false);

        $this->actingAs($rep)
            ->post(route('crm.products.quotes.share.store', $quote), [
                'subject' => 'Share QT-SHARE-001',
                'channel' => 'app',
                'recipient_user_id' => $manager->id,
                'recipient_email' => null,
                'recipient_phone' => null,
                'integration_id' => null,
                'notes' => 'Internal review requested',
                'body' => 'Please review the latest commercial quote.',
            ])
            ->assertRedirect();

        $quote->refresh();
        $artifact = CrmCommercialDocumentArtifact::query()->where('quote_id', $quote->id)->firstOrFail();
        $thread = DiscussionThread::query()->with('messages')->firstOrFail();

        $this->assertSame('sent', $quote->status);
        $this->assertNotNull($quote->shared_at);
        $this->assertSame($quote->updated_at?->toDateTimeString(), $artifact->source_updated_at?->toDateTimeString());
        $this->assertSame($thread->id, $artifact->shared_discussion_thread_id);
        $this->assertSame('sent', $thread->delivery_status);
        $this->assertCount(1, $thread->messages);

        Storage::disk('documents')->assertExists($artifact->path);

        $this->assertDatabaseHas('crm_discussion_messages', [
            'thread_id' => $thread->id,
            'user_id' => $rep->id,
            'direction' => 'outbound',
            'channel' => 'app',
        ]);
    }

    public function test_finance_can_share_an_issued_invoice_and_non_finance_users_cannot_open_the_share_flow(): void
    {
        Storage::fake('documents');

        $finance = $this->createUser([
            'email' => 'invoice-share-finance@example.com',
            'role' => 'finance',
        ]);

        $owner = $this->createUser([
            'email' => 'invoice-share-owner@example.com',
            'role' => 'rep',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Invoice Share Customer',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'name' => 'Invoice Share Contact',
            'email' => 'invoice.share@example.com',
            'is_primary' => true,
        ]);

        $invoice = $this->createInvoice([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'invoice_number' => 'INV-SHARE-001',
            'status' => 'issued',
            'issued_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('crm.products.invoices.share.create', $invoice))
            ->assertForbidden();

        $this->actingAs($finance)
            ->get(route('crm.products.invoices.share.create', $invoice))
            ->assertOk()
            ->assertSee('Share document')
            ->assertSee(e(route('crm.discussions.app.direct.create', ['source_type' => 'invoice', 'source_id' => $invoice->id])), false)
            ->assertSee(e(route('crm.discussions.email.direct.create', ['source_type' => 'invoice', 'source_id' => $invoice->id])), false)
            ->assertSee(e(route('crm.discussions.whatsapp.direct.create', ['source_type' => 'invoice', 'source_id' => $invoice->id])), false);

        $this->actingAs($finance)
            ->get(route('crm.discussions.app.direct.create', ['source_type' => 'invoice', 'source_id' => $invoice->id]))
            ->assertOk()
            ->assertSee('Commercial Source');

        $this->actingAs($finance)
            ->get(route('crm.discussions.app.bulk.create', ['source_type' => 'invoice', 'source_id' => $invoice->id]))
            ->assertOk()
            ->assertSee('Commercial Source');

        $this->actingAs($finance)
            ->post(route('crm.products.invoices.share.store', $invoice), [
                'subject' => 'Share INV-SHARE-001',
                'channel' => 'app',
                'recipient_user_id' => $owner->id,
                'recipient_email' => null,
                'recipient_phone' => null,
                'integration_id' => null,
                'notes' => 'Invoice handoff',
                'body' => 'The issued invoice is ready for the account owner.',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $artifact = CrmCommercialDocumentArtifact::query()->where('invoice_id', $invoice->id)->firstOrFail();
        $thread = DiscussionThread::query()->with('messages')->firstOrFail();

        $this->assertSame('sent', $invoice->status);
        $this->assertNotNull($invoice->shared_at);
        $this->assertSame($invoice->updated_at?->toDateTimeString(), $artifact->source_updated_at?->toDateTimeString());
        $this->assertSame($thread->id, $artifact->shared_discussion_thread_id);
        $this->assertSame('sent', $thread->delivery_status);
        $this->assertCount(1, $thread->messages);

        Storage::disk('documents')->assertExists($artifact->path);
    }

    private function createQuote(array $attributes = []): CrmQuote
    {
        $product = CrmProduct::query()->create([
            'code' => 'QUOTE-PDF-' . uniqid(),
            'name' => 'Quote PDF License',
            'type' => 'license',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 2500,
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
            'subject' => 'Document Delivery Quote',
            'quote_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'currency_code' => 'BWP',
            'currency_symbol' => 'P',
            'currency_position' => 'before',
            'currency_precision' => 2,
            'document_discount_type' => 'none',
            'document_discount_value' => 0,
            'document_discount_amount' => 0,
            'subtotal_amount' => 2500,
            'tax_amount' => 350,
            'total_amount' => 2850,
            'notes' => 'Quote delivery note',
            'terms' => 'Net 14 days',
        ], $attributes));

        $quote->items()->create([
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'position' => 1,
            'item_name' => $product->name,
            'item_description' => 'Annual quote document line',
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

        return $quote;
    }

    private function createInvoice(array $attributes = []): CrmInvoice
    {
        $product = CrmProduct::query()->create([
            'code' => 'INVOICE-PDF-' . uniqid(),
            'name' => 'Invoice PDF License',
            'type' => 'license',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 2500,
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
            'subject' => 'Document Delivery Invoice',
            'invoice_date' => now()->toDateString(),
            'currency_code' => 'BWP',
            'currency_symbol' => 'P',
            'currency_position' => 'before',
            'currency_precision' => 2,
            'document_discount_type' => 'none',
            'document_discount_value' => 0,
            'document_discount_amount' => 0,
            'subtotal_amount' => 2500,
            'tax_amount' => 350,
            'total_amount' => 2850,
            'notes' => 'Invoice delivery note',
            'terms' => 'Net 14 days',
            'issued_at' => null,
        ], $attributes));

        $invoice->items()->create([
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'position' => 1,
            'item_name' => $product->name,
            'item_description' => 'Annual invoice document line',
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
