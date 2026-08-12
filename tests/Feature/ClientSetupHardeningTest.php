<?php

namespace Tests\Feature;

use App\Models\CrmClientSetupAttachment;
use App\Services\ClientSetup\ClientSetupAttachmentScanService;
use App\Services\ClientSetup\ClientSetupAttachmentService;
use App\Services\ClientSetup\ClientSetupInvitationService;
use App\Services\ClientSetup\Contracts\ClientSetupScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ClientSetupHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_configured_scanner_records_a_clean_result_and_audit_event(): void
    {
        Mail::fake();
        Storage::fake('documents');
        $invitation = app(ClientSetupInvitationService::class)->create([
            'email' => 'scanner-' . uniqid() . '@example.com',
        ]);
        $attachment = app(ClientSetupAttachmentService::class)->store(
            $invitation['submission'],
            $invitation['invitation'],
            UploadedFile::fake()->create('policy.pdf', 10, 'application/pdf'),
            'policy',
            'required'
        );

        config(['client_setup.release.scanner_adapter' => ClientSetupTestScanner::class]);
        $result = app(ClientSetupAttachmentScanService::class)->scan($attachment);

        $this->assertSame('approved', $result->scan_status);
        $this->assertSame('test-scanner', $result->scan_provider);
        $this->assertSame('scan-123', $result->scan_reference);
        $this->assertNotNull($result->scan_completed_at);
        $this->assertDatabaseHas('crm_client_setup_events', [
            'submission_id' => $invitation['submission']->id,
            'event_type' => 'attachment_scan_completed',
            'actor_type' => 'system',
        ]);
    }

    public function test_terminal_scan_result_cannot_be_overwritten(): void
    {
        Mail::fake();
        Storage::fake('documents');
        $invitation = app(ClientSetupInvitationService::class)->create([
            'email' => 'scanner-terminal-' . uniqid() . '@example.com',
        ]);
        $attachment = app(ClientSetupAttachmentService::class)->store(
            $invitation['submission'],
            $invitation['invitation'],
            UploadedFile::fake()->create('policy.pdf', 10, 'application/pdf'),
            'policy'
        );
        $service = app(ClientSetupAttachmentScanService::class);
        $service->recordResult($attachment, ['status' => 'rejected', 'provider' => 'test-scanner']);

        $this->expectException(RuntimeException::class);
        $service->recordResult($attachment->fresh(), ['status' => 'approved', 'provider' => 'test-scanner']);
    }
}

class ClientSetupTestScanner implements ClientSetupScanner
{
    public function scan(CrmClientSetupAttachment $attachment): array
    {
        return [
            'status' => 'approved',
            'provider' => 'test-scanner',
            'reference' => 'scan-123',
            'message' => 'Clean test result.',
        ];
    }
}
