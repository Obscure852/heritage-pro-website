<?php

namespace Tests\Feature;

use App\Mail\ClientSetupVerificationCodeMail;
use App\Models\CrmClientSetupNotification;
use App\Models\User;
use App\Services\ClientSetup\ClientSetupInvitationService;
use App\Services\ClientSetup\ClientSetupMigrationService;
use App\Services\ClientSetup\ClientSetupSupplementalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClientSetupMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_client_can_download_clean_templates_and_upload_a_workbook_for_validation(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'migration',
        ]))->assertOk()
            ->assertSee('Download template')
            ->assertSee('Instructions/Data Dictionary');

        $this->get(route('client-setup.migration-template.download', [
            'token' => $invitation['raw_token'],
            'kind' => 'staff',
        ]))->assertOk()->assertHeader('content-disposition', 'attachment; filename=heritage-client-setup-staff-template-v1.0.xlsx');

        $csv = implode(',', config('client_setup.migration_templates.staff.headings')) . "\n"
            . "Thato,,Molapisi,thato@example.com,02/02/1976,M,Lecturer,74000001,ID1,Motswana,Gaborone,Plot 1,Yes,Current,Engineering,Lecturer,,2024,tmolapisi\n";

        $this->post(route('client-setup.migration-upload', ['token' => $invitation['raw_token']]), [
            'kind' => 'staff',
            'file' => UploadedFile::fake()->createWithContent('staff.csv', $csv),
        ])->assertRedirect();

        $this->assertDatabaseHas('crm_client_setup_migration_uploads', [
            'submission_id' => $invitation['submission']->id,
            'kind' => 'staff',
            'validation_status' => 'validated',
            'template_version' => '1.0',
            'template_compatibility_status' => 'compatible',
            'row_count' => 1,
            'valid_row_count' => 1,
            'error_count' => 0,
        ]);
        $this->assertDatabaseHas('crm_client_setup_attachments', [
            'submission_id' => $invitation['submission']->id,
            'category' => 'migration_staff',
            'scan_status' => 'pending',
        ]);
    }

    public function test_explicit_optional_deferrals_can_finalize_supplemental_setup(): void
    {
        $invitation = $this->createInvitation();
        $service = app(ClientSetupSupplementalService::class);
        $submission = $invitation['submission'];

        $submission->forceFill([
            'academic_status' => 'submitted',
            'status' => 'academic_submitted',
        ])->save();

        app(\App\Services\ClientSetup\ClientSetupDraftService::class)->saveStage(
            $invitation['invitation'],
            'migration',
            ['migration_scope' => ['no_migration'], 'migration_datasets' => [], 'migration_data_quality_issues' => ''],
            'complete'
        );
        app(\App\Services\ClientSetup\ClientSetupDraftService::class)->saveStage(
            $invitation['invitation'],
            'integrations_access',
            ['integration_scope' => ['none'], 'integrations' => [], 'user_roles' => [], 'access_controls' => ''],
            'complete'
        );
        app(\App\Services\ClientSetup\ClientSetupDraftService::class)->saveStage(
            $invitation['invitation'],
            'finance',
            [
                'finance_scope_decision' => 'defer',
                'finance_deferred_owner' => 'Implementation team',
                'finance_deferred_date' => '2026-12',
                'finance_capabilities' => [],
                'finance_registration_result_rules' => '',
            ],
            'complete'
        );

        $result = $service->complete($submission->fresh(['stageProgress']), $invitation['invitation']);
        $completedAt = $result->completed_at;

        $retry = $service->complete($result->fresh(['stageProgress']), $invitation['invitation']);

        $this->assertSame('complete_submission', $result->status);
        $this->assertSame($completedAt?->toISOString(), $retry->completed_at?->toISOString());
        $this->assertSame(1, $submission->events()->where('event_type', 'supplemental_setup_completed')->count());
        $this->assertDatabaseHas('crm_client_setup_events', [
            'submission_id' => $submission->id,
            'event_type' => 'supplemental_setup_completed',
        ]);
    }

    public function test_supplemental_completion_notifications_are_idempotent_on_retry(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);

        $invitation['submission']->forceFill([
            'academic_status' => 'submitted',
            'status' => 'academic_submitted',
        ])->save();

        $draftService = app(\App\Services\ClientSetup\ClientSetupDraftService::class);
        $draftService->saveStage($invitation['invitation'], 'migration', [
            'migration_scope' => ['no_migration'],
            'migration_datasets' => [],
            'migration_data_quality_issues' => '',
        ], 'complete');
        $draftService->saveStage($invitation['invitation'], 'integrations_access', [
            'integration_scope' => ['none'],
            'integrations' => [],
            'user_roles' => [],
            'access_controls' => '',
        ], 'complete');
        $draftService->saveStage($invitation['invitation'], 'finance', [
            'finance_scope_decision' => 'defer',
            'finance_deferred_owner' => 'Implementation team',
            'finance_deferred_date' => '2026-12',
            'finance_capabilities' => [],
            'finance_registration_result_rules' => '',
        ], 'complete');

        $route = route('client-setup.supplemental-complete', ['token' => $invitation['raw_token']]);
        $this->post($route)->assertRedirect();
        $firstCounts = CrmClientSetupNotification::query()
            ->where('submission_id', $invitation['submission']->id)
            ->whereIn('event_key', ['supplemental_received', 'final_submission_received'])
            ->selectRaw('event_key, count(*) as total')
            ->groupBy('event_key')
            ->pluck('total', 'event_key')
            ->all();

        $this->post($route)->assertRedirect();
        $secondCounts = CrmClientSetupNotification::query()
            ->where('submission_id', $invitation['submission']->id)
            ->whereIn('event_key', ['supplemental_received', 'final_submission_received'])
            ->selectRaw('event_key, count(*) as total')
            ->groupBy('event_key')
            ->pluck('total', 'event_key')
            ->all();

        $this->assertSame($firstCounts, $secondCounts);
        $this->assertSame('complete_submission', $invitation['submission']->fresh()->status);
    }

    public function test_migration_upload_records_row_level_validation_errors_without_importing_data(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);

        $csv = implode(',', config('client_setup.migration_templates.students.headings')) . "\n"
            . "Mpho,Kgosi,,IMP-STU-001,X,01/01/2005,Motswana,,,,active,DIP-IS,Year 1,,,,\n"
            . "Lorato,Molefe,,IMP-STU-001,F,not-a-date,Motswana,,,,active,DIP-IS,Year 1,,,,\n";

        $this->post(route('client-setup.migration-upload', ['token' => $invitation['raw_token']]), [
            'kind' => 'students',
            'file' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])->assertRedirect();

        $this->assertDatabaseHas('crm_client_setup_migration_uploads', [
            'submission_id' => $invitation['submission']->id,
            'kind' => 'students',
            'validation_status' => 'has_errors',
            'row_count' => 2,
            'valid_row_count' => 0,
        ]);
        $this->assertDatabaseMissing('crm_client_setup_events', [
            'submission_id' => $invitation['submission']->id,
            'event_type' => 'migration_imported',
        ]);
    }

    public function test_migration_upload_rejects_an_unsupported_template_header(): void
    {
        Mail::fake();
        $invitation = $this->createInvitation();
        $this->verifyInvitation($invitation);
        $headers = config('client_setup.migration_templates.staff.headings');
        $headers[0] = 'renamed_first_name';
        $csv = implode(',', $headers) . "\n"
            . ",,Molapisi,thato@example.com,02/02/1976,M,Lecturer,74000001,ID1,Motswana,Gaborone,Plot 1,Yes,Current,Engineering,Lecturer,,2024,tmolapisi\n";

        $this->post(route('client-setup.migration-upload', ['token' => $invitation['raw_token']]), [
            'kind' => 'staff',
            'file' => UploadedFile::fake()->createWithContent('staff-unsupported.csv', $csv),
        ])->assertRedirect();

        $this->assertDatabaseHas('crm_client_setup_migration_uploads', [
            'submission_id' => $invitation['submission']->id,
            'template_compatibility_status' => 'incompatible',
            'validation_status' => 'has_errors',
        ]);
    }

    public function test_crm_approval_requires_scan_and_then_records_approval_for_future_import(): void
    {
        $admin = User::query()->create([
            'name' => 'Phase Five Reviewer',
            'email' => 'phase5-reviewer-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
        ]);
        $invitation = app(ClientSetupInvitationService::class)->create([
            'email' => 'phase5-approval-' . uniqid() . '@example.com',
        ], $admin);
        $csv = implode(',', config('client_setup.migration_templates.staff.headings')) . "\n"
            . "Thato,,Molapisi,thato@example.com,02/02/1976,M,Lecturer,74000001,ID1,Motswana,Gaborone,Plot 1,Yes,Current,Engineering,Lecturer,,2024,tmolapisi\n";
        $upload = app(ClientSetupMigrationService::class)->validateAndStore(
            $invitation['submission'],
            $invitation['invitation'],
            'staff',
            UploadedFile::fake()->createWithContent('staff.csv', $csv)
        );

        $this->actingAs($admin)->patch(route('crm.client-setup.migration-uploads.approve', [
            'submission' => $invitation['submission'],
            'migrationUpload' => $upload,
        ]))->assertRedirect();
        $this->assertDatabaseHas('crm_client_setup_migration_uploads', [
            'id' => $upload->id,
            'crm_approval_status' => 'pending',
        ]);

        $upload->attachment()->update(['scan_status' => 'approved']);
        $this->actingAs($admin)->patch(route('crm.client-setup.migration-uploads.approve', [
            'submission' => $invitation['submission'],
            'migrationUpload' => $upload,
        ]))->assertRedirect();

        $this->assertDatabaseHas('crm_client_setup_migration_uploads', [
            'id' => $upload->id,
            'crm_approval_status' => 'approved',
            'crm_approved_by_id' => $admin->id,
        ]);
    }

    private function createInvitation(): array
    {
        return app(ClientSetupInvitationService::class)->create([
            'email' => 'phase5-' . uniqid() . '@example.com',
            'contact_name' => 'Phase Five Client',
        ]);
    }

    private function verifyInvitation(array $invitation): void
    {
        $this->post(route('client-setup.verification-code', ['token' => $invitation['raw_token']]));
        $code = null;

        Mail::assertSent(ClientSetupVerificationCodeMail::class, function (ClientSetupVerificationCodeMail $mail) use (&$code): bool {
            $code = $mail->code;
            return true;
        });

        $this->post(route('client-setup.verify', ['token' => $invitation['raw_token']]), ['code' => $code]);
    }
}
