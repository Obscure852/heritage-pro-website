<?php

namespace Tests\Feature;

use App\Jobs\ImportClientSetupMigrationJob;
use App\Models\CrmClientSetupMigrationUpload;
use App\Models\User;
use App\Services\ClientSetup\ClientSetupInvitationService;
use App\Services\ClientSetup\ClientSetupMigrationImportService;
use App\Services\ClientSetup\ClientSetupMigrationService;
use App\Services\ClientSetup\Contracts\ClientSetupMigrationImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientSetupImportExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_can_download_all_row_errors_beyond_the_ui_preview_limit(): void
    {
        Mail::fake();
        Storage::fake('documents');
        $admin = $this->createUser();
        $invitation = $this->createInvitation($admin);
        $headers = implode(',', config('client_setup.migration_templates.staff.headings'));
        $rows = [];

        for ($row = 0; $row < 260; $row++) {
            $rows[] = ",,Surname{$row},invalid-email,not-a-date,X,,,,,,,,,,,,,\n";
        }

        $upload = app(ClientSetupMigrationService::class)->validateAndStore(
            $invitation['submission'],
            $invitation['invitation'],
            'staff',
            UploadedFile::fake()->createWithContent('large-invalid-staff.csv', $headers . "\n" . implode('', $rows))
        );

        $this->assertSame(250, count($upload->validation_errors));
        $this->assertSame(260, $upload->migrationErrors()->count());

        $response = $this->actingAs($admin)->get(route('crm.client-setup.migration-uploads.validation-report', [
            'submission' => $invitation['submission'],
            'migrationUpload' => $upload,
        ]));

        $response->assertOk();
        $this->assertStringContainsString(',261,', $response->streamedContent());
    }

    public function test_approved_upload_is_imported_once_through_the_adapter_job(): void
    {
        Mail::fake();
        Storage::fake('documents');
        Queue::fake();
        ClientSetupTestImporter::$calls = 0;
        config(['client_setup.release.importer_adapter' => ClientSetupTestImporter::class]);
        $admin = $this->createUser();
        $invitation = $this->createInvitation($admin);
        $upload = $this->createApprovedUpload($invitation, $admin);

        $queued = app(ClientSetupMigrationImportService::class)->queue($upload, $admin);

        $this->assertSame('queued', $queued->import_status);
        Queue::assertPushed(ImportClientSetupMigrationJob::class, function (ImportClientSetupMigrationJob $job) use ($upload, $admin): bool {
            return $job->uploadId === $upload->id && $job->actorId === $admin->id;
        });

        $completed = app(ClientSetupMigrationImportService::class)->execute($upload->id, $admin->id);
        $again = app(ClientSetupMigrationImportService::class)->execute($upload->id, $admin->id);

        $this->assertSame('completed', $completed->import_status);
        $this->assertSame('completed', $again->import_status);
        $this->assertSame(1, ClientSetupTestImporter::$calls);
        $this->assertSame('college-import-123', $completed->import_reference);
        $this->assertDatabaseHas('crm_client_setup_events', [
            'submission_id' => $invitation['submission']->id,
            'event_type' => 'migration_import_completed',
        ]);
    }

    public function test_import_action_is_not_available_to_managers(): void
    {
        Mail::fake();
        Storage::fake('documents');
        $manager = $this->createUser(['role' => 'manager']);
        $invitation = $this->createInvitation($manager);
        $upload = $this->createApprovedUpload($invitation, $manager);

        config(['client_setup.release.importer_adapter' => ClientSetupTestImporter::class]);

        $this->actingAs($manager)
            ->post(route('crm.client-setup.migration-uploads.import', [
                'submission' => $invitation['submission'],
                'migrationUpload' => $upload,
            ]))
            ->assertForbidden();
    }

    private function createApprovedUpload(array $invitation, User $admin): CrmClientSetupMigrationUpload
    {
        $csv = implode(',', config('client_setup.migration_templates.staff.headings')) . "\n"
            . "Thato,,Molapisi,thato@example.com,02/02/1976,M,Lecturer,74000001,ID1,Motswana,Gaborone,Plot 1,Yes,Current,Engineering,Lecturer,,2024,tmolapisi\n";
        $upload = app(ClientSetupMigrationService::class)->validateAndStore(
            $invitation['submission'],
            $invitation['invitation'],
            'staff',
            UploadedFile::fake()->createWithContent('staff.csv', $csv)
        );
        $upload->attachment()->update([
            'scan_status' => 'approved',
            'scan_completed_at' => now(),
        ]);

        return app(ClientSetupMigrationService::class)->approveForImport($upload->fresh(), $admin);
    }

    private function createInvitation(User $admin): array
    {
        return app(ClientSetupInvitationService::class)->create([
            'email' => 'import-' . uniqid() . '@example.com',
            'contact_name' => 'Import Client',
            'assigned_to_id' => $admin->id,
        ], $admin);
    }

    private function createUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Import Reviewer',
            'email' => 'import-reviewer-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ], $attributes));
    }
}

class ClientSetupTestImporter implements ClientSetupMigrationImporter
{
    public static int $calls = 0;

    public function import(CrmClientSetupMigrationUpload $upload): array
    {
        self::$calls++;

        return [
            'reference' => 'college-import-123',
            'created' => 1,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];
    }
}
