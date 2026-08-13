<?php

namespace Tests\Feature;

use App\Mail\ClientSetupVerificationCodeMail;
use App\Models\CrmClientSetupChangeRequest;
use App\Models\CrmClientSetupSubmission;
use App\Models\User;
use App\Services\ClientSetup\ClientSetupAccessService;
use App\Services\ClientSetup\ClientSetupDraftService;
use App\Services\ClientSetup\ClientSetupInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientSetupPilotTest extends TestCase
{
    use RefreshDatabase;

    public function test_synthetic_pilot_covers_academic_optional_review_change_and_archive_journey(): void
    {
        Mail::fake();
        Storage::fake('documents');
        $admin = $this->createUser();
        $invitation = app(ClientSetupInvitationService::class)->create([
            'email' => 'pilot-' . uniqid() . '@example.com',
            'contact_name' => 'Synthetic Pilot Client',
        ], $admin);

        $this->verifyInvitation($invitation);

        $this->from(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))->patch(route('client-setup.stage.save', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]), [
            'data' => ['institution_legal_name' => 'Synthetic Pilot College'],
            'status' => 'in_progress',
            'action' => 'save',
        ])->assertRedirect();

        $draftService = app(ClientSetupDraftService::class);

        foreach (array_filter(config('client_setup.stages'), static fn (array $stage): bool => $stage['required_for_academic']) as $stage) {
            $draftService->saveStage(
                $invitation['invitation'],
                $stage['key'],
                $this->validStagePayload($stage['key']),
                'complete'
            );
        }

        foreach ([
            'Result slip' => 'pilot-result-slip.pdf',
            'Transcript' => 'pilot-transcript.pdf',
        ] as $category => $filename) {
            $this->post(route('client-setup.attachment-upload', ['token' => $invitation['raw_token']]), [
                'category' => $category,
                'requirement' => 'required',
                'return_stage' => 'results_lifecycle',
                'attachment' => UploadedFile::fake()->create($filename, 20, 'application/pdf'),
            ])->assertRedirect();
        }

        $this->post(route('client-setup.academic-submit', ['token' => $invitation['raw_token']]))
            ->assertRedirect(route('client-setup.academic-submitted', ['token' => $invitation['raw_token']]));

        $staffCsv = implode(',', config('client_setup.migration_templates.staff.headings')) . "\n"
            . "Thato,,Molapisi,thato@example.com,02/02/1976,M,Lecturer,74000001,ID1,Motswana,Gaborone,Plot 1,Yes,Current,Engineering,Lecturer,,2024,tmolapisi\n";

        $this->post(route('client-setup.migration-upload', ['token' => $invitation['raw_token']]), [
            'kind' => 'staff',
            'file' => UploadedFile::fake()->createWithContent('pilot-staff.csv', $staffCsv),
        ])->assertRedirect();

        $draftService->saveStage($invitation['invitation'], 'migration', [
            'migration_scope' => ['no_migration'],
        ], 'complete');
        $draftService->saveStage($invitation['invitation'], 'integrations_access', [
            'integration_scope' => ['none'],
            'integrations' => [],
            'user_roles' => [],
            'access_controls' => '',
        ], 'complete');
        $this->post(route('client-setup.supplemental-complete', ['token' => $invitation['raw_token']]))
            ->assertRedirect();

        $submission = $invitation['submission']->fresh();
        $this->assertSame('complete_submission', $submission->status);
        $this->assertSame('submitted', $submission->academic_status);
        $this->assertDatabaseHas('crm_client_setup_migration_uploads', [
            'submission_id' => $submission->id,
            'validation_status' => 'validated',
        ]);

        $this->actingAs($admin)->post(route('crm.client-setup.change-requests.store', $submission), [
            'stage_key' => 'scope',
            'field_key' => 'scope.institution_legal_name',
            'body' => 'Confirm the registered institution name.',
        ])->assertRedirect();

        $changeRequest = CrmClientSetupChangeRequest::query()->where('submission_id', $submission->id)->firstOrFail();
        Auth::logout();
        $this->post(route('client-setup.change-request.respond', [
            'token' => $invitation['raw_token'],
            'changeRequest' => $changeRequest,
        ]), [
            'client_response' => 'The registered institution name is confirmed.',
        ])->assertRedirect();

        $this->actingAs($admin)->patch(route('crm.client-setup.change-requests.resolve', [$submission, $changeRequest]))
            ->assertRedirect();
        $this->actingAs($admin)->patch(route('crm.client-setup.status', $submission), ['status' => 'under_review'])
            ->assertRedirect();
        $this->actingAs($admin)->patch(route('crm.client-setup.status', $submission), ['status' => 'approved'])
            ->assertRedirect();
        $this->actingAs($admin)->patch(route('crm.client-setup.status', $submission), ['status' => 'archived'])
            ->assertRedirect();

        $this->assertDatabaseHas('crm_client_setup_submissions', [
            'id' => $submission->id,
            'status' => 'archived',
            'academic_status' => 'submitted',
        ]);
        $this->assertDatabaseHas('crm_client_setup_events', [
            'submission_id' => $submission->id,
            'event_type' => 'client_change_response_received',
        ]);
    }

    private function createUser(): User
    {
        return User::query()->create([
            'name' => 'Synthetic Pilot Reviewer',
            'email' => 'pilot-reviewer-' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'active' => true,
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

        $this->post(route('client-setup.verify', ['token' => $invitation['raw_token']]), ['code' => $code])
            ->assertRedirect();
    }

    private function validStagePayload(string $stageKey): array
    {
        $payload = $this->fillFields(config("client_setup_academic.stages.{$stageKey}", []), $stageKey);

        if ($stageKey === 'scope') {
            $payload['submission_date'] = now()->toDateString();
        }

        return $payload;
    }

    private function fillFields(array $fields, string $stageKey): array
    {
        $payload = [];

        foreach ($fields as $field) {
            if (($field['requirement'] ?? 'O') !== 'R' && ! isset($field['required_when'])) {
                continue;
            }

            if ($field['type'] === 'repeatable') {
                $row = $this->fillFields($field['fields'] ?? [], $stageKey);

                if ($stageKey === 'institution' && $field['key'] === 'responsible_contacts') {
                    $row['role'] = 'academic_lead';
                    $row['is_primary'] = true;
                }

                if ($stageKey === 'institution' && $field['key'] === 'campuses') {
                    $row['active'] = true;
                }

                if ($stageKey === 'programmes' && $field['key'] === 'programmes') {
                    $row['active'] = true;
                }

                if ($stageKey === 'assessment' && $field['key'] === 'assessment_components') {
                    $row['weight_percent'] = 100;
                }

                if ($stageKey === 'assessment' && $field['key'] === 'grade_bands') {
                    $row['minimum_mark'] = 0;
                    $row['maximum_mark'] = 100;
                }

                $payload[$field['key']] = [$row];
                continue;
            }

            if (isset($field['required_when']) && $field['required_when']['field'] === 'curriculum_in_scope') {
                $payload['curriculum_in_scope'] = true;
            }

            if ($field['type'] === 'boolean') {
                $payload[$field['key']] = $field['must_be_true'] ?? false;
            } elseif ($field['type'] === 'multiselect') {
                $payload[$field['key']] = [array_key_first($field['options'])];
            } elseif ($field['type'] === 'select') {
                $payload[$field['key']] = array_key_first($field['options']);
            } elseif ($field['type'] === 'number') {
                $payload[$field['key']] = $field['min'] ?? 1;
            } elseif ($field['type'] === 'date') {
                $payload[$field['key']] = now()->toDateString();
            } elseif ($field['type'] === 'month') {
                $payload[$field['key']] = now()->format('Y-m');
            } elseif ($field['type'] === 'email') {
                $payload[$field['key']] = 'synthetic@example.com';
            } else {
                $payload[$field['key']] = 'Synthetic ' . strtolower(str_replace('_', ' ', $field['key'])) . ' information';
            }
        }

        if ($stageKey === 'curriculum') {
            $payload['curriculum_in_scope'] = true;
            $payload['curriculum_versions'] = [[
                'programme_code' => 'SYN-001',
                'code' => 'SYN-001-V1',
                'name' => 'Synthetic curriculum',
                'effective_from' => now()->toDateString(),
                'total_credits' => 120,
                'modules' => [[
                    'code' => 'SYN-MOD-001',
                    'title' => 'Synthetic module',
                    'year_level' => '1',
                    'semester' => '1',
                    'credits' => 12,
                    'core_elective' => 'core',
                ]],
            ]];
        }

        return $payload;
    }
}
