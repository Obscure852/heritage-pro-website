<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientSetup\ClientSetupStageSaveRequest;
use App\Http\Requests\ClientSetup\ClientSetupAttachmentUploadRequest;
use App\Http\Requests\ClientSetup\ClientSetupChangeResponseRequest;
use App\Http\Requests\ClientSetup\ClientSetupMigrationUploadRequest;
use App\Http\Requests\ClientSetup\ClientSetupResumeRequest;
use App\Http\Requests\ClientSetup\ClientSetupVerificationCodeRequest;
use App\Models\CrmClientSetupSubmission;
use App\Models\CrmClientSetupChangeRequest;
use App\Services\ClientSetup\ClientSetupAccessService;
use App\Services\ClientSetup\ClientSetupAttachmentService;
use App\Services\ClientSetup\ClientSetupAcademicService;
use App\Services\ClientSetup\ClientSetupAuditService;
use App\Services\ClientSetup\ClientSetupDraftService;
use App\Services\ClientSetup\ClientSetupInvitationService;
use App\Services\ClientSetup\ClientSetupWizardService;
use App\Services\ClientSetup\ClientSetupReviewService;
use App\Services\ClientSetup\ClientSetupMigrationService;
use App\Services\ClientSetup\ClientSetupNotificationService;
use App\Services\ClientSetup\ClientSetupSupplementalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ClientSetupController extends Controller
{
    public function __construct(
        private readonly ClientSetupAccessService $accessService,
        private readonly ClientSetupAttachmentService $attachmentService,
        private readonly ClientSetupAcademicService $academicService,
        private readonly ClientSetupAuditService $auditService,
        private readonly ClientSetupDraftService $draftService,
        private readonly ClientSetupInvitationService $invitationService,
        private readonly ClientSetupReviewService $reviewService,
        private readonly ClientSetupMigrationService $migrationService,
        private readonly ClientSetupNotificationService $notificationService,
        private readonly ClientSetupSupplementalService $supplementalService,
        private readonly ClientSetupWizardService $wizardService
    ) {
    }

    public function entry(string $token): View|RedirectResponse
    {
        $invitation = $this->invitation($token);

        $this->invitationService->markAccessed($invitation);

        if ($this->accessService->isVerified($invitation)) {
            $submission = $this->draftService->forInvitation($invitation);

            return redirect()->route('client-setup.stage', [
                'token' => $token,
                'stage' => $this->wizardService->firstIncompleteRequiredStage($submission),
            ]);
        }

        return view('client-setup.verify', [
            'invitation' => $invitation,
            'maskedEmail' => $this->maskEmail($invitation->email),
        ]);
    }

    public function resume(): View
    {
        return view('client-setup.resume');
    }

    public function requestResumeLink(ClientSetupResumeRequest $request): RedirectResponse
    {
        try {
            $this->invitationService->resendForEmail($request->validated('email'));
        } catch (Throwable $exception) {
            Log::error('Client setup resume link request failed.', [
                'reason' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('client-setup.resume')
            ->with('client_setup_success', 'If an active setup is associated with that email, a fresh resume link has been sent.');
    }

    public function exit(string $token): View
    {
        $invitation = $this->invitation($token);
        $this->accessService->forget();

        return view('client-setup.exit', [
            'invitation' => $invitation,
            'maskedEmail' => $this->maskEmail($invitation->email),
        ]);
    }

    public function submitAcademic(string $token): RedirectResponse
    {
        $invitation = $this->invitation($token);

        if (! $this->accessService->isVerified($invitation)) {
            return redirect()->route('client-setup.entry', ['token' => $token]);
        }

        $submission = $this->draftService->forInvitation($invitation);
        $readiness = $this->academicService->readiness($submission);

        if (! $readiness['ready']) {
            $stage = $readiness['missing_stages'][0] ?? 'scope';

            return redirect()
                ->route('client-setup.stage', ['token' => $token, 'stage' => $stage])
                ->with('client_setup_error', 'Complete all required academic stages before submitting the academic configuration.');
        }

        $result = DB::transaction(function () use ($invitation, $submission): array {
            $lockedSubmission = CrmClientSetupSubmission::query()
                ->with('stageProgress')
                ->lockForUpdate()
                ->findOrFail($submission->id);

            if (in_array($lockedSubmission->academic_status, ['submitted', 'approved'], true)) {
                return [
                    'submission' => $lockedSubmission->fresh(['invitations', 'assignedTo']),
                    'changed' => false,
                ];
            }

            $now = now();
            $lockedSubmission->forceFill([
                'academic_status' => 'submitted',
                'status' => 'academic_submitted',
                'academic_submitted_at' => $now,
                'last_activity_at' => $now,
            ])->save();

            $revisionNumber = ((int) $lockedSubmission->revisions()->max('revision_number')) + 1;
            $revision = $lockedSubmission->revisions()->create([
                'invitation_id' => $invitation->id,
                'revision_number' => $revisionNumber,
                'source' => 'academic_submission',
                'payload' => $lockedSubmission->payloadArray(),
                'changed_keys' => array_keys($lockedSubmission->payloadArray()),
            ]);

            $this->auditService->record($lockedSubmission, 'academic_submitted', [
                'invitation' => $invitation,
                'metadata' => [
                    'revision_number' => $revision->revision_number,
                    'academic_status' => $lockedSubmission->academic_status,
                ],
            ]);

            return [
                'submission' => $lockedSubmission->fresh(['invitations', 'assignedTo']),
                'changed' => true,
            ];
        });

        if ($result['changed']) {
            $submitted = $result['submission'];
            $this->notificationService->notifySubmission($submitted, 'academic_submitted', [
                'audiences' => ['client', 'crm'],
                'context_key' => 'academic:' . ($submitted->academic_submitted_at?->format('YmdHis') ?? now()->format('YmdHis')),
                'action_url' => route('client-setup.academic-submitted', ['token' => $token]),
            ]);
        }

        return redirect()->route('client-setup.academic-submitted', ['token' => $token]);
    }

    public function academicSubmitted(string $token): View|RedirectResponse
    {
        $invitation = $this->invitation($token);

        if (! $this->accessService->isVerified($invitation)) {
            return redirect()->route('client-setup.entry', ['token' => $token]);
        }

        $submission = $this->draftService->forInvitation($invitation);

        return view('client-setup.academic-submitted', [
            'invitation' => $invitation,
            'submission' => $submission,
            'academicRevisionNumber' => $submission->revisions()->where('source', 'academic_submission')->max('revision_number'),
            'supplementalSummary' => $this->supplementalService->summary($submission),
        ]);
    }

    public function completeSupplemental(string $token): RedirectResponse
    {
        $invitation = $this->invitation($token);

        if (! $this->accessService->isVerified($invitation)) {
            return redirect()->route('client-setup.entry', ['token' => $token]);
        }

        try {
            $completedSubmission = $this->supplementalService->complete(
                $this->draftService->forInvitation($invitation),
                $invitation
            );
        } catch (RuntimeException $exception) {
            return back()->with('client_setup_error', $exception->getMessage());
        }

        $this->notificationService->notifySubmission($completedSubmission->load(['invitations', 'assignedTo']), 'supplemental_received', [
            'audiences' => ['crm'],
            'context_key' => 'supplemental:' . ($completedSubmission->completed_at?->format('YmdHis') ?? now()->format('YmdHis')),
        ]);
        $this->notificationService->notifySubmission($completedSubmission, 'final_submission_received', [
            'audiences' => ['client', 'crm'],
            'context_key' => 'final:' . ($completedSubmission->completed_at?->format('YmdHis') ?? now()->format('YmdHis')),
            'action_url' => route('client-setup.academic-submitted', ['token' => $token]),
        ]);

        return redirect()
            ->route('client-setup.academic-submitted', ['token' => $token])
            ->with('client_setup_success', 'Supplemental setup has been marked complete for implementation review.');
    }

    public function uploadAttachment(ClientSetupAttachmentUploadRequest $request, string $token): RedirectResponse
    {
        $invitation = $this->invitation($token);

        if (! $this->accessService->isVerified($invitation)) {
            return redirect()->route('client-setup.entry', ['token' => $token]);
        }

        $submission = $this->draftService->forInvitation($invitation);

        if (in_array($submission->status, ['approved', 'archived'], true)) {
            return back()->with('client_setup_error', 'This setup submission is no longer accepting attachments.');
        }

        $validated = $request->validated();

        try {
            $this->attachmentService->store(
                $submission,
                $invitation,
                $validated['attachment'],
                $validated['category'],
                $validated['requirement'] ?? 'optional'
            );
        } catch (Throwable $exception) {
            Log::error('Client setup attachment upload failed.', [
                'submission_uuid' => $submission->uuid,
                'reason' => $exception->getMessage(),
            ]);

            return back()->with('client_setup_error', 'The attachment could not be saved. Please try again.');
        }

        return redirect()
            ->route('client-setup.stage', ['token' => $token, 'stage' => $validated['return_stage'] ?? 'evidence_signoff'])
            ->with('client_setup_success', 'Attachment uploaded and queued for security scanning.');
    }

    public function downloadMigrationTemplate(string $token, string $kind): BinaryFileResponse|RedirectResponse
    {
        $invitation = $this->invitation($token);

        if (! $this->accessService->isVerified($invitation)) {
            return redirect()->route('client-setup.entry', ['token' => $token]);
        }

        try {
            $definition = $this->migrationService->template($kind);

            return response()->download(
                $this->migrationService->templatePath($kind),
                $definition['filename'],
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
        } catch (RuntimeException) {
            abort(404);
        }
    }

    public function uploadMigrationTemplate(ClientSetupMigrationUploadRequest $request, string $token): RedirectResponse
    {
        $invitation = $this->invitation($token);

        if (! $this->accessService->isVerified($invitation)) {
            return redirect()->route('client-setup.entry', ['token' => $token]);
        }

        $submission = $this->draftService->forInvitation($invitation);

        try {
            $upload = $this->migrationService->validateAndStore(
                $submission,
                $invitation,
                $request->validated()['kind'],
                $request->file('file')
            );
        } catch (Throwable $exception) {
            Log::error('Client setup migration upload failed.', [
                'submission_uuid' => $submission->uuid,
                'reason' => $exception->getMessage(),
            ]);

            return back()->with('client_setup_error', 'The migration workbook could not be processed. Please use the supplied template.');
        }

        $message = $upload->validation_status === 'validated'
            ? 'Migration workbook uploaded and validated successfully.'
            : 'Migration workbook uploaded; please review the validation errors before the implementation team imports it.';

        return back()->with($upload->validation_status === 'validated' ? 'client_setup_success' : 'client_setup_error', $message);
    }

    public function respondToChangeRequest(
        ClientSetupChangeResponseRequest $request,
        string $token,
        CrmClientSetupChangeRequest $changeRequest
    ): RedirectResponse {
        $invitation = $this->invitation($token);

        if (! $this->accessService->isVerified($invitation)) {
            return redirect()->route('client-setup.entry', ['token' => $token]);
        }

        $this->reviewService->recordClientResponse(
            $changeRequest,
            $invitation,
            $request->validated()['client_response']
        );

        return back()->with('client_setup_success', 'Your response was sent to the implementation team.');
    }

    public function requestCode(string $token): RedirectResponse
    {
        $invitation = $this->invitation($token);

        try {
            $this->invitationService->requestVerificationCode($invitation);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('client-setup.entry', ['token' => $token])
                ->with('client_setup_error', $exception->getMessage());
        }

        return redirect()
            ->route('client-setup.entry', ['token' => $token])
            ->with('client_setup_success', 'A verification code has been sent to ' . $this->maskEmail($invitation->email) . '.');
    }

    public function verify(ClientSetupVerificationCodeRequest $request, string $token): RedirectResponse
    {
        $invitation = $this->invitation($token);

        if (! $this->invitationService->verifyCode($invitation, $request->validated('code'))) {
            return redirect()
                ->route('client-setup.entry', ['token' => $token])
                ->withInput()
                ->with('client_setup_error', 'That verification code is invalid or has expired.');
        }

        $this->accessService->markVerified($invitation);

        return redirect()
            ->route('client-setup.stage', ['token' => $token, 'stage' => 'scope'])
            ->with('client_setup_success', 'Your access has been verified.');
    }

    public function stage(string $token, string $stage): View|RedirectResponse
    {
        $invitation = $this->invitation($token);

        try {
            $stageDefinition = $this->wizardService->stage($stage);
        } catch (RuntimeException) {
            abort(404);
        }

        if (! $this->accessService->isVerified($invitation)) {
            return redirect()->route('client-setup.entry', ['token' => $token]);
        }

        try {
            $submission = $this->draftService->forInvitation($invitation);
        } catch (RuntimeException $exception) {
            Log::warning('Client setup stage could not be opened.', [
                'invitation_uuid' => $invitation->uuid,
                'stage' => $stage,
                'reason' => $exception->getMessage(),
            ]);

            abort(410);
        }

        $navigation = $this->wizardService->navigation($submission, $stage);
        $academicReadiness = $this->academicService->syncAcademicStatus($submission);
        $changeRequests = $submission->changeRequests()
            ->where('status', 'open')
            ->where(function ($query) use ($stage): void {
                $query->whereNull('stage_key')->orWhere('stage_key', $stage);
            })
            ->latest()
            ->get();
        $academicLocked = in_array($submission->academic_status, ['submitted', 'approved'], true)
            && $this->academicService->isAcademicStage($stage);
        $currentNavigation = collect($navigation)->firstWhere('key', $stage);
        $currentIndex = array_search($stage, array_column($navigation, 'key'), true);
        $previousNavigation = $currentIndex === false
            ? null
            : collect(array_slice($navigation, 0, $currentIndex))
                ->reverse()
                ->first(static fn (array $item): bool => ! $item['locked']);
        $nextNavigation = $currentIndex === false
            ? null
            : collect(array_slice($navigation, $currentIndex + 1))
                ->first(static fn (array $item): bool => ! $item['locked']);

        if ($currentNavigation['locked']) {
            return redirect()->route('client-setup.stage', [
                'token' => $token,
                'stage' => $this->wizardService->firstIncompleteRequiredStage($submission),
            ]);
        }

        return view('client-setup.stage', [
            'invitation' => $invitation,
            'submission' => $submission,
            'stage' => $stage,
            'stageDefinition' => $stageDefinition,
            'structuredFields' => $this->academicService->fields($stage),
            'stagePayload' => $submission->payloadArray()[$stage] ?? [],
            'stageProgress' => $submission->stageProgress->firstWhere('stage_key', $stage),
            'navigation' => $navigation,
            'wizardProgress' => $this->wizardService->progress($submission),
            'academicReadiness' => $academicReadiness,
            'academicLocked' => $academicLocked,
            'previousNavigation' => $previousNavigation,
            'nextNavigation' => $nextNavigation,
            'changeRequests' => $changeRequests,
            'supplementalSummary' => $this->supplementalService->summary($submission),
        ]);
    }

    public function saveStage(ClientSetupStageSaveRequest $request, string $token, string $stage): RedirectResponse
    {
        $invitation = $this->invitation($token);

        try {
            $this->wizardService->stage($stage);
        } catch (RuntimeException) {
            abort(404);
        }

        if (! $this->accessService->isVerified($invitation)) {
            return redirect()->route('client-setup.entry', ['token' => $token]);
        }

        $validated = $request->validated();
        $payload = $validated['data'] ?? ($validated['payload'] ?? null);
        $payloadWasJson = $payload === null;

        if ($payloadWasJson) {
            $payload = json_decode((string) ($validated['payload_json'] ?? '{}'), true);
        }

        if (! is_array($payload) || ($payloadWasJson && json_last_error() !== JSON_ERROR_NONE)) {
            return back()
                ->withInput()
                ->with('client_setup_error', 'The saved stage payload must be a valid JSON object.');
        }

        $action = $validated['action'] ?? 'save';
        $structuredSubmission = array_key_exists('data', $validated);
        $validation = $structuredSubmission && $this->academicService->hasStructuredSchema($stage)
            ? $this->academicService->validateStage($stage, $payload)
            : ['payload' => $payload, 'errors' => [], 'error_details' => []];
        $payload = $validation['payload'];
        $validationErrors = $validation['errors'];
        $validationErrorDetails = $validation['error_details'] ?? [];
        $stageStatus = $action === 'continue' ? 'complete' : $validated['status'];

        if ($stage === 'results_lifecycle' && $stageStatus === 'complete') {
            $submission = $this->draftService->forInvitation($invitation);
            $uploadedCategories = $submission->attachments
                ->pluck('category')
                ->map(static fn ($category): string => strtolower(trim((string) $category)))
                ->all();

            foreach (config('client_setup.required_stage_attachments.results_lifecycle', []) as $requiredCategory) {
                if (! in_array(strtolower($requiredCategory), $uploadedCategories, true)) {
                    $message = $requiredCategory . ' attachment is required before completing this stage.';
                    $validationErrors[] = $message;
                    $validationErrorDetails[] = ['path' => null, 'message' => $message];
                }
            }
        }

        if ($validationErrors !== [] && $stageStatus === 'complete') {
            $stageStatus = 'in_progress';
        }

        try {
            $savedSubmission = $this->draftService->saveStage(
                $invitation,
                $stage,
                $payload,
                $stageStatus,
                $validationErrors,
                $validationErrorDetails
            );
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->with('client_setup_error', $exception->getMessage());
        }

        $this->academicService->syncAcademicStatus($savedSubmission);

        if ($validationErrors !== [] && $action === 'continue') {
            return back()
                ->withInput()
                ->with('client_setup_error', 'Please complete the highlighted requirements before continuing.');
        }

        if ($action === 'exit') {
            $this->accessService->forget();

            return redirect()
                ->route('client-setup.exit', ['token' => $token])
                ->with('client_setup_success', 'Your progress has been saved. You can return using the original setup link.');
        }

        if ($action === 'continue') {
            $nextStage = $this->wizardService->nextStage($savedSubmission, $stage);

            if ($nextStage) {
                return redirect()
                    ->route('client-setup.stage', ['token' => $token, 'stage' => $nextStage])
                    ->with('client_setup_success', 'Stage saved. You can safely leave and return later.');
            }
        }

        return redirect()
            ->route('client-setup.stage', ['token' => $token, 'stage' => $stage])
            ->with('client_setup_success', 'Stage saved. You can safely leave and return later.');
    }

    private function invitation(string $token)
    {
        $invitation = $this->invitationService->findByToken($token);

        abort_unless($invitation && $invitation->isUsable(), 404);

        return $invitation;
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($local === '' || $domain === '') {
            return 'your email address';
        }

        return substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 1)) . '@' . $domain;
    }
}
