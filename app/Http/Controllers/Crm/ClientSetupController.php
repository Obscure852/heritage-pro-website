<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\ClientSetupAssignmentRequest;
use App\Http\Requests\Crm\ClientSetupChangeRequestRequest;
use App\Http\Requests\Crm\ClientSetupInvitationRequest;
use App\Http\Requests\Crm\ClientSetupNoteRequest;
use App\Http\Requests\Crm\ClientSetupRevisionCompareRequest;
use App\Http\Requests\Crm\ClientSetupStatusRequest;
use App\Models\Contact;
use App\Models\CrmClientSetupAttachment;
use App\Models\CrmClientSetupChangeRequest;
use App\Models\CrmClientSetupMigrationUpload;
use App\Models\CrmClientSetupSubmission;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\ClientSetup\ClientSetupAcademicService;
use App\Services\ClientSetup\ClientSetupAttachmentService;
use App\Services\ClientSetup\ClientSetupDeletionService;
use App\Services\ClientSetup\ClientSetupInvitationService;
use App\Services\ClientSetup\ClientSetupReviewService;
use App\Services\ClientSetup\ClientSetupRevisionComparisonService;
use App\Services\ClientSetup\ClientSetupSupplementalService;
use App\Services\ClientSetup\ClientSetupMigrationService;
use App\Services\ClientSetup\ClientSetupMigrationImportService;
use App\Services\ClientSetup\ClientSetupWizardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ClientSetupController extends CrmController
{
    public function __construct(
        private readonly ClientSetupInvitationService $invitationService,
        private readonly ClientSetupReviewService $reviewService,
        private readonly ClientSetupWizardService $wizardService,
        private readonly ClientSetupAcademicService $academicService,
        private readonly ClientSetupAttachmentService $attachmentService,
        private readonly ClientSetupDeletionService $deletionService,
        private readonly ClientSetupSupplementalService $supplementalService,
        private readonly ClientSetupMigrationService $migrationService,
        private readonly ClientSetupMigrationImportService $migrationImportService,
        private readonly ClientSetupRevisionComparisonService $revisionComparisonService
    ) {
    }

    public function index(): View
    {
        $filters = [
            'q' => trim((string) request('q', '')),
            'status' => trim((string) request('status', '')),
            'academic_status' => trim((string) request('academic_status', '')),
            'assigned_to_id' => trim((string) request('assigned_to_id', '')),
            'completeness' => trim((string) request('completeness', '')),
            'activity_from' => trim((string) request('activity_from', '')),
            'activity_to' => trim((string) request('activity_to', '')),
        ];

        $query = CrmClientSetupSubmission::query()
            ->with(['lead', 'customer', 'primaryContact', 'assignedTo', 'stageProgress', 'invitations' => fn ($q) => $q->latest()->limit(1)])
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%' . addcslashes($filters['q'], '%_') . '%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('payload->scope->institution_legal_name', 'like', $term)
                        ->orWhereHas('lead', fn ($lead) => $lead->where('company_name', 'like', $term))
                        ->orWhereHas('customer', fn ($customer) => $customer->where('company_name', 'like', $term))
                        ->orWhereHas('invitations', fn ($invitation) => $invitation->where('email', 'like', $term));
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['academic_status'] !== '', fn ($query) => $query->where('academic_status', $filters['academic_status']))
            ->when($filters['assigned_to_id'] !== '', fn ($query) => $query->where('assigned_to_id', $filters['assigned_to_id']))
            ->when($filters['completeness'] === 'complete', fn ($query) => $query->whereIn('academic_status', ['ready', 'submitted', 'approved']))
            ->when($filters['completeness'] === 'incomplete', fn ($query) => $query->whereNotIn('academic_status', ['ready', 'submitted', 'approved']))
            ->when($filters['activity_from'] !== '', fn ($query) => $query->whereDate('last_activity_at', '>=', $filters['activity_from']))
            ->when($filters['activity_to'] !== '', fn ($query) => $query->whereDate('last_activity_at', '<=', $filters['activity_to']))
            ->when($this->crmUser()->isRep(), fn ($query) => $query->where('assigned_to_id', $this->crmUser()->id))
            ->latest('last_activity_at');

        $submissions = $query->paginate(20)->withQueryString();
        $submissions->getCollection()->transform(function (CrmClientSetupSubmission $submission): CrmClientSetupSubmission {
            $submission->setAttribute('supplemental_summary', $this->supplementalService->summary($submission));
            return $submission;
        });

        return view('crm.client-setup.index', [
            'submissions' => $submissions,
            'canDeleteClientSetup' => $this->crmUser()->isAdmin(),
            'filters' => $filters,
            'owners' => $this->owners(),
            'statuses' => $this->statusLabels(),
            'academicStatuses' => [
                'not_started' => 'Not started', 'in_progress' => 'In progress',
                'ready' => 'Ready', 'submitted' => 'Submitted', 'approved' => 'Approved',
            ],
            'stats' => [
                ['value' => CrmClientSetupSubmission::query()->whereIn('status', ['academic_submitted', 'complete_submission'])->count(), 'label' => 'Ready for review'],
                ['value' => CrmClientSetupSubmission::query()->where('status', 'changes_requested')->count(), 'label' => 'Changes requested'],
                ['value' => CrmClientSetupSubmission::query()->where('status', 'approved')->count(), 'label' => 'Approved'],
            ],
        ]);
    }

    public function create(): View
    {
        return view('crm.client-setup.create', [
            'leads' => $this->leadsForSelect(),
            'customers' => Customer::query()->select(['id', 'company_name', 'owner_id'])->orderBy('company_name')->get(),
            'contacts' => Contact::query()->select(['id', 'name', 'email', 'lead_id', 'customer_id'])->orderBy('name')->get(),
            'owners' => $this->owners(),
        ]);
    }

    public function store(ClientSetupInvitationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $result = $this->invitationService->create($data, $this->crmUser());
        $this->invitationService->sendInvitation($result['invitation'], $result['raw_token']);

        return redirect()->route('crm.client-setup.show', $result['submission'])
            ->with('crm_success', 'Client setup invitation created and emailed.')
            ->with('setup_invitation_url', $result['url']);
    }

    public function destroy(CrmClientSetupSubmission $submission): RedirectResponse
    {
        $this->authorizeCrmAdmin();
        $this->deletionService->forceDelete($submission);

        return redirect()
            ->route('crm.client-setup.index')
            ->with('crm_success', 'Client setup record permanently deleted.');
    }

    public function show(CrmClientSetupSubmission $submission): View
    {
        $this->authorizeRecordAccess($submission->assigned_to_id);
        $submission->load([
            'lead', 'customer', 'primaryContact', 'assignedTo',
            'invitations.createdBy', 'stageProgress', 'revisions.user',
            'attachments.uploadedBy', 'migrationUploads.attachment', 'notes.user', 'changeRequests.user', 'changeRequests.resolvedBy',
            'events.user',
        ]);

        return view('crm.client-setup.show', [
            'submission' => $submission,
            'canDeleteClientSetup' => $this->crmUser()->isAdmin(),
            'readiness' => $this->academicService->readiness($submission),
            'progress' => $this->wizardService->progress($submission),
            'stages' => $this->wizardService->stages(),
            'owners' => $this->owners(),
            'statuses' => $this->statusLabels(),
            'setupInvitationUrl' => session('setup_invitation_url'),
            'supplementalSummary' => $this->supplementalService->summary($submission),
        ]);
    }

    public function resend(CrmClientSetupSubmission $submission): RedirectResponse
    {
        $this->authorizeRecordAccess($submission->assigned_to_id);
        $invitation = $submission->invitations()->latest()->firstOrFail();
        $result = $this->invitationService->resend($invitation, $this->crmUser());

        return back()->with('crm_success', 'A fresh setup link was generated and emailed.')
            ->with('setup_invitation_url', $result['url']);
    }

    public function assignment(ClientSetupAssignmentRequest $request, CrmClientSetupSubmission $submission): RedirectResponse
    {
        $this->reviewService->assign($submission, $request->validated()['assigned_to_id'] ?? null, $this->crmUser());

        return back()->with('crm_success', 'Implementation owner updated.');
    }

    public function status(ClientSetupStatusRequest $request, CrmClientSetupSubmission $submission): RedirectResponse
    {
        try {
            $this->reviewService->changeStatus($submission, $request->validated()['status'], $this->crmUser());
        } catch (Throwable $exception) {
            return back()->with('crm_error', $exception->getMessage());
        }

        return back()->with('crm_success', 'Review status updated.');
    }

    public function storeNote(ClientSetupNoteRequest $request, CrmClientSetupSubmission $submission): RedirectResponse
    {
        $this->reviewService->addNote($submission, $request->validated()['body'], $this->crmUser());

        return back()->with('crm_success', 'Internal note added.');
    }

    public function storeChangeRequest(ClientSetupChangeRequestRequest $request, CrmClientSetupSubmission $submission): RedirectResponse
    {
        $this->reviewService->addChangeRequest($submission, $request->validated(), $this->crmUser());

        return back()->with('crm_success', 'Change request recorded and the submission marked for changes.');
    }

    public function resolveChangeRequest(CrmClientSetupSubmission $submission, CrmClientSetupChangeRequest $changeRequest): RedirectResponse
    {
        abort_unless($changeRequest->submission_id === $submission->id, 404);
        $this->reviewService->resolveChangeRequest($changeRequest, $this->crmUser());

        return back()->with('crm_success', 'Change request marked as resolved.');
    }

    public function approveMigrationUpload(CrmClientSetupSubmission $submission, CrmClientSetupMigrationUpload $migrationUpload): RedirectResponse
    {
        abort_unless($migrationUpload->submission_id === $submission->id, 404);

        try {
            $this->migrationService->approveForImport($migrationUpload, $this->crmUser());
        } catch (Throwable $exception) {
            return back()->with('crm_error', $exception->getMessage());
        }

        return back()->with('crm_success', 'Migration workbook approved for a future production import.');
    }

    public function downloadMigrationValidationReport(
        CrmClientSetupSubmission $submission,
        CrmClientSetupMigrationUpload $migrationUpload
    ): StreamedResponse {
        abort_unless($migrationUpload->submission_id === $submission->id, 404);

        $filename = 'client-setup-' . $submission->uuid . '-' . $migrationUpload->kind . '-validation-report.csv';

        return response()->streamDownload(function () use ($migrationUpload): void {
            $output = fopen('php://output', 'wb');

            fputcsv($output, [
                'upload_uuid',
                'kind',
                'template_version',
                'compatibility_status',
                'validation_status',
                'row_number',
                'messages',
            ]);

            $errors = $migrationUpload->migrationErrors()
                ->orderBy('row_number')
                ->orderBy('id')
                ->cursor();

            $hasErrors = false;

            foreach ($errors as $error) {
                $hasErrors = true;
                fputcsv($output, [
                    $migrationUpload->uuid,
                    $migrationUpload->kind,
                    $migrationUpload->template_version,
                    $migrationUpload->template_compatibility_status,
                    $migrationUpload->validation_status,
                    $error->row_number,
                    implode(' ', $error->messages ?: []),
                ]);
            }

            if (! $hasErrors) {
                fputcsv($output, [
                    $migrationUpload->uuid,
                    $migrationUpload->kind,
                    $migrationUpload->template_version,
                    $migrationUpload->template_compatibility_status,
                    $migrationUpload->validation_status,
                    '',
                    'No validation errors.',
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importMigrationUpload(
        CrmClientSetupSubmission $submission,
        CrmClientSetupMigrationUpload $migrationUpload
    ): RedirectResponse {
        abort_unless($migrationUpload->submission_id === $submission->id, 404);
        abort_unless($this->crmUser()->canAccessCrmModule('client_setup', 'admin'), 403);

        try {
            $this->migrationImportService->queue($migrationUpload, $this->crmUser());
        } catch (Throwable $exception) {
            return back()->with('crm_error', $exception->getMessage());
        }

        return back()->with('crm_success', 'Migration import queued for controlled execution.');
    }

    public function downloadAttachment(CrmClientSetupSubmission $submission, CrmClientSetupAttachment $attachment): Response
    {
        abort_unless($attachment->submission_id === $submission->id, 404);
        abort_unless($attachment->scan_status === 'approved', 423, 'This attachment is awaiting security scanning.');

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function print(CrmClientSetupSubmission $submission): View
    {
        $submission->load(['lead', 'customer', 'primaryContact', 'assignedTo', 'stageProgress', 'revisions', 'attachments']);

        return view('crm.client-setup.print', [
            'submission' => $submission,
            'readiness' => $this->academicService->readiness($submission),
            'stages' => $this->wizardService->stages(),
            'supplementalSummary' => $this->supplementalService->summary($submission),
        ]);
    }

    public function compareRevisions(
        ClientSetupRevisionCompareRequest $request,
        CrmClientSetupSubmission $submission
    ): View {
        $this->authorizeRecordAccess($submission->assigned_to_id);

        try {
            $comparison = $this->revisionComparisonService->compare(
                $submission,
                (int) $request->validated('from'),
                (int) $request->validated('to')
            );
        } catch (Throwable $exception) {
            abort(404, $exception->getMessage());
        }

        return view('crm.client-setup.revision-compare', [
            'submission' => $submission->load(['lead', 'customer', 'assignedTo']),
            'comparison' => $comparison,
        ]);
    }

    private function statusLabels(): array
    {
        return [
            'draft' => 'Draft', 'academic_submitted' => 'Academic submitted',
            'supplemental_in_progress' => 'Supplemental in progress',
            'complete_submission' => 'Complete submission', 'under_review' => 'Under review',
            'changes_requested' => 'Changes requested', 'approved' => 'Approved', 'archived' => 'Archived',
        ];
    }
}
