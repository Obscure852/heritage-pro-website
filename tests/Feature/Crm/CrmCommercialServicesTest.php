<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmCommercialSetting;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use App\Services\Crm\CommercialDocumentValidationService;
use App\Services\Crm\CommercialNumberingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CrmCommercialServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbering_service_uses_stored_sequences(): void
    {
        $settings = CrmCommercialSetting::query()->firstOrFail();
        $settings->update([
            'quote_prefix' => 'QTE',
            'quote_next_sequence' => 7,
            'invoice_prefix' => 'BILL',
            'invoice_next_sequence' => 11,
        ]);

        $service = app(CommercialNumberingService::class);

        $this->assertSame('QTE-00007', $service->nextQuoteNumber());
        $this->assertSame('BILL-00011', $service->nextInvoiceNumber());

        $settings->refresh();

        $this->assertSame(8, $settings->quote_next_sequence);
        $this->assertSame(12, $settings->invoice_next_sequence);
    }

    public function test_validation_service_accepts_valid_quote_payload(): void
    {
        $owner = $this->createUser([
            'email' => 'validator-owner@example.com',
            'role' => 'manager',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Validation Lead',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'name' => 'Validation Contact',
            'email' => 'validation.contact@example.com',
            'is_primary' => true,
        ]);

        $stage = SalesStage::query()->firstOrFail();
        $request = CrmRequest::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'sales_stage_id' => $stage->id,
            'type' => 'sales',
            'title' => 'Validation Sales Request',
            'outcome' => 'pending',
        ]);

        $validated = app(CommercialDocumentValidationService::class)->validateQuote([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'contact_id' => $contact->id,
            'request_id' => $request->id,
            'quote_number' => 'QT-00001',
            'status' => 'draft',
            'quote_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
        ]);

        $this->assertSame($lead->id, $validated['lead_id']);
        $this->assertSame($contact->id, $validated['contact_id']);
        $this->assertSame($request->id, $validated['request_id']);
        $this->assertSame(now()->addDays(14)->toDateString(), $validated['valid_until']);
    }

    public function test_validation_service_accepts_valid_invoice_payload(): void
    {
        $owner = $this->createUser([
            'email' => 'validator-invoice@example.com',
            'role' => 'finance',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Validation Customer',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'name' => 'Validation Customer Contact',
            'email' => 'validation.customer.contact@example.com',
            'is_primary' => true,
        ]);

        $stage = SalesStage::query()->firstOrFail();
        $request = CrmRequest::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'sales_stage_id' => $stage->id,
            'type' => 'sales',
            'title' => 'Validation Invoice Request',
            'outcome' => 'pending',
        ]);

        $validated = app(CommercialDocumentValidationService::class)->validateInvoice([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'request_id' => $request->id,
            'invoice_number' => 'INV-00001',
            'status' => 'draft',
            'invoice_date' => now()->toDateString(),
        ]);

        $this->assertSame($customer->id, $validated['customer_id']);
        $this->assertSame($contact->id, $validated['contact_id']);
        $this->assertSame($request->id, $validated['request_id']);
    }

    public function test_validation_service_rejects_invalid_account_context_and_mismatched_links(): void
    {
        $owner = $this->createUser([
            'email' => 'validator-invalid@example.com',
            'role' => 'manager',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Broken Lead',
            'status' => 'active',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Broken Customer',
            'status' => 'active',
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'name' => 'Customer Contact',
            'email' => 'customer.contact@example.com',
            'is_primary' => true,
        ]);

        $supportRequest = CrmRequest::query()->create([
            'owner_id' => $owner->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'type' => 'support',
            'title' => 'Broken Request',
            'support_status' => 'open',
        ]);

        try {
            app(CommercialDocumentValidationService::class)->validateInvoice([
                'owner_id' => $owner->id,
                'lead_id' => $lead->id,
                'contact_id' => $contact->id,
                'request_id' => $supportRequest->id,
                'invoice_number' => 'INV-00001',
                'status' => 'draft',
                'invoice_date' => now()->toDateString(),
            ]);

            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            $this->assertArrayHasKey('contact_id', $errors);
            $this->assertArrayHasKey('request_id', $errors);
        }
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
