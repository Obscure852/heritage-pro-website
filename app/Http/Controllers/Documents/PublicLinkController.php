<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StorePublicLinkRequest;
use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\DocumentCategory;
use App\Models\DocumentShare;
use App\Services\Documents\DocumentStorageService;
use App\Services\Documents\PublicLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicLinkController extends Controller {
    protected PublicLinkService $publicLinkService;
    protected DocumentStorageService $storageService;

    public function __construct(PublicLinkService $publicLinkService, DocumentStorageService $storageService) {
        $this->publicLinkService = $publicLinkService;
        $this->storageService = $storageService;
    }

    /**
     * List all active public links for a document (authenticated).
     */
    public function index(Document $document): JsonResponse {
        $this->authorize('share', $document);

        $links = $this->publicLinkService->getActiveLinks($document);

        $formatted = $links->map(function (DocumentShare $link) {
            return [
                'id' => $link->id,
                'access_token' => $link->access_token,
                'url' => route('documents.public.view', ['token' => $link->access_token]),
                'expires_at' => $link->expires_at?->format('Y-m-d H:i'),
                'allow_download' => $link->allow_download,
                'max_views' => $link->max_views,
                'view_count' => $link->view_count,
                'has_password' => !is_null($link->password_hash),
                'is_active' => $link->is_active,
                'created_at' => $link->created_at->format('Y-m-d H:i'),
            ];
        });

        return response()->json(['links' => $formatted]);
    }

    /**
     * Create a new public link for a document (authenticated).
     */
    public function store(StorePublicLinkRequest $request, Document $document): JsonResponse {
        $this->authorize('share', $document);

        try {
            $link = $this->publicLinkService->generateLink(
                $document,
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Public link created successfully.',
                'link' => [
                    'id' => $link->id,
                    'access_token' => $link->access_token,
                    'url' => route('documents.public.view', ['token' => $link->access_token]),
                    'expires_at' => $link->expires_at?->format('Y-m-d H:i'),
                    'allow_download' => $link->allow_download,
                    'max_views' => $link->max_views,
                    'view_count' => $link->view_count,
                    'has_password' => !is_null($link->password_hash),
                    'is_active' => $link->is_active,
                    'created_at' => $link->created_at->format('Y-m-d H:i'),
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Disable a public link (can be re-enabled later).
     */
    public function disable(Document $document, DocumentShare $share): JsonResponse {
        $this->authorize('share', $document);

        // Validate share belongs to this document and is a public link
        if ($share->document_id !== $document->id || $share->shareable_type !== DocumentShare::TYPE_PUBLIC_LINK) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid public link.',
            ], 404);
        }

        $this->publicLinkService->disableLink($share, request()->user());

        return response()->json([
            'success' => true,
            'message' => 'Public link disabled.',
        ]);
    }

    /**
     * Permanently revoke and delete a public link.
     */
    public function destroy(Document $document, DocumentShare $share): JsonResponse {
        $this->authorize('share', $document);

        // Validate share belongs to this document and is a public link
        if ($share->document_id !== $document->id || $share->shareable_type !== DocumentShare::TYPE_PUBLIC_LINK) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid public link.',
            ], 404);
        }

        $this->publicLinkService->deleteLink($share, request()->user());

        return response()->json([
            'success' => true,
            'message' => 'Public link revoked.',
        ]);
    }

    /**
     * Public document view via access token (no authentication).
     */
    public function publicView(Request $request, string $token) {
        $this->ensurePublicAccessEnabled();

        $link = $this->publicLinkService->resolveLink($token);

        if (!$link) {
            return response()->view('documents.public.expired', [], 404);
        }

        // If password-protected and not yet verified in session
        if ($link->password_hash && !session("public_link_verified:{$link->id}")) {
            return redirect()->route('documents.public.password', ['token' => $token]);
        }

        // Increment view count and refresh to get the accurate count
        $this->publicLinkService->incrementViewCount($link);
        $link->refresh();

        // Log public access audit (no user_id for anonymous)
        DocumentAudit::create([
            'document_id' => $link->document_id,
            'user_id' => null,
            'action' => DocumentAudit::ACTION_PUBLIC_ACCESS,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'token_prefix' => substr($link->access_token, 0, 8),
                'view_count' => $link->view_count,
            ],
        ]);

        $document = $link->document()->with(['owner:id,firstname,lastname', 'category:id,name,color'])->first();
        if (!$document) {
            return response()->view('documents.public.expired', [], 404);
        }

        return view('documents.public.view', [
            'document' => $document,
            'link' => $link,
            'token' => $token,
            'allowDownload' => $link->allow_download,
        ]);
    }

    /**
     * Show the password entry page for a protected public link (no authentication).
     */
    public function passwordPage(Request $request, string $token) {
        $this->ensurePublicAccessEnabled();

        $link = $this->publicLinkService->resolveLink($token);

        if (!$link) {
            return response()->view('documents.public.expired', [], 404);
        }

        $document = $link->document()->select('id', 'title')->first();

        // Check if currently locked out
        $locked = $this->publicLinkService->checkLockout($request->ip(), $link);
        $lockoutExpiry = $locked ? now()->addMinutes(15)->timestamp : null;

        return view('documents.public.password', [
            'token' => $token,
            'document' => $document,
            'locked' => $locked,
            'lockoutExpiry' => $lockoutExpiry,
        ]);
    }

    /**
     * Verify password for a protected public link (no authentication).
     */
    public function verifyPassword(Request $request, string $token) {
        $this->ensurePublicAccessEnabled();

        $link = $this->publicLinkService->resolveLink($token);

        if (!$link) {
            return response()->view('documents.public.expired', [], 404);
        }

        $document = $link->document()->select('id', 'title')->first();

        // Check lockout
        if ($this->publicLinkService->checkLockout($request->ip(), $link)) {
            return view('documents.public.password', [
                'token' => $token,
                'document' => $document,
                'locked' => true,
                'lockoutExpiry' => now()->addMinutes(15)->timestamp,
                'error' => "Too many failed attempts. Please wait 15 minutes before trying again.",
            ]);
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Verify password
        if ($this->publicLinkService->verifyPassword($link, $request->input('password'))) {
            // Store verification in session
            session()->put("public_link_verified:{$link->id}", true);
            return redirect()->route('documents.public.view', ['token' => $token]);
        }

        // Record failed attempt
        $attempts = $this->publicLinkService->recordFailedAttempt($request->ip(), $link);
        $maxAttempts = config('documents.public.max_password_attempts', 3);

        if ($attempts >= $maxAttempts) {
            return view('documents.public.password', [
                'token' => $token,
                'document' => $document,
                'locked' => true,
                'lockoutExpiry' => now()->addMinutes(15)->timestamp,
                'error' => "Too many failed attempts. Please wait 15 minutes before trying again.",
            ]);
        }

        $remaining = $maxAttempts - $attempts;
        return view('documents.public.password', [
            'token' => $token,
            'document' => $document,
            'locked' => false,
            'lockoutExpiry' => null,
            'error' => "Incorrect password. {$remaining} attempt(s) remaining.",
        ]);
    }

    /**
     * Public portal landing page showing published & featured documents (no authentication).
     */
    public function portal(): View {
        $this->ensurePublicAccessEnabled();

        $featured = Document::published()
            ->where('visibility', Document::VISIBILITY_PUBLIC)
            ->where('is_featured', true)
            ->with(['category:id,name,color', 'owner:id,firstname,lastname'])
            ->latest('published_at')
            ->take(5)
            ->get();

        $categories = DocumentCategory::where('is_active', true)
            ->withCount(['documents' => fn($q) => $q->published()->where('visibility', Document::VISIBILITY_PUBLIC)])
            ->having('documents_count', '>', 0)
            ->orderBy('name')
            ->get();

        $recentDocuments = Document::published()
            ->where('visibility', Document::VISIBILITY_PUBLIC)
            ->with(['category:id,name,color', 'owner:id,firstname,lastname'])
            ->latest('published_at')
            ->take(12)
            ->get();

        return view('documents.public.portal', compact('featured', 'categories', 'recentDocuments'));
    }

    /**
     * Search published documents on the public portal (no authentication).
     *
     * Accepts ?q= for text search and ?category= for category filter.
     */
    public function portalSearch(Request $request): View {
        $this->ensurePublicAccessEnabled();

        $search = $request->input('q', '');
        $selectedCategory = $request->input('category');

        $query = Document::published()
            ->where('visibility', Document::VISIBILITY_PUBLIC)
            ->with(['category:id,name,color', 'owner:id,firstname,lastname']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($selectedCategory) {
            $query->where('category_id', $selectedCategory);
        }

        $documents = $query->latest('published_at')
            ->paginate(20)
            ->withQueryString();

        $categories = DocumentCategory::where('is_active', true)
            ->withCount(['documents' => fn($q) => $q->published()->where('visibility', Document::VISIBILITY_PUBLIC)])
            ->having('documents_count', '>', 0)
            ->orderBy('name')
            ->get();

        return view('documents.public.portal', compact('documents', 'categories', 'search', 'selectedCategory'));
    }

    /**
     * Stream a public document for inline preview (no authentication).
     *
     * Serves the file with Content-Disposition: inline for iframe/img embedding.
     * Does NOT increment view count or create audit log (already handled in publicView).
     */
    public function publicPreview(Request $request, string $token): StreamedResponse|RedirectResponse {
        $this->ensurePublicAccessEnabled();

        $link = $this->publicLinkService->resolveLink($token);

        if (!$link) {
            abort(404);
        }

        // If password-protected, verify session has verified flag
        if ($link->password_hash && !session("public_link_verified:{$link->id}")) {
            abort(403, 'Password verification required.');
        }

        $document = $link->document;
        if (!$document) {
            abort(404);
        }

        if ($document->isExternalUrl()) {
            if (blank($document->external_url)) {
                abort(404, 'Document URL not found.');
            }

            return redirect()->away($document->external_url);
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
     * Stream a public document download (no authentication).
     *
     * Verifies link is active, password verified in session, and download is allowed.
     */
    public function publicDownload(Request $request, string $token): StreamedResponse|RedirectResponse {
        $this->ensurePublicAccessEnabled();

        $link = $this->publicLinkService->resolveLink($token);

        if (!$link) {
            abort(404);
        }

        // If password-protected, verify session has verified flag
        if ($link->password_hash && !session("public_link_verified:{$link->id}")) {
            abort(403, 'Password verification required.');
        }

        // Check allow_download flag
        if (!$link->allow_download) {
            abort(403, 'Download is not permitted for this link.');
        }

        $document = $link->document;
        if (!$document) {
            abort(404);
        }

        // Log audit: ACTION_DOWNLOADED with public_link metadata
        DocumentAudit::create([
            'document_id' => $document->id,
            'user_id' => null,
            'action' => DocumentAudit::ACTION_DOWNLOADED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'public_link' => true,
                'token_prefix' => substr($link->access_token, 0, 8),
            ],
        ]);

        // Increment download count on the document
        $document->increment('download_count');

        if ($document->isExternalUrl()) {
            if (blank($document->external_url)) {
                abort(404, 'Document URL not found.');
            }

            return redirect()->away($document->external_url);
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
     * Block unauthenticated public endpoints when public access is disabled.
     */
    private function ensurePublicAccessEnabled(): void {
        abort_unless(config('documents.public.enabled', true), 404);
    }
}
