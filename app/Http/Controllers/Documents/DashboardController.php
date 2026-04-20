<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentAudit;
use App\Models\DocumentShare;
use App\Policies\DocumentPolicy;
use App\Services\Documents\QuotaService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {
    private QuotaService $quotaService;

    public function __construct(QuotaService $quotaService) {
        $this->quotaService = $quotaService;
    }

    /**
     * Display the document management dashboard with widgets for
     * storage quota, recent documents, pending approvals, shared docs, and admin stats.
     */
    public function index(): View {
        $this->authorize('viewAny', Document::class);

        $user = auth()->user();

        // DSH-01: Storage Quota data
        $userQuota = $this->quotaService->getOrCreateQuota($user);
        $usedFormatted = $this->quotaService->formatBytes($userQuota->used_bytes);
        $totalFormatted = $this->quotaService->formatBytes($userQuota->quota_bytes);

        // DSH-02: Recent Documents (last 5 viewed by this user)
        $recentDocIds = DocumentAudit::where('user_id', $user->id)
            ->where('action', DocumentAudit::ACTION_VIEWED)
            ->select('document_id', DB::raw('MAX(created_at) as last_viewed'))
            ->groupBy('document_id')
            ->orderByDesc('last_viewed')
            ->limit(5)
            ->pluck('document_id');

        $recentDocuments = $recentDocIds->isNotEmpty()
            ? Document::with('owner:id,firstname,lastname')
                ->whereIn('id', $recentDocIds)
                ->select('id', 'ulid', 'title', 'extension', 'updated_at', 'owner_id')
                ->visibleTo($user)
                ->get()
                ->sortBy(fn($doc) => array_search($doc->id, $recentDocIds->toArray()))
            : collect();

        // DSH-03: Pending Approvals (for current user as reviewer)
        $pendingApprovals = DocumentApproval::with(['document:id,ulid,title', 'submittedBy:id,firstname,lastname'])
            ->where('reviewer_id', $user->id)
            ->whereIn('status', [DocumentApproval::STATUS_PENDING, DocumentApproval::STATUS_IN_REVIEW])
            ->select('id', 'document_id', 'submitted_by_user_id', 'status', 'due_date', 'submitted_at')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        // DSH-04: Shared With Me (most recent 5)
        $sharedDocuments = DocumentShare::with(['document:id,ulid,title,extension', 'sharedBy:id,firstname,lastname'])
            ->where('shareable_type', 'user')
            ->where('shareable_id', $user->id)
            ->whereNull('revoked_at')
            ->select('id', 'document_id', 'shared_by_user_id', 'permission_level', 'created_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // DSH-05: Admin Statistics (admin-only)
        $isAdmin = DocumentPolicy::isAdmin($user);
        $stats = null;

        if ($isAdmin) {
            $statusCounts = Document::select('status', DB::raw('COUNT(*) as count'))
                ->whereNull('deleted_at')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $stats = [
                'total_documents' => array_sum($statusCounts),
                'total_storage' => Document::whereNull('deleted_at')->sum('size_bytes'),
                'active_users' => \App\Models\User::where('status', 'Current')->count(),
                'status_counts' => $statusCounts,
            ];
        }

        return view('documents.dashboard', compact(
            'userQuota',
            'usedFormatted',
            'totalFormatted',
            'recentDocuments',
            'pendingApprovals',
            'sharedDocuments',
            'isAdmin',
            'stats'
        ));
    }
}
