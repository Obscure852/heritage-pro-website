<?php

namespace Tests\Feature\Crm;

use App\Models\CrmRequest;
use App\Models\Lead;
use App\Models\RequestAttachment;
use App\Models\SalesStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrmRequestAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_request_with_multiple_attachments_and_open_them(): void
    {
        Storage::fake('documents');

        $admin = $this->createUser([
            'email' => 'request-admin@example.com',
            'role' => 'admin',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'Attachment Lead',
            'status' => 'active',
        ]);

        $salesStage = SalesStage::query()->create([
            'name' => 'Discovery',
            'slug' => 'discovery',
            'position' => 1,
            'is_active' => true,
            'is_won' => false,
            'is_lost' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('crm.requests.sales.store'), [
                'owner_id' => $admin->id,
                'lead_id' => $lead->id,
                'title' => 'Request With Attachments',
                'sales_stage_id' => $salesStage->id,
                'attachments' => [
                    UploadedFile::fake()->create('proposal.pdf', 120, 'application/pdf'),
                    UploadedFile::fake()->create('implementation-plan.docx', 80, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                ],
            ])
            ->assertRedirect();

        $crmRequest = CrmRequest::query()->where('title', 'Request With Attachments')->firstOrFail();

        $this->assertDatabaseCount('request_attachments', 2);

        $pdfAttachment = RequestAttachment::query()
            ->where('request_id', $crmRequest->id)
            ->where('original_name', 'proposal.pdf')
            ->firstOrFail();

        Storage::disk('documents')->assertExists($pdfAttachment->path);

        $this->actingAs($admin)
            ->get(route('crm.requests.show', $crmRequest))
            ->assertOk()
            ->assertSee('proposal.pdf')
            ->assertSee('implementation-plan.docx');

        $this->actingAs($admin)
            ->get(route('crm.requests.edit', $crmRequest))
            ->assertOk()
            ->assertSee('data-dropzone', false);

        $this->actingAs($admin)
            ->get(route('crm.requests.attachments.open', [$crmRequest, $pdfAttachment]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($admin)
            ->get(route('crm.requests.attachments.download', [$crmRequest, $pdfAttachment]))
            ->assertOk();
    }

    public function test_rep_cannot_open_an_attachment_for_a_request_they_do_not_own(): void
    {
        Storage::fake('documents');

        $owner = $this->createUser([
            'email' => 'request-owner@example.com',
            'role' => 'rep',
        ]);

        $otherRep = $this->createUser([
            'email' => 'request-other@example.com',
            'role' => 'rep',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $owner->id,
            'company_name' => 'Restricted Lead',
            'status' => 'active',
        ]);

        $crmRequest = CrmRequest::query()->create([
            'owner_id' => $owner->id,
            'lead_id' => $lead->id,
            'type' => 'sales',
            'title' => 'Restricted Request',
        ]);

        $uploadedFile = UploadedFile::fake()->create('private-proposal.pdf', 64, 'application/pdf');
        $path = $uploadedFile->store('crm/request-attachments/' . $crmRequest->id, 'documents');

        $attachment = $crmRequest->attachments()->create([
            'uploaded_by_id' => $owner->id,
            'disk' => 'documents',
            'path' => $path,
            'original_name' => 'private-proposal.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 65536,
        ]);

        $this->actingAs($otherRep)
            ->get(route('crm.requests.attachments.open', [$crmRequest, $attachment]))
            ->assertForbidden();
    }

    public function test_authorized_user_can_delete_request_attachments(): void
    {
        Storage::fake('documents');

        $admin = $this->createUser([
            'email' => 'attachment-delete-admin@example.com',
            'role' => 'admin',
        ]);

        $lead = Lead::query()->create([
            'owner_id' => $admin->id,
            'company_name' => 'Delete Attachment Lead',
            'status' => 'active',
        ]);

        $crmRequest = CrmRequest::query()->create([
            'owner_id' => $admin->id,
            'lead_id' => $lead->id,
            'type' => 'sales',
            'title' => 'Attachment Delete Request',
        ]);

        $uploadedFile = UploadedFile::fake()->create('delete-me.docx', 40, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $path = $uploadedFile->store('crm/request-attachments/' . $crmRequest->id, 'documents');

        $attachment = $crmRequest->attachments()->create([
            'uploaded_by_id' => $admin->id,
            'disk' => 'documents',
            'path' => $path,
            'original_name' => 'delete-me.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'extension' => 'docx',
            'size' => 40960,
        ]);

        $this->actingAs($admin)
            ->delete(route('crm.requests.attachments.destroy', [$crmRequest, $attachment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('request_attachments', [
            'id' => $attachment->id,
        ]);

        Storage::disk('documents')->assertMissing($path);
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
