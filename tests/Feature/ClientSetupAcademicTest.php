<?php

namespace Tests\Feature;

use App\Models\CrmClientSetupInvitation;
use App\Models\CrmClientSetupNotification;
use App\Models\CrmClientSetupStageProgress;
use App\Services\ClientSetup\ClientSetupAccessService;
use App\Services\ClientSetup\ClientSetupAcademicService;
use App\Services\ClientSetup\ClientSetupAttachmentService;
use App\Services\ClientSetup\ClientSetupDraftService;
use App\Services\ClientSetup\ClientSetupInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientSetupAcademicTest extends TestCase
{
    use RefreshDatabase;

    public function test_structured_stage_save_records_validation_errors_and_does_not_complete_an_incomplete_stage(): void
    {
        Mail::fake();
        $invitation = $this->createVerifiedInvitation();

        $freezeResponse = $this->from(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))->patch(route('client-setup.stage.save', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]), [
            'data' => ['institution_legal_name' => 'Synthetic College'],
            'status' => 'in_progress',
            'action' => 'continue',
        ])->assertRedirect(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]));

        $this->assertDatabaseHas('crm_client_setup_stage_progress', [
            'stage_key' => 'scope',
            'status' => 'in_progress',
        ]);
        $progress = CrmClientSetupStageProgress::query()
            ->where('submission_id', $invitation['submission']->id)
            ->where('stage_key', 'scope')
            ->firstOrFail();

        $this->assertNotEmpty($progress->validation_errors);
        $this->assertContains('prepared_by_name', array_column($progress->validation_error_details, 'path'));

        $this->get(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))
            ->assertOk()
            ->assertSee('id="client_setup_prepared_by_name_error"', false)
            ->assertSee('aria-describedby="client_setup_prepared_by_name_error"', false);
    }

    public function test_complete_academic_payload_can_be_submitted_and_freezes_academic_stages(): void
    {
        Mail::fake();
        Storage::fake('documents');
        $invitation = $this->createVerifiedInvitation();
        $draftService = app(ClientSetupDraftService::class);

        foreach (array_filter(config('client_setup.stages'), static fn (array $stage): bool => $stage['required_for_academic']) as $stage) {
            $draftService->saveStage(
                $invitation['invitation'],
                $stage['key'],
                $this->validStagePayload($stage['key']),
                'complete'
            );
        }

        $attachmentService = app(ClientSetupAttachmentService::class);
        foreach ([
            'Result slip' => 'result-slip.pdf',
            'Transcript' => 'transcript.pdf',
        ] as $category => $filename) {
            $attachmentService->store(
                $invitation['submission'],
                $invitation['invitation'],
                UploadedFile::fake()->create($filename, 20, 'application/pdf'),
                $category,
                'required'
            );
        }

        $readiness = app(ClientSetupAcademicService::class)->readiness($invitation['submission']->fresh(['stageProgress']));
        $this->assertTrue($readiness['ready'], json_encode($readiness));

        $this->post(route('client-setup.academic-submit', ['token' => $invitation['raw_token']]))
            ->assertRedirect(route('client-setup.academic-submitted', ['token' => $invitation['raw_token']]));

        $this->post(route('client-setup.academic-submit', ['token' => $invitation['raw_token']]))
            ->assertRedirect(route('client-setup.academic-submitted', ['token' => $invitation['raw_token']]));

        $this->assertDatabaseHas('crm_client_setup_submissions', [
            'id' => $invitation['submission']->id,
            'academic_status' => 'submitted',
            'status' => 'academic_submitted',
        ]);
        $this->assertDatabaseHas('crm_client_setup_revisions', [
            'submission_id' => $invitation['submission']->id,
            'source' => 'academic_submission',
        ]);
        $this->assertDatabaseHas('crm_client_setup_events', [
            'submission_id' => $invitation['submission']->id,
            'event_type' => 'academic_submitted',
        ]);
        $this->assertSame(1, $invitation['submission']->events()->where('event_type', 'academic_submitted')->count());
        $this->assertSame(2, CrmClientSetupNotification::query()->where([
            'submission_id' => $invitation['submission']->id,
            'event_key' => 'academic_submitted',
        ])->count());

        $freezeResponse = $this->from(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]))->patch(route('client-setup.stage.save', [
            'token' => $invitation['raw_token'],
            'stage' => 'scope',
        ]), [
            'data' => $this->validStagePayload('scope'),
            'status' => 'in_progress',
            'action' => 'save',
        ]);

        $freezeResponse
            ->assertRedirect(route('client-setup.stage', [
                'token' => $invitation['raw_token'],
                'stage' => 'scope',
            ]))
            ->assertSessionHas('client_setup_error', 'The academic configuration has been submitted and is locked for review.');

        $this->patch(route('client-setup.stage.save', [
            'token' => $invitation['raw_token'],
            'stage' => 'migration',
        ]), [
            'data' => ['migration_scope' => ['no_migration']],
            'status' => 'complete',
            'action' => 'save',
        ])->assertRedirect(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'migration',
        ]));

        $this->assertDatabaseHas('crm_client_setup_submissions', [
            'id' => $invitation['submission']->id,
            'status' => 'supplemental_in_progress',
        ]);
    }

    public function test_readiness_rejects_assessment_weights_that_do_not_total_one_hundred(): void
    {
        $service = app(ClientSetupAcademicService::class);

        $result = $service->validateStage('assessment', [
            'assessment_types' => ['exam'],
            'assessment_components' => [[
                'category' => 'Exam',
                'sequence' => 'Final',
                'marked_out_of' => 100,
                'weight_percent' => 80,
                'compulsory' => true,
                'shown_on_result_slip' => true,
            ]],
        ]);

        $this->assertContains('Assessment component weights must total 100% (currently 80.00%).', $result['errors']);
    }

    public function test_policy_attachment_is_stored_privately_and_audited(): void
    {
        Mail::fake();
        Storage::fake('documents');
        $invitation = $this->createVerifiedInvitation();
        $file = UploadedFile::fake()->create('assessment-policy.pdf', 40, 'application/pdf');

        $this->post(route('client-setup.attachment-upload', ['token' => $invitation['raw_token']]), [
            'category' => 'Assessment policy',
            'requirement' => 'required',
            'attachment' => $file,
        ])->assertRedirect(route('client-setup.stage', [
            'token' => $invitation['raw_token'],
            'stage' => 'evidence_signoff',
        ]));

        $this->assertDatabaseHas('crm_client_setup_attachments', [
            'submission_id' => $invitation['submission']->id,
            'category' => 'Assessment policy',
            'original_name' => 'assessment-policy.pdf',
            'scan_status' => 'pending',
        ]);
        $attachment = $invitation['submission']->attachments()->firstOrFail();
        Storage::disk('documents')->assertExists($attachment->path);
        $this->assertDatabaseHas('crm_client_setup_events', [
            'submission_id' => $invitation['submission']->id,
            'event_type' => 'attachment_uploaded',
        ]);
    }

    private function createVerifiedInvitation(): array
    {
        $invitation = app(ClientSetupInvitationService::class)->create([
            'email' => 'academic-' . uniqid() . '@example.com',
            'contact_name' => 'Synthetic Academic Client',
        ]);

        app(ClientSetupAccessService::class)->markVerified($invitation['invitation']);

        return $invitation;
    }

    private function validStagePayload(string $stageKey): array
    {
        $payload = $this->fillFields(config("client_setup_academic.stages.{$stageKey}", []), $stageKey);

        if ($stageKey === 'scope') {
            $payload['submission_date'] = now()->toDateString();
        }

        return $payload;
    }

    private function fillFields(array $fields, string $stageKey, int $depth = 0): array
    {
        $payload = [];

        foreach ($fields as $field) {
            if (($field['requirement'] ?? 'O') !== 'R' && ! isset($field['required_when'])) {
                continue;
            }

            if ($field['type'] === 'repeatable') {
                $row = $this->fillFields($field['fields'] ?? [], $stageKey, $depth + 1);

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

            if (isset($field['required_when'])) {
                if ($field['required_when']['field'] === 'curriculum_in_scope') {
                    $payload['curriculum_in_scope'] = true;
                }
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
