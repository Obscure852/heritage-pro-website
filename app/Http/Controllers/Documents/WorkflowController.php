<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\PublishDocumentRequest;
use App\Http\Requests\Documents\ReviewDocumentRequest;
use App\Http\Requests\Documents\SubmitForReviewRequest;
use App\Models\Document;
use App\Models\User;
use App\Policies\DocumentPolicy;
use App\Services\Documents\DocumentSettingService;
use App\Services\Documents\WorkflowService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class WorkflowController extends Controller {
    protected WorkflowService $workflowService;
    protected DocumentSettingService $settingService;

    public function __construct(WorkflowService $workflowService, DocumentSettingService $settingService) {
        $this->workflowService = $workflowService;
        $this->settingService = $settingService;
    }

    /**
     * Submit a document for review.
     */
    public function submitForReview(SubmitForReviewRequest $request, Document $document): JsonResponse {
        $this->authorize('update', $document);

        if (!$this->settingService->get('approval.require_approval', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Approval workflow is not enabled.',
            ], 422);
        }

        try {
            $validated = $request->validated();
            $deadline = isset($validated['deadline']) ? Carbon::parse($validated['deadline']) : null;

            $this->workflowService->submitForReview(
                $document,
                $validated['reviewer_ids'],
                $validated['notes'] ?? null,
                $deadline,
                $request->user()
            );

            $document->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Document submitted for review.',
                'status' => $document->status,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Withdraw a document submission before review begins.
     */
    public function withdraw(Request $request, Document $document): JsonResponse {
        if ($document->owner_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the document owner can withdraw a submission.',
            ], 403);
        }

        try {
            $this->workflowService->withdrawSubmission($document, $request->user());

            $document->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Submission withdrawn successfully.',
                'status' => $document->status,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Review a document (approve, reject, or request revision).
     */
    public function review(ReviewDocumentRequest $request, Document $document): JsonResponse {
        Gate::authorize('approve-documents');

        try {
            $validated = $request->validated();

            $this->workflowService->reviewDocument(
                $document,
                $request->user(),
                $validated['action'],
                $validated['comments'] ?? null
            );

            $document->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully.',
                'status' => $document->status,
                'action' => $validated['action'],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Publish an approved document with visibility settings.
     */
    public function publish(PublishDocumentRequest $request, Document $document): JsonResponse {
        $this->authorize('publish', $document);

        try {
            $validated = $request->validated();

            $this->workflowService->publishDocument(
                $document,
                $validated['visibility'],
                $validated['roles'] ?? null,
                $request->user()
            );

            $document->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Document published successfully.',
                'status' => $document->status,
                'visibility' => $document->visibility,
                'published_at' => $document->published_at?->toIso8601String(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Unpublish a document, resetting to draft status.
     */
    public function unpublish(Request $request, Document $document): JsonResponse {
        $this->authorize('publish', $document);

        try {
            $this->workflowService->unpublishDocument($document, $request->user());

            $document->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Document unpublished successfully.',
                'status' => $document->status,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Archive a published document.
     */
    public function archive(Request $request, Document $document): JsonResponse {
        $this->authorize('publish', $document);

        try {
            $this->workflowService->archiveDocument($document, $request->user());

            $document->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Document archived successfully.',
                'status' => $document->status,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Unarchive an archived document, restoring to approved status.
     */
    public function unarchive(Request $request, Document $document): JsonResponse {
        $this->authorize('publish', $document);

        try {
            $this->workflowService->unarchiveDocument($document, $request->user());

            $document->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Document unarchived successfully.',
                'status' => $document->status,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Search for users eligible to review documents.
     *
     * Returns users with APPROVER_ROLES matching the search query.
     */
    public function searchReviewers(Request $request): JsonResponse {
        $this->authorize('viewAny', Document::class);

        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->input('q');

        $users = User::where('status', 'Current')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', DocumentPolicy::APPROVER_ROLES);
            })
            ->where(function ($q) use ($query) {
                $q->where('firstname', 'like', "%{$query}%")
                  ->orWhere('lastname', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'firstname', 'lastname', 'email', 'department')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
