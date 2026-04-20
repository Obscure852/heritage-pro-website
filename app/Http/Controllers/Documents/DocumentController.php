<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreDocumentRequest;
use App\Http\Requests\Documents\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\DocumentCategory;
use App\Models\DocumentFavorite;
use App\Models\DocumentFolder;
use App\Models\DocumentShare;
use App\Models\DocumentTag;
use App\Models\DocumentVersion;
use App\Mail\Documents\LegalHoldPlacedMail;
use App\Mail\Documents\LegalHoldRemovedMail;
use App\Models\DocumentNotification;
use App\Models\User;
use App\Policies\DocumentFolderPolicy;
use App\Policies\DocumentPolicy;
use App\Services\Documents\AuditService;
use App\Services\Documents\DocumentSettingService;
use App\Services\Documents\DocumentStorageService;
use App\Services\Documents\FolderPermissionService;
use App\Services\Documents\FolderService;
use App\Services\Documents\QuotaService;
use App\Services\Documents\WorkflowService;
use App\Support\ExternalDocumentMetadata;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller {
    protected DocumentStorageService $storageService;
    protected FolderPermissionService $folderPermissionService;
    protected FolderService $folderService;
    protected WorkflowService $workflowService;
    protected QuotaService $quotaService;
    protected AuditService $auditService;
    protected DocumentSettingService $settingService;

    public function __construct(DocumentStorageService $storageService, FolderPermissionService $folderPermissionService, FolderService $folderService, WorkflowService $workflowService, QuotaService $quotaService, AuditService $auditService, DocumentSettingService $settingService) {
        $this->storageService = $storageService;
        $this->folderPermissionService = $folderPermissionService;
        $this->folderService = $folderService;
        $this->workflowService = $workflowService;
        $this->quotaService = $quotaService;
        $this->auditService = $auditService;
        $this->settingService = $settingService;
    }

    /**
     * Display a paginated listing of documents with folder context.
     *
     * Accepts optional ?folder= parameter to scope documents to a specific folder.
     * Loads subfolders, folder tree for sidebar, and breadcrumbs for navigation.
     */
    public function index(Request $request): View {
        $this->authorize('viewAny', Document::class);

        $sortableColumns = ['title', 'created_at', 'size_bytes', 'extension'];
        $sortBy = in_array($request->input('sort'), $sortableColumns)
            ? $request->input('sort')
            : 'created_at';
        $sortDir = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        // Resolve current folder context
        $folderId = $request->input('folder');
        $currentFolder = $folderId ? DocumentFolder::findOrFail($folderId) : null;
        if ($currentFolder) {
            $this->authorize('view', $currentFolder);
        }

        // Build document query scoped to current folder
        $query = Document::with(['owner:id,firstname,lastname', 'category:id,name', 'tags:id,name'])
            ->select([
                'id', 'ulid', 'title', 'source_type', 'original_name', 'mime_type', 'extension',
                'size_bytes', 'status', 'owner_id', 'category_id', 'folder_id', 'legal_hold', 'created_at',
            ])
            ->visibleTo($request->user());

        // WFL-11: Allow including archived documents when requested
        if (!$request->boolean('include_archived')) {
            $query->notArchived();
        }

        // Filter: show only favorites (SRC-07)
        if ($request->boolean('only_favorites')) {
            $query->whereIn('id', function ($sub) use ($request) {
                $sub->select('document_id')
                    ->from('document_favorites')
                    ->where('user_id', $request->user()->id);
            });
        }

        // Scope to current folder (or show unfiled documents at root)
        if ($currentFolder) {
            $query->where('folder_id', $currentFolder->id);
        } else {
            $query->whereNull('folder_id');
        }

        $documents = $query->orderBy($sortBy, $sortDir)
            ->paginate(30)
            ->appends($request->only(['sort', 'direction', 'folder', 'only_favorites', 'include_archived']));

        // Load subfolders for current location
        $subfoldersQuery = DocumentFolder::where('parent_id', $folderId);
        if (!DocumentFolderPolicy::isAdmin($request->user())) {
            $subfoldersQuery->where(function ($visibilityQuery) use ($request) {
                $visibilityQuery->where('owner_id', $request->user()->id)
                    ->orWhereIn('visibility', [
                        DocumentFolder::VISIBILITY_INTERNAL,
                        DocumentFolder::VISIBILITY_PUBLIC,
                    ]);
            });
        }
        $subfolders = $subfoldersQuery
            ->select([
                'id',
                'name',
                'owner_id',
                'repository_type',
                'visibility',
                'document_count',
                'depth',
                'created_at',
            ])
            ->orderBy('name')
            ->get();

        // Build full folder tree for sidebar navigation
        $folderTree = $this->folderService->buildFolderTree($request->user());

        // Build breadcrumbs for current folder location
        $breadcrumbs = $currentFolder
            ? $this->folderService->getAncestorsWithSelf($currentFolder)
            : collect();

        $canUploadToCurrentFolder = $currentFolder === null
            || $this->canUploadToFolder($request->user(), $currentFolder);

        // Load user's favorited documents for sidebar
        $favorites = DocumentFavorite::where('user_id', $request->user()->id)
            ->with(['document' => function ($query) use ($request) {
                $query->select('id', 'ulid', 'title', 'extension')
                    ->visibleTo($request->user());
            }])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->pluck('document')
            ->filter();

        // Load recently viewed documents from audit trail
        $recentDocIds = DocumentAudit::where('user_id', $request->user()->id)
            ->where('action', DocumentAudit::ACTION_VIEWED)
            ->select('document_id', DB::raw('MAX(created_at) as last_viewed'))
            ->groupBy('document_id')
            ->orderByDesc('last_viewed')
            ->limit(10)
            ->pluck('document_id');

        $recentDocuments = $recentDocIds->isNotEmpty()
            ? Document::whereIn('id', $recentDocIds)
                ->select('id', 'ulid', 'title', 'extension')
                ->visibleTo($request->user())
                ->get()
                ->sortBy(fn($doc) => array_search($doc->id, $recentDocIds->toArray()))
            : collect();

        // Load favorite document IDs for star icon state on document rows
        $favoriteDocIds = DocumentFavorite::where('user_id', $request->user()->id)
            ->pluck('document_id')
            ->toArray();

        // Load shared-with-me documents for sidebar
        $sharedWithMe = $this->getSharedWithMeSidebar($request->user());

        // Load user quota data for sidebar widget and warning banner
        $userQuota = $this->quotaService->getOrCreateQuota($request->user());
        $usedFormatted = $this->quotaService->formatBytes($userQuota->used_bytes);
        $totalFormatted = $this->quotaService->formatBytes($userQuota->quota_bytes);

        return view('documents.index', compact(
            'documents', 'sortBy', 'sortDir',
            'currentFolder', 'subfolders', 'folderTree', 'breadcrumbs',
            'canUploadToCurrentFolder',
            'favorites', 'recentDocuments', 'favoriteDocIds', 'sharedWithMe',
            'userQuota', 'usedFormatted', 'totalFormatted'
        ));
    }

    /**
     * Show the form for creating a new document (upload).
     */
    public function create(Request $request): View {
        $this->authorize('create', Document::class);

        $currentFolder = null;
        $uploadRedirectUrl = route('documents.index');

        if ($request->filled('folder')) {
            $currentFolder = DocumentFolder::findOrFail((int) $request->input('folder'));

            abort_unless(
                $this->canUploadToFolder($request->user(), $currentFolder),
                403,
                'You do not have permission to upload into this folder.'
            );

            $uploadRedirectUrl = route('documents.index', ['folder' => $currentFolder->id]);
        }

        $categories = DocumentCategory::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $tags = DocumentTag::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Load user quota for remaining storage display
        $userQuota = $this->quotaService->getOrCreateQuota(auth()->user());
        $usedFormatted = $this->quotaService->formatBytes($userQuota->used_bytes);
        $totalFormatted = $this->quotaService->formatBytes($userQuota->quota_bytes);
        $remainingBytes = max(0, $userQuota->quota_bytes - $userQuota->used_bytes);
        $remainingFormatted = $this->quotaService->formatBytes($remainingBytes);

        $uploadMaxSizeMb = (int) $this->settingService->get('storage.max_file_size_mb', 50);
        $allowedExtensions = array_values(array_filter(array_map(
            static fn($extension) => strtolower(trim((string) $extension)),
            (array) $this->settingService->get('allowed_extensions', config('documents.allowed_extensions', []))
        )));
        $acceptedFilesCsv = !empty($allowedExtensions)
            ? implode(',', array_map(static fn($extension) => '.' . $extension, $allowedExtensions))
            : null;

        return view('documents.create', compact(
            'currentFolder',
            'categories',
            'tags',
            'userQuota',
            'usedFormatted',
            'totalFormatted',
            'remainingFormatted',
            'uploadMaxSizeMb',
            'allowedExtensions',
            'acceptedFilesCsv',
            'uploadRedirectUrl'
        ));
    }

    /**
     * Store a newly uploaded document with version and audit records.
     */
    public function store(StoreDocumentRequest $request): JsonResponse {
        $validated = $request->validated();
        $user = $request->user();
        $sourceType = $validated['source_type'] ?? Document::SOURCE_UPLOAD;
        $targetFolder = $this->resolveUploadFolder($validated['folder_id'] ?? null);

        if (($validated['folder_id'] ?? null) !== null && $targetFolder === null) {
            return response()->json([
                'success' => false,
                'message' => 'The selected folder could not be found.',
            ], 422);
        }

        if ($targetFolder !== null && !$this->canUploadToFolder($user, $targetFolder)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to upload into the selected folder.',
            ], 403);
        }

        if ($sourceType === Document::SOURCE_EXTERNAL_URL) {
            return $this->storeExternalDocument($request, $validated, $user, $targetFolder);
        }

        $file = $request->file('file');

        // Check quota BEFORE storing file
        $quotaCheck = $this->quotaService->canUpload($user, $file->getSize());
        if (!$quotaCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $quotaCheck['reason'],
            ], 422);
        }

        // Store file first (outside transaction so filesystem is committed)
        $fileData = $this->storageService->store($file, $user->id);
        $storagePath = $fileData['storage_path'];
        $requestedTitle = trim((string) ($validated['title'] ?? ''));
        $preserveOriginalName = $request->boolean('preserve_original_name');
        $resolvedTitle = $preserveOriginalName
            ? ''
            : $requestedTitle;
        if ($resolvedTitle === '') {
            $resolvedTitle = trim((string) pathinfo($fileData['original_name'], PATHINFO_FILENAME));
        }
        if ($resolvedTitle === '') {
            $resolvedTitle = 'Untitled Document';
        }

        try {
            $newTags = $request->input('new_tags', []);
            $document = DB::transaction(function () use ($validated, $fileData, $user, $newTags, $resolvedTitle, $targetFolder) {
                $document = Document::create([
                    'title' => $resolvedTitle,
                    'description' => $validated['description'] ?? null,
                    'storage_disk' => $this->storageService->disk(),
                    'storage_path' => $fileData['storage_path'],
                    'original_name' => $fileData['original_name'],
                    'mime_type' => $fileData['mime_type'],
                    'extension' => $fileData['extension'],
                    'size_bytes' => $fileData['size_bytes'],
                    'checksum_sha256' => $fileData['checksum_sha256'],
                    'folder_id' => $targetFolder?->id,
                    'category_id' => $validated['category_id'] ?? null,
                    'owner_id' => $user->id,
                    'status' => Document::STATUS_DRAFT,
                    'visibility' => Document::VISIBILITY_PRIVATE,
                    'current_version' => config('documents.versioning.initial_version', '1.0'),
                    'version_count' => 1,
                    'expiry_date' => $validated['expiry_date'] ?? null,
                ]);

                // Create initial version record
                DocumentVersion::create([
                    'document_id' => $document->id,
                    'version_number' => config('documents.versioning.initial_version', '1.0'),
                    'version_type' => DocumentVersion::TYPE_MAJOR,
                    'storage_disk' => $this->storageService->disk(),
                    'storage_path' => $fileData['storage_path'],
                    'original_name' => $fileData['original_name'],
                    'mime_type' => $fileData['mime_type'],
                    'size_bytes' => $fileData['size_bytes'],
                    'checksum_sha256' => $fileData['checksum_sha256'],
                    'uploaded_by_user_id' => $user->id,
                    'is_current' => true,
                ]);

                if ($targetFolder !== null) {
                    $this->folderService->incrementDocumentCount($targetFolder->id);
                }

                // Attach tags if provided (handle both existing IDs and new:tagname from Select2)
                $rawTagIds = $validated['tag_ids'] ?? [];
                // Prefix new_tags with 'new:' for resolveTagIds
                foreach ($newTags as $newTagName) {
                    $rawTagIds[] = 'new:' . $newTagName;
                }

                $this->syncDocumentTags($document, $rawTagIds, $user->id);

                // Create audit record
                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $user->id,
                    'action' => DocumentAudit::ACTION_CREATED,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'session_id' => session()->getId(),
                    'metadata' => [
                        'title' => $document->title,
                        'original_name' => $fileData['original_name'],
                        'size_bytes' => $fileData['size_bytes'],
                        'mime_type' => $fileData['mime_type'],
                        'folder_id' => $document->folder_id,
                    ],
                ]);

                return $document;
            });

            // Increment quota usage after successful upload
            $this->quotaService->incrementUsage($user, $document->size_bytes);
            $this->quotaService->checkAndSendWarning($user);

            $responseData = [
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'document' => [
                    'id' => $document->id,
                    'ulid' => $document->ulid,
                    'title' => $document->title,
                ],
            ];

            // Add warning if over 100% but under 110% (soft warning)
            if ($quotaCheck['reason'] === 'over_quota_warning') {
                $responseData['warning'] = 'You are over your storage quota. Please free up space to avoid being blocked from uploading.';
            }

            return response()->json($responseData);
        } catch (\Exception $e) {
            // Clean up orphaned file on DB failure
            $this->storageService->delete($storagePath);

            Log::error('DocumentController: Failed to store document record', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Display a single document with all relationships.
     */
    public function show(Request $request, Document $document): View {
        $this->authorize('view', $document);

        $document->load([
            'owner:id,firstname,lastname',
            'category:id,name',
            'tags:id,name,slug',
            'versions' => fn($q) => $q->orderBy('created_at', 'desc'),
            'versions.uploadedBy:id,firstname,lastname',
            'approvals.reviewer:id,firstname,lastname,email',
            'legalHoldBy:id,firstname,lastname',
        ]);

        // Create audit record
        DocumentAudit::create([
            'document_id' => $document->id,
            'user_id' => $request->user()->id,
            'action' => DocumentAudit::ACTION_VIEWED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
        ]);

        // Increment view count
        $document->increment('view_count');

        // Check if current user has favorited this document
        $isFavorited = DocumentFavorite::where('user_id', $request->user()->id)
            ->where('document_id', $document->id)
            ->exists();

        // Load pending approvals and reviewer status for workflow panel
        $pendingApprovals = $document->approvals
            ->whereIn('status', ['pending', 'in_review']);

        $isReviewer = $document->approvals
            ->where('reviewer_id', $request->user()->id)
            ->whereIn('status', ['pending', 'in_review'])
            ->isNotEmpty();

        // Load available roles for publish modal
        $availableRoles = \App\Models\Role::all();

        // Load per-document audit history for activity tab
        $documentAudits = DocumentAudit::where('document_id', $document->id)
            ->with('user:id,firstname,lastname')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $auditService = $this->auditService;

        return view('documents.show', compact('document', 'isFavorited', 'pendingApprovals', 'isReviewer', 'availableRoles', 'documentAudits', 'auditService'));
    }

    /**
     * Show the form for editing a document's metadata.
     */
    public function edit(Document $document): View {
        $this->authorize('update', $document);

        $categories = DocumentCategory::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $tags = DocumentTag::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('documents.edit', compact('document', 'categories', 'tags'));
    }

    /**
     * Update a document's metadata.
     */
    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse {
        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($validated, $document, $user) {
            // Track changes for audit
            $changes = [];
            $trackedFields = ['title', 'description', 'category_id', 'expiry_date'];
            if ($document->isExternalUrl()) {
                $trackedFields[] = 'external_url';
            }

            foreach ($trackedFields as $field) {
                $newValue = $validated[$field] ?? null;
                $oldValue = $document->{$field};
                if ($oldValue instanceof \Carbon\Carbon) {
                    $oldValue = $oldValue->toDateString();
                }
                if ($oldValue !== $newValue) {
                    $changes[$field] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }

            // Determine expiry-related updates
            $expiryDate = $validated['expiry_date'] ?? null;
            $expiryUpdates = ['expiry_date' => $expiryDate];
            // Reset warning flag when expiry is cleared or changed
            if ($expiryDate !== ($document->expiry_date ? $document->expiry_date->toDateString() : null)) {
                $expiryUpdates['expiry_warning_sent_at'] = null;
            }

            // Update document metadata
            $updatePayload = array_merge([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
            ], $expiryUpdates);

            if ($document->isExternalUrl()) {
                $updatePayload['external_url'] = $validated['external_url'] ?? $document->external_url;
            }

            $document->update($updatePayload);

            // Sync tags with pivot data (handle new:tagname from Select2)
            if (array_key_exists('tag_ids', $validated)) {
                $oldTagIds = $document->tags->pluck('id')->toArray();
                $rawTagIds = $validated['tag_ids'] ?? [];
                $resolvedTagIds = $this->resolveTagIds($rawTagIds, $user->id);

                $tagSync = [];
                foreach ($resolvedTagIds as $tagId) {
                    $tagSync[$tagId] = ['tagged_by_user_id' => $user->id, 'created_at' => now()];
                }
                $document->tags()->sync($tagSync);

                // Recalculate usage counts for all affected tags (old + new)
                $affectedTagIds = array_unique(array_merge($oldTagIds, $resolvedTagIds));
                foreach ($affectedTagIds as $affectedId) {
                    DocumentTag::where('id', $affectedId)->update([
                        'usage_count' => DB::table('document_tag')->where('tag_id', $affectedId)->count(),
                    ]);
                }
            }

            // Create audit record
            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'action' => DocumentAudit::ACTION_UPDATED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
                'metadata' => ['changes' => $changes],
            ]);
        });

        // WFL-10: Reset published documents to draft on edit when approval is required
        $this->workflowService->handlePostEditStatusReset($document);

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Soft-delete a document.
     */
    public function destroy(Document $document, Request $request): RedirectResponse {
        $this->authorize('delete', $document);

        // Legal hold enforcement
        if ($document->legal_hold) {
            $document->load('legalHoldBy:id,firstname,lastname');
            $holdInfo = 'This document is under legal hold and cannot be deleted.';
            if ($document->legalHoldBy) {
                $holdInfo .= '<br>Placed by <strong>' . e($document->legalHoldBy->full_name) . '</strong>';
            }
            if ($document->legal_hold_at) {
                $holdInfo .= ' on ' . $document->legal_hold_at->format('d M Y');
            }
            if ($document->legal_hold_reason) {
                $holdInfo .= '.<br>Reason: ' . e($document->legal_hold_reason);
            }
            return redirect()->back()->with('error', $holdInfo);
        }

        // Create audit record before soft delete
        DocumentAudit::create([
            'document_id' => $document->id,
            'user_id' => $request->user()->id,
            'action' => DocumentAudit::ACTION_TRASHED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'metadata' => [
                'title' => $document->title,
            ],
        ]);

        $document->delete();

        // Recalculate owner quota to avoid drift from mixed delete/restore/version operations
        $owner = User::find($document->owner_id);
        if ($owner) {
            $this->quotaService->recalculate($owner);
        }

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document moved to trash.');
    }

    /**
     * Stream a document for inline preview.
     */
    public function preview(Document $document, Request $request): StreamedResponse|RedirectResponse {
        $this->authorize('view', $document);

        // Create audit record
        DocumentAudit::create([
            'document_id' => $document->id,
            'user_id' => $request->user()->id,
            'action' => DocumentAudit::ACTION_PREVIEWED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
        ]);

        if ($document->isExternalUrl()) {
            return $this->redirectToExternalDocument($document);
        }

        $stream = $this->storageService->download($document->storage_path);

        if ($stream === null) {
            abort(404, 'Document file not found.');
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="' . $document->original_name . '"',
        ]);
    }

    /**
     * Stream a document for download as attachment.
     */
    public function download(Document $document, Request $request): StreamedResponse|RedirectResponse {
        $this->authorize('view', $document);

        // Create audit record
        DocumentAudit::create([
            'document_id' => $document->id,
            'user_id' => $request->user()->id,
            'action' => DocumentAudit::ACTION_DOWNLOADED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
        ]);

        // Increment download count
        $document->increment('download_count');

        if ($document->isExternalUrl()) {
            return $this->redirectToExternalDocument($document);
        }

        $stream = $this->storageService->download($document->storage_path);

        if ($stream === null) {
            abort(404, 'Document file not found.');
        }

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $document->original_name . '"',
        ]);
    }

    /**
     * Display trashed (soft-deleted) documents.
     */
    public function trash(Request $request): View {
        $this->authorize('viewAny', Document::class);

        $query = Document::onlyTrashed()
            ->with(['owner:id,firstname,lastname'])
            ->select(['id', 'ulid', 'title', 'original_name', 'size_bytes', 'deleted_at', 'owner_id']);

        // Non-admins can only see their own trashed documents
        if (!DocumentPolicy::isAdmin($request->user())) {
            $query->where('owner_id', $request->user()->id);
        }

        $documents = $query->orderBy('deleted_at', 'desc')
            ->paginate(30);

        return view('documents.trash', compact('documents'));
    }

    /**
     * Restore a soft-deleted document.
     */
    public function restore(int $id, Request $request): RedirectResponse {
        $document = Document::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $document);

        $document->restore();

        $owner = User::find($document->owner_id);
        if ($owner) {
            $this->quotaService->recalculate($owner);
        }

        // Create audit record
        DocumentAudit::create([
            'document_id' => $document->id,
            'user_id' => $request->user()->id,
            'action' => DocumentAudit::ACTION_RESTORED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'metadata' => [
                'title' => $document->title,
            ],
        ]);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document restored successfully.');
    }

    /**
     * Bulk soft-delete multiple documents.
     */
    public function bulkDelete(Request $request): JsonResponse {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:documents,id'],
        ]);

        $deleted = 0;
        $unauthorized = 0;
        $legalHoldSkipped = 0;
        $affectedOwnerIds = [];

        DB::transaction(function () use ($validated, $request, &$deleted, &$unauthorized, &$legalHoldSkipped, &$affectedOwnerIds) {
            $documents = Document::whereIn('id', $validated['ids'])->get();

            foreach ($documents as $document) {
                // Skip documents under legal hold
                if ($document->legal_hold) {
                    $legalHoldSkipped++;
                    continue;
                }

                if ($request->user()->can('delete', $document)) {
                    DocumentAudit::create([
                        'document_id' => $document->id,
                        'user_id' => $request->user()->id,
                        'action' => DocumentAudit::ACTION_TRASHED,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'session_id' => session()->getId(),
                        'metadata' => [
                            'title' => $document->title,
                            'bulk_action' => true,
                        ],
                    ]);

                    $document->delete();
                    $deleted++;
                    $affectedOwnerIds[] = $document->owner_id;
                } else {
                    $unauthorized++;
                }
            }
        });

        if (!empty($affectedOwnerIds)) {
            User::whereIn('id', array_values(array_unique($affectedOwnerIds)))
                ->get()
                ->each(fn (User $owner) => $this->quotaService->recalculate($owner));
        }

        $message = "{$deleted} document(s) moved to trash.";
        if ($unauthorized > 0) {
            $message .= " {$unauthorized} document(s) skipped (unauthorized).";
        }
        if ($legalHoldSkipped > 0) {
            $message .= " {$legalHoldSkipped} document(s) skipped (legal hold).";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'deleted' => $deleted,
            'unauthorized' => $unauthorized,
            'legal_hold_skipped' => $legalHoldSkipped,
        ]);
    }

    /**
     * Bulk download multiple documents as a ZIP archive.
     */
    public function bulkDownload(Request $request): StreamedResponse|JsonResponse {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:20'],
            'ids.*' => ['integer', 'exists:documents,id'],
        ]);

        if (!class_exists(\ZipArchive::class)) {
            return response()->json([
                'success' => false,
                'message' => 'ZIP functionality is not available. Please download files individually.',
            ], 501);
        }

        $documents = Document::whereIn('id', $validated['ids'])->get();

        $authorizedDocs = $documents->filter(function ($document) use ($request) {
            return $request->user()->can('view', $document);
        });

        if ($authorizedDocs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No authorized documents found for download.',
            ], 403);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'dms_bulk_');
        $zip = new \ZipArchive();

        if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ZIP archive.',
            ], 500);
        }

        $addedCount = 0;
        $skippedExternal = [];
        foreach ($authorizedDocs as $document) {
            if ($document->isExternalUrl()) {
                $skippedExternal[] = [
                    'title' => $document->title,
                    'url' => $document->external_url,
                ];
                continue;
            }

            if ($this->storageService->exists($document->storage_path)) {
                $fullPath = $this->storageService->fullPath($document->storage_path);
                $zip->addFile($fullPath, $document->original_name);
                $addedCount++;

                // Audit each download
                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $request->user()->id,
                    'action' => DocumentAudit::ACTION_DOWNLOADED,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => session()->getId(),
                    'metadata' => ['bulk_download' => true],
                ]);
            }
        }

        if (!empty($skippedExternal)) {
            $lines = [
                'The following documents are hosted externally and were not included in this ZIP:',
                '',
            ];

            foreach ($skippedExternal as $item) {
                $lines[] = '- ' . $item['title'] . ' -> ' . $item['url'];
            }

            $zip->addFromString('REMOTE-DOCUMENTS.txt', implode(PHP_EOL, $lines) . PHP_EOL);
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($tempPath);
            return response()->json([
                'success' => false,
                'message' => !empty($skippedExternal)
                    ? count($skippedExternal) . ' selected document(s) are remote URLs and cannot be bundled into a ZIP. Open them individually from the document page.'
                    : 'No files could be found for download.',
            ], !empty($skippedExternal) ? 422 : 404);
        }

        // Register shutdown function to clean up temp file
        $cleanupPath = $tempPath;
        register_shutdown_function(function () use ($cleanupPath) {
            @unlink($cleanupPath);
        });

        return response()->streamDownload(function () use ($tempPath) {
            readfile($tempPath);
        }, 'documents-' . now()->format('Y-m-d-His') . '.zip', [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Get the 10 most recent documents shared with the given user for the sidebar.
     *
     * Queries shares targeting the user directly, by role, or by department.
     */
    private function getSharedWithMeSidebar(\App\Models\User $user) {
        $roleIdentifiers = array_values(array_unique(array_map('strval', array_merge(
            $user->roles()->pluck('roles.id')->toArray(),
            $user->roles()->pluck('roles.name')->toArray()
        ))));

        return DocumentShare::where('is_active', true)
            ->whereNull('revoked_at')
            ->where(function ($q) use ($user, $roleIdentifiers) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('shareable_type', DocumentShare::TYPE_USER)
                       ->where('shareable_id', (string) $user->id);
                });
                if (!empty($roleIdentifiers)) {
                    $q->orWhere(function ($q2) use ($roleIdentifiers) {
                        $q2->where('shareable_type', DocumentShare::TYPE_ROLE)
                           ->whereIn('shareable_id', $roleIdentifiers);
                    });
                }
                if ($user->department) {
                    $q->orWhere(function ($q2) use ($user) {
                        $q2->where('shareable_type', DocumentShare::TYPE_DEPARTMENT)
                           ->where('shareable_id', (string) $user->department);
                    });
                }
            })
            ->with('document:id,ulid,title,mime_type,extension')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Toggle publish/unpublish status on a document.
     *
     * Published documents get STATUS_PUBLISHED and published_at timestamp.
     * Unpublished documents revert to STATUS_DRAFT with cleared published_at.
     */
    public function publish(Request $request, Document $document): JsonResponse {
        $this->authorize('publish', $document);

        // When approval workflow is enabled, redirect to workflow publish endpoint
        if ($this->settingService->get('approval.require_approval', true)) {
            return response()->json([
                'success' => false,
                'message' => 'Use the workflow publish endpoint when approval is required.',
            ], 422);
        }

        DB::transaction(function () use ($request, $document) {
            if ($document->status === Document::STATUS_PUBLISHED) {
                // Unpublish
                $document->update([
                    'status' => Document::STATUS_DRAFT,
                    'published_at' => null,
                    'is_featured' => false, // Cannot be featured if unpublished
                ]);

                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $request->user()->id,
                    'action' => DocumentAudit::ACTION_PUBLISHED,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata' => ['action' => 'unpublished'],
                ]);
            } else {
                // Publish
                $document->update([
                    'status' => Document::STATUS_PUBLISHED,
                    'published_at' => now(),
                ]);

                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $request->user()->id,
                    'action' => DocumentAudit::ACTION_PUBLISHED,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata' => ['action' => 'published'],
                ]);
            }
        });

        $document->refresh();

        return response()->json([
            'success' => true,
            'status' => $document->status,
            'published_at' => $document->published_at?->toIso8601String(),
            'is_featured' => $document->is_featured,
        ]);
    }

    /**
     * Toggle featured status on a published document.
     *
     * Enforces maximum of 10 featured documents across the system.
     */
    public function toggleFeatured(Request $request, Document $document): JsonResponse {
        $this->authorize('publish', $document);

        // Document must be published to be featured
        if ($document->status !== Document::STATUS_PUBLISHED) {
            return response()->json([
                'success' => false,
                'message' => 'Only published documents can be featured.',
            ], 422);
        }

        $result = DB::transaction(function () use ($document) {
            $document = Document::lockForUpdate()->find($document->id);

            if (!$document->is_featured) {
                $featuredCount = Document::where('is_featured', true)->lockForUpdate()->count();
                if ($featuredCount >= 10) {
                    return ['success' => false, 'message' => 'Maximum 10 featured documents. Please unfeature another document first.'];
                }
                $document->update(['is_featured' => true]);
            } else {
                $document->update(['is_featured' => false]);
            }

            return ['success' => true, 'is_featured' => $document->is_featured];
        });

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Toggle legal hold status on a document.
     *
     * Only administrators may place or remove legal holds.
     * Creates audit record and notifies document owner via email and in-app notification.
     */
    public function toggleLegalHold(Request $request, Document $document): JsonResponse {
        if (!$request->user()->hasAnyRoles(['Administrator'])) {
            abort(403, 'Only administrators can manage legal holds.');
        }

        $validated = $request->validate([
            'legal_hold' => ['required'],
            'reason' => ['required_if:legal_hold,true,1', 'nullable', 'string', 'max:500'],
        ]);

        $placingHold = filter_var($validated['legal_hold'], FILTER_VALIDATE_BOOLEAN);

        DB::transaction(function () use ($document, $request, $placingHold, $validated) {
            if ($placingHold) {
                $document->update([
                    'legal_hold' => true,
                    'legal_hold_reason' => $validated['reason'] ?? null,
                    'legal_hold_by_user_id' => $request->user()->id,
                    'legal_hold_at' => now(),
                ]);

                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $request->user()->id,
                    'action' => DocumentAudit::ACTION_LEGAL_HOLD_PLACED,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => session()->getId(),
                    'metadata' => [
                        'reason' => $validated['reason'] ?? null,
                    ],
                ]);
            } else {
                $document->update([
                    'legal_hold' => false,
                    'legal_hold_reason' => null,
                    'legal_hold_by_user_id' => null,
                    'legal_hold_at' => null,
                ]);

                DocumentAudit::create([
                    'document_id' => $document->id,
                    'user_id' => $request->user()->id,
                    'action' => DocumentAudit::ACTION_LEGAL_HOLD_REMOVED,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => session()->getId(),
                ]);
            }
        });

        // Notify document owner OUTSIDE transaction
        try {
            $owner = $document->owner;
            if ($owner && $owner->id !== $request->user()->id) {
                if ($placingHold) {
                    DocumentNotification::create([
                        'user_id' => $owner->id,
                        'type' => 'legal_hold_placed',
                        'title' => 'Legal Hold Placed',
                        'message' => 'A legal hold has been placed on "' . $document->title . '" by ' . $request->user()->full_name . '.',
                        'data' => ['document_id' => $document->id, 'reason' => $validated['reason'] ?? null],
                        'url' => route('documents.show', $document),
                    ]);
                    Mail::to($owner->email)->queue(new LegalHoldPlacedMail($document, $request->user()));
                } else {
                    DocumentNotification::create([
                        'user_id' => $owner->id,
                        'type' => 'legal_hold_removed',
                        'title' => 'Legal Hold Removed',
                        'message' => 'The legal hold on "' . $document->title . '" has been removed by ' . $request->user()->full_name . '.',
                        'data' => ['document_id' => $document->id],
                        'url' => route('documents.show', $document),
                    ]);
                    Mail::to($owner->email)->queue(new LegalHoldRemovedMail($document, $request->user()));
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send legal hold notification', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'legal_hold' => $document->legal_hold,
            'message' => $placingHold ? 'Legal hold placed.' : 'Legal hold removed.',
        ]);
    }

    /**
     * Store a document that is backed by an external URL instead of an upload.
     *
     * URL-backed documents still participate in metadata, sharing, and audit
     * flows, but they do not consume local quota or create a version row.
     */
    private function storeExternalDocument(Request $request, array $validated, User $user, ?DocumentFolder $targetFolder): JsonResponse
    {
        $metadata = ExternalDocumentMetadata::fromUrl($validated['external_url'], $validated['title']);
        $newTags = $request->input('new_tags', []);

        $document = DB::transaction(function () use ($validated, $user, $targetFolder, $metadata, $newTags) {
            $document = Document::create([
                'title' => trim((string) $validated['title']),
                'description' => $validated['description'] ?? null,
                'source_type' => Document::SOURCE_EXTERNAL_URL,
                'external_url' => trim((string) $validated['external_url']),
                'storage_disk' => null,
                'storage_path' => null,
                'original_name' => $metadata['original_name'],
                'mime_type' => $metadata['mime_type'],
                'extension' => $metadata['extension'],
                'size_bytes' => null,
                'checksum_sha256' => null,
                'folder_id' => $targetFolder?->id,
                'category_id' => $validated['category_id'] ?? null,
                'owner_id' => $user->id,
                'status' => Document::STATUS_DRAFT,
                'visibility' => Document::VISIBILITY_PRIVATE,
                'current_version' => config('documents.versioning.initial_version', '1.0'),
                'version_count' => 1,
                'expiry_date' => $validated['expiry_date'] ?? null,
            ]);

            if ($targetFolder !== null) {
                $this->folderService->incrementDocumentCount($targetFolder->id);
            }

            $rawTagIds = $validated['tag_ids'] ?? [];
            foreach ($newTags as $newTagName) {
                $rawTagIds[] = 'new:' . $newTagName;
            }
            $this->syncDocumentTags($document, $rawTagIds, $user->id);

            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'action' => DocumentAudit::ACTION_CREATED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
                'metadata' => [
                    'title' => $document->title,
                    'source_type' => $document->source_type,
                    'external_url' => $document->external_url,
                    'folder_id' => $document->folder_id,
                ],
            ]);

            return $document;
        });

        return response()->json([
            'success' => true,
            'message' => 'Document link saved successfully.',
            'document' => [
                'id' => $document->id,
                'ulid' => $document->ulid,
                'title' => $document->title,
            ],
        ]);
    }

    /**
     * Sync tags onto a document and refresh tag usage counters.
     *
     * @param array<int|string> $rawTagIds
     */
    private function syncDocumentTags(Document $document, array $rawTagIds, int $userId): void
    {
        if (empty($rawTagIds)) {
            return;
        }

        $resolvedTagIds = $this->resolveTagIds($rawTagIds, $userId);
        $tagSync = [];
        foreach ($resolvedTagIds as $tagId) {
            $tagSync[$tagId] = ['tagged_by_user_id' => $userId, 'created_at' => now()];
        }
        $document->tags()->sync($tagSync);

        foreach ($resolvedTagIds as $affectedId) {
            DocumentTag::where('id', $affectedId)->update([
                'usage_count' => DB::table('document_tag')->where('tag_id', $affectedId)->count(),
            ]);
        }
    }

    /**
     * Redirect a URL-backed document to its remote source.
     */
    private function redirectToExternalDocument(Document $document): RedirectResponse
    {
        if (blank($document->external_url)) {
            abort(404, 'Document URL not found.');
        }

        return redirect()->away($document->external_url);
    }

    /**
     * Resolve the folder targeted by an upload request.
     */
    private function resolveUploadFolder(?int $folderId): ?DocumentFolder {
        if ($folderId === null) {
            return null;
        }

        return DocumentFolder::find($folderId);
    }

    /**
     * Determine whether the given user may upload into the specified folder.
     */
    private function canUploadToFolder(User $user, DocumentFolder $folder): bool {
        return $this->folderPermissionService->canUploadToFolder($folder, $user);
    }

    /**
     * Resolve tag values from Select2 input, creating new tags as needed.
     *
     * Handles both existing tag IDs (integers) and new:tagname prefixed values.
     * New tags are created as non-official with the specified user as creator.
     *
     * @param array<int|string> $tagValues
     * @param int $userId
     * @return array<int>
     */
    private function resolveTagIds(array $tagValues, int $userId): array {
        $tagIds = [];
        foreach ($tagValues as $tagValue) {
            if (str_starts_with((string) $tagValue, 'new:')) {
                $tagName = substr($tagValue, 4);
                $tag = DocumentTag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    [
                        'name' => $tagName,
                        'slug' => Str::slug($tagName),
                        'is_official' => false,
                        'created_by_user_id' => $userId,
                        'usage_count' => 0,
                    ]
                );
                $tagIds[] = $tag->id;
            } else {
                $tagIds[] = (int) $tagValue;
            }
        }
        return $tagIds;
    }
}
