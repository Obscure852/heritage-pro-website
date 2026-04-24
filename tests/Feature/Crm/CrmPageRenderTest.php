<?php

namespace Tests\Feature\Crm;

use App\Models\Contact;
use App\Models\CrmInvoice;
use App\Models\CrmProduct;
use App\Models\CrmQuote;
use App\Models\CrmRequest;
use App\Models\DiscussionCampaign;
use App\Models\DiscussionMessage;
use App\Models\Customer;
use App\Models\DevelopmentRequest;
use App\Models\DiscussionThread;
use App\Models\DiscussionThreadParticipant;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmPageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_all_primary_crm_pages(): void
    {
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->get(route('crm.dashboard'))
            ->assertOk()
            ->assertSee('crm-presence-sound-toggle', false)
            ->assertSee('crm-discussion-sound', false);

        $this->actingAs($admin)
            ->get(route('crm.leads.index'))
            ->assertOk()
            ->assertSee('class="crm-shell-content"', false);

        $this->actingAs($admin)
            ->get(route('crm.customers.index'))
            ->assertOk()
            ->assertSee('Import customer')
            ->assertDontSee('New customer');

        $this->actingAs($admin)
            ->get(route('crm.contacts.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.calendar.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.products.catalog.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.products.quotes.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.products.invoices.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.requests.index'))
            ->assertRedirect(route('crm.requests.sales.index'));

        $this->actingAs($admin)
            ->get(route('crm.requests.sales.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.requests.support.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.dev.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.integrations.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.settings.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.products.settings'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.settings.company-information'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.settings.branding'))
            ->assertOk();
    }

    public function test_admin_can_render_create_edit_and_show_pages_for_crm_modules(): void
    {
        $admin = $this->createUser();

        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'North Campus',
            'status' => 'active',
        ]);

        $customer = Customer::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $lead->id,
            'company_name' => 'South Campus',
            'status' => 'active',
        ]);

        $leadContact = Contact::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $lead->id,
            'name' => 'Lead Contact',
            'is_primary' => true,
        ]);

        $contact = Contact::query()->create([
            'owner_id' => $admin->id,
            'customer_id' => $customer->id,
            'name' => 'Pat Doe',
            'is_primary' => true,
        ]);

        $salesStage = SalesStage::query()->create([
            'name' => 'Proposal',
            'slug' => 'proposal',
            'position' => 1,
            'is_active' => true,
            'is_won' => false,
            'is_lost' => false,
        ]);

        $crmRequest = CrmRequest::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $lead->id,
            'contact_id' => $leadContact->id,
            'sales_stage_id' => $salesStage->id,
            'type' => 'sales',
            'title' => 'Initial Proposal',
        ]);

        $supportRequest = CrmRequest::query()->create([
            'owner_id' => $admin->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'type' => 'support',
            'title' => 'Support Follow-up',
            'support_status' => 'open',
        ]);

        $devRequest = DevelopmentRequest::query()->create([
            'owner_id' => $admin->id,
            'customer_id' => $customer->id,
            'title' => 'Analytics Export',
            'description' => 'Need a new export.',
            'priority' => 'medium',
            'status' => 'backlog',
        ]);

        $integration = Integration::query()->create([
            'owner_id' => $admin->id,
            'name' => 'Sandbox API',
            'kind' => 'school_api',
            'status' => 'active',
        ]);

        $product = CrmProduct::query()->create([
            'code' => 'CRM-LIC',
            'name' => 'CRM License',
            'type' => 'license',
            'billing_frequency' => 'annual',
            'default_unit_label' => 'license',
            'default_unit_price' => 2500,
            'default_tax_rate' => 14,
            'active' => true,
        ]);

        $discussion = DiscussionThread::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $admin->id,
            'subject' => 'Internal handoff',
            'channel' => 'app',
            'kind' => 'direct',
            'status' => 'sent',
            'delivery_status' => 'sent',
            'recipient_user_id' => $admin->id,
            'last_message_at' => now(),
        ]);

        $emailDraftDiscussion = DiscussionThread::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $admin->id,
            'subject' => 'Draft email handoff',
            'channel' => 'email',
            'kind' => 'external_direct',
            'status' => 'draft',
            'delivery_status' => 'queued',
            'recipient_email' => 'prospect@example.com',
            'last_message_at' => now(),
        ]);

        $emailDiscussion = DiscussionThread::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $admin->id,
            'subject' => 'Sent email handoff',
            'channel' => 'email',
            'kind' => 'external_direct',
            'status' => 'sent',
            'delivery_status' => 'sent',
            'recipient_email' => 'customer@example.com',
            'last_message_at' => now(),
        ]);

        $whatsappDraftDiscussion = DiscussionThread::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $admin->id,
            'subject' => 'Draft WhatsApp handoff',
            'channel' => 'whatsapp',
            'kind' => 'external_direct',
            'status' => 'draft',
            'delivery_status' => 'pending_integration',
            'recipient_phone' => '+26771234567',
            'last_message_at' => now(),
        ]);

        $whatsappDiscussion = DiscussionThread::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $admin->id,
            'subject' => 'Queued WhatsApp handoff',
            'channel' => 'whatsapp',
            'kind' => 'external_direct',
            'status' => 'sent',
            'delivery_status' => 'queued',
            'recipient_phone' => '+26771230000',
            'last_message_at' => now(),
        ]);

        $appCampaign = DiscussionCampaign::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $admin->id,
            'channel' => 'app',
            'status' => 'draft',
            'subject' => 'Internal launch',
            'body' => 'Share the launch update in company chat.',
            'audience_snapshot' => [
                'requested' => ['recipient_user_ids' => [$admin->id]],
                'resolved' => [['recipient_type' => 'user', 'recipient_id' => $admin->id, 'user_id' => $admin->id, 'label' => $admin->name]],
                'skipped' => [],
            ],
        ]);

        $emailCampaign = DiscussionCampaign::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $admin->id,
            'channel' => 'email',
            'status' => 'draft',
            'subject' => 'Renewal outreach',
            'body' => 'Send the renewal reminder to the selected accounts.',
            'audience_snapshot' => [
                'requested' => ['recipient_user_ids' => [$admin->id]],
                'resolved' => [['recipient_type' => 'user', 'recipient_id' => $admin->id, 'user_id' => $admin->id, 'label' => $admin->name, 'email' => $admin->email, 'address' => $admin->email]],
                'skipped' => [],
            ],
        ]);

        $whatsappCampaign = DiscussionCampaign::query()->create([
            'owner_id' => $admin->id,
            'initiated_by_id' => $admin->id,
            'channel' => 'whatsapp',
            'status' => 'draft',
            'subject' => 'Payment follow-up',
            'body' => 'Send the payment follow-up reminder to the selected contacts.',
            'audience_snapshot' => [
                'requested' => ['recipient_user_ids' => [$admin->id]],
                'resolved' => [['recipient_type' => 'user', 'recipient_id' => $admin->id, 'user_id' => $admin->id, 'label' => $admin->name, 'phone' => '+26771234567', 'address' => '+26771234567']],
                'skipped' => [],
            ],
        ]);

        $quote = CrmQuote::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $lead->id,
            'contact_id' => $leadContact->id,
            'request_id' => $crmRequest->id,
            'quote_number' => 'QT-00099',
            'status' => 'draft',
            'subject' => 'Campus Renewal Quote',
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
        ]);

        $quote->items()->create([
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'position' => 1,
            'item_name' => 'CRM License',
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
            'owner_id' => $admin->id,
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'invoice_number' => 'INV-00021',
            'status' => 'draft',
            'subject' => 'Campus Renewal Invoice',
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
        ]);

        $invoice->items()->create([
            'product_id' => $product->id,
            'source_type' => 'catalog',
            'position' => 1,
            'item_name' => 'CRM License',
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

        $this->actingAs($admin)
            ->get(route('crm.users.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.users.edit', $admin))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.leads.create'))
            ->assertOk()
            ->assertSee('<ol class="breadcrumb m-0">', false)
            ->assertSee('Create Lead')
            ->assertSee('placeholder="Enter institution name"', false)
            ->assertSee('placeholder="Add lead notes, call context, or qualification details"', false);

        $this->actingAs($admin)
            ->get(route('crm.leads.edit', $lead))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.leads.show', $lead))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.customers.onboarding.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.customers.edit', $customer))
            ->assertOk()
            ->assertSee(route('crm.customers.index'), false)
            ->assertSee('id="customer_status"', false)
            ->assertDontSee('id="status"', false);

        $this->actingAs($admin)
            ->get(route('crm.customers.show', $customer))
            ->assertOk()
            ->assertDontSee('Direct customer');

        $this->actingAs($admin)
            ->get(route('crm.contacts.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.contacts.edit', $contact))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.contacts.show', $contact))
            ->assertOk()
            ->assertSee('class="crm-shell-content"', false)
            ->assertSee('<li class="breadcrumb-item active">Contacts</li>', false);

        $this->actingAs($admin)
            ->get(route('crm.products.catalog.create'))
            ->assertOk()
            ->assertSee('Create product');

        $this->actingAs($admin)
            ->get(route('crm.products.catalog.edit', $product))
            ->assertOk()
            ->assertSee('Save changes');

        $this->actingAs($admin)
            ->get(route('crm.products.catalog.show', $product))
            ->assertOk()
            ->assertSee('Catalog detail');

        $this->actingAs($admin)
            ->get(route('crm.products.quotes.create'))
            ->assertOk()
            ->assertSee('Save quote');

        $this->actingAs($admin)
            ->get(route('crm.products.quotes.edit', $quote))
            ->assertOk()
            ->assertSee('Save changes');

        $this->actingAs($admin)
            ->get(route('crm.products.quotes.show', $quote))
            ->assertOk()
            ->assertSee('Snapshotted items');

        $this->actingAs($admin)
            ->get(route('crm.products.invoices.create'))
            ->assertOk()
            ->assertSee('Save invoice draft');

        $this->actingAs($admin)
            ->get(route('crm.products.invoices.edit', $invoice))
            ->assertOk()
            ->assertSee('Save changes');

        $this->actingAs($admin)
            ->get(route('crm.products.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Snapshotted items');

        $this->actingAs($admin)
            ->get(route('crm.requests.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.requests.sales.index'))
            ->assertOk()
            ->assertSee('Find sales work')
            ->assertSee('Lead');

        $this->actingAs($admin)
            ->get(route('crm.requests.support.index'))
            ->assertOk()
            ->assertSee('Find support work')
            ->assertSee('Customer');

        $this->actingAs($admin)
            ->get(route('crm.requests.sales.create'))
            ->assertOk()
            ->assertSee('Create sales request')
            ->assertDontSee('name="support_status"', false);

        $this->actingAs($admin)
            ->get(route('crm.requests.support.create'))
            ->assertOk()
            ->assertSee('Create support request')
            ->assertDontSee('name="sales_stage_id"', false);

        $this->actingAs($admin)
            ->get(route('crm.requests.edit', $crmRequest))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.requests.show', $crmRequest))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.requests.edit', $supportRequest))
            ->assertOk()
            ->assertSee('Edit support request')
            ->assertDontSee('name="sales_stage_id"', false);

        $this->actingAs($admin)
            ->get(route('crm.dev.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.dev.edit', $devRequest))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.dev.show', $devRequest))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.app.workspace'))
            ->assertOk()
            ->assertSee('data-crm-discussion-channel-badge="app"', false)
            ->assertSee('data-crm-discussion-channel-badge="email"', false)
            ->assertSee('data-crm-discussion-channel-badge="whatsapp"', false)
            ->assertSee('data-crm-active-discussion-thread="', false)
            ->assertSee('Enter to send')
            ->assertSee('Shift+Enter for a new line')
            ->assertSee('Type @ to mention a user');

        $this->actingAs($admin)
            ->get(route('crm.discussions.app.direct.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.app.direct.edit', $discussion))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.app.bulk.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.app.bulk.edit', $appCampaign))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.email.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.email.direct.create'))
            ->assertOk()
            ->assertDontSee('label for="integration_id"', false)
            ->assertDontSee('Recipient phone')
            ->assertSee('data-email-editor', false)
            ->assertSee('data-email-editor-error', false);

        $this->actingAs($admin)
            ->get(route('crm.discussions.email.direct.edit', $emailDraftDiscussion))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.email.direct.show', $emailDiscussion))
            ->assertOk()
            ->assertSee('data-crm-active-discussion-thread="' . $emailDiscussion->id . '"', false)
            ->assertSee('Enter to send')
            ->assertSee('Shift+Enter for a new line');

        $this->actingAs($admin)
            ->get(route('crm.discussions.email.bulk.create'))
            ->assertOk()
            ->assertDontSee('label for="integration_id"', false)
            ->assertSee('data-email-editor', false)
            ->assertSee('data-email-editor-error', false);

        $this->actingAs($admin)
            ->get(route('crm.discussions.email.bulk.edit', $emailCampaign))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.whatsapp.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.whatsapp.direct.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.whatsapp.direct.edit', $whatsappDraftDiscussion))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.whatsapp.direct.show', $whatsappDiscussion))
            ->assertOk()
            ->assertSee('data-crm-active-discussion-thread="' . $whatsappDiscussion->id . '"', false);

        $this->actingAs($admin)
            ->get(route('crm.discussions.whatsapp.bulk.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.whatsapp.bulk.edit', $whatsappCampaign))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.discussions.create'))
            ->assertRedirect(route('crm.discussions.app.direct.create'));

        $this->actingAs($admin)
            ->get(route('crm.discussions.show', $discussion))
            ->assertRedirect(route('crm.discussions.app.threads.show', $discussion));

        $this->actingAs($admin)
            ->get(route('crm.integrations.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.integrations.edit', $integration))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.integrations.show', $integration))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.settings.sales-stages.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('crm.settings.sales-stages.edit', $salesStage))
            ->assertOk();
    }

    public function test_external_discussion_show_page_renders_seen_in_crm_for_internal_recipients(): void
    {
        $sender = $this->createUser([
            'email' => 'render-email-sender@example.com',
            'role' => 'admin',
            'name' => 'Render Sender',
        ]);

        $recipient = $this->createUser([
            'email' => 'render-email-recipient@example.com',
            'role' => 'manager',
            'name' => 'Render Recipient',
        ]);

        $thread = DiscussionThread::query()->create([
            'owner_id' => $sender->id,
            'initiated_by_id' => $sender->id,
            'recipient_user_id' => $recipient->id,
            'subject' => 'CRM seen email thread',
            'channel' => 'email',
            'kind' => 'external_direct',
            'recipient_email' => $recipient->email,
            'delivery_status' => 'sent',
            'status' => 'sent',
            'last_message_at' => now(),
        ]);

        $message = DiscussionMessage::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'direction' => 'outbound',
            'channel' => 'email',
            'body' => 'Please confirm the CRM seen state.',
            'delivery_status' => 'sent',
            'sent_at' => now(),
        ]);

        DiscussionThreadParticipant::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'role' => 'owner',
            'last_read_at' => now(),
        ]);

        DiscussionThreadParticipant::query()->create([
            'thread_id' => $thread->id,
            'user_id' => $recipient->id,
            'role' => 'member',
            'last_read_at' => $message->sent_at->copy()->addMinute(),
        ]);

        $this->actingAs($sender)
            ->get(route('crm.discussions.email.direct.show', $thread))
            ->assertOk()
            ->assertSee('Seen in CRM');
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
