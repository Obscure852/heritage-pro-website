<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\UpdateQuotaRequest;
use App\Models\User;
use App\Services\Documents\QuotaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuotaController extends Controller {
    protected QuotaService $quotaService;

    public function __construct(QuotaService $quotaService) {
        $this->quotaService = $quotaService;
    }

    /**
     * Display the quota management page with user table.
     */
    public function index(Request $request): View {
        Gate::authorize('manage-document-quotas');

        $query = User::select('id', 'firstname', 'lastname', 'email', 'department', 'status')
            ->where('status', 'Current')
            ->with('documentQuota');

        // Search filter by user name
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', '%' . $search . '%')
                  ->orWhere('lastname', 'like', '%' . $search . '%');
            });
        }

        // Sorting
        $sortableColumns = ['firstname', 'used_bytes', 'quota_bytes'];
        $sortBy = in_array($request->input('sort'), $sortableColumns)
            ? $request->input('sort')
            : 'firstname';
        $sortDir = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, ['used_bytes', 'quota_bytes'])) {
            // Sort by quota fields via subquery
            $query->orderBy(
                \App\Models\UserDocumentQuota::select($sortBy)
                    ->whereColumn('user_document_quotas.user_id', 'users.id')
                    ->limit(1),
                $sortDir
            );
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $users = $query->paginate(50)->appends($request->only(['sort', 'direction', 'search']));

        return view('documents.quotas.index', compact('users', 'sortBy', 'sortDir'));
    }

    /**
     * Update a single user's quota.
     */
    public function update(UpdateQuotaRequest $request, User $user): RedirectResponse {
        Gate::authorize('manage-document-quotas');

        $quota = $this->quotaService->getOrCreateQuota($user);
        $validated = $request->validated();

        if (!empty($validated['is_unlimited'])) {
            $quota->update([
                'is_unlimited' => true,
            ]);
        } else {
            $quotaBytes = ((int) $validated['quota_mb']) * 1024 * 1024;
            $quota->update([
                'quota_bytes' => $quotaBytes,
                'is_unlimited' => false,
            ]);
        }

        return redirect()->back()->with('success', "Quota updated for {$user->full_name}.");
    }

    /**
     * Perform bulk quota operations on multiple users.
     */
    public function bulkUpdate(Request $request): RedirectResponse {
        Gate::authorize('manage-document-quotas');

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'action' => ['required', 'in:set_quota,set_unlimited,recalculate'],
            'quota_mb' => ['required_if:action,set_quota', 'nullable', 'integer', 'min:1'],
        ]);

        $count = 0;
        foreach ($validated['user_ids'] as $userId) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            $quota = $this->quotaService->getOrCreateQuota($user);

            switch ($validated['action']) {
                case 'set_quota':
                    $quotaBytes = ((int) $validated['quota_mb']) * 1024 * 1024;
                    $quota->update([
                        'quota_bytes' => $quotaBytes,
                        'is_unlimited' => false,
                    ]);
                    break;

                case 'set_unlimited':
                    $quota->update(['is_unlimited' => true]);
                    break;

                case 'recalculate':
                    $this->quotaService->recalculate($user);
                    break;
            }

            $count++;
        }

        return redirect()->back()->with('success', "Bulk action completed for {$count} user(s).");
    }

    /**
     * Recalculate a single user's quota usage from actual documents.
     */
    public function recalculate(User $user): JsonResponse {
        Gate::authorize('manage-document-quotas');

        $quota = $this->quotaService->recalculate($user);

        return response()->json([
            'success' => true,
            'used_bytes' => $quota->used_bytes,
            'used_formatted' => $this->quotaService->formatBytes($quota->used_bytes),
            'usage_percent' => $quota->usage_percent,
        ]);
    }
}
