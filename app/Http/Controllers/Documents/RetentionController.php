<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreRetentionPolicyRequest;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentRetentionPolicy;
use App\Policies\DocumentPolicy;
use App\Services\Documents\RetentionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RetentionController extends Controller {
    public function __construct(
        protected RetentionService $retentionService,
    ) {}

    /**
     * List all retention policies.
     */
    public function index(): View {
        abort_unless(DocumentPolicy::isAdmin(auth()->user()), 403);

        $policies = DocumentRetentionPolicy::with('createdBy:id,firstname,lastname')
            ->orderBy('name')
            ->get();

        return view('documents.retention-policies.index', compact('policies'));
    }

    /**
     * Show create retention policy form.
     */
    public function create(): View {
        abort_unless(DocumentPolicy::isAdmin(auth()->user()), 403);

        $categories = DocumentCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('documents.retention-policies.form', [
            'policy' => null,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new retention policy.
     */
    public function store(StoreRetentionPolicyRequest $request): RedirectResponse {
        abort_unless(DocumentPolicy::isAdmin(auth()->user()), 403);

        $data = $request->validated();
        $data['created_by_user_id'] = auth()->id();
        $data['conditions'] = ['category_id' => $data['category_id'] ?? null];
        unset($data['category_id']);

        DocumentRetentionPolicy::create($data);

        return redirect()->route('documents.settings', ['tab' => 'retention'])
            ->with('success', 'Retention policy created successfully.');
    }

    /**
     * Show edit retention policy form.
     */
    public function edit(DocumentRetentionPolicy $policy): View {
        abort_unless(DocumentPolicy::isAdmin(auth()->user()), 403);

        $categories = DocumentCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('documents.retention-policies.form', compact('policy', 'categories'));
    }

    /**
     * Update an existing retention policy.
     */
    public function update(StoreRetentionPolicyRequest $request, DocumentRetentionPolicy $policy): RedirectResponse {
        abort_unless(DocumentPolicy::isAdmin(auth()->user()), 403);

        $data = $request->validated();
        $data['conditions'] = ['category_id' => $data['category_id'] ?? null];
        unset($data['category_id']);

        $policy->update($data);

        return redirect()->route('documents.settings', ['tab' => 'retention'])
            ->with('success', 'Retention policy updated successfully.');
    }

    /**
     * Delete a retention policy.
     */
    public function destroy(DocumentRetentionPolicy $policy): RedirectResponse {
        abort_unless(DocumentPolicy::isAdmin(auth()->user()), 403);

        $policy->delete();

        return redirect()->route('documents.settings', ['tab' => 'retention'])
            ->with('success', 'Retention policy deleted successfully.');
    }

    /**
     * Show documents expiring within 30 days.
     */
    public function expiringSoon(Request $request): View {
        abort_unless(DocumentPolicy::isAdmin(auth()->user()), 403);

        $documents = Document::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->where('status', '!=', Document::STATUS_ARCHIVED)
            ->with('owner:id,firstname,lastname', 'category:id,name')
            ->orderBy('expiry_date')
            ->paginate(50);

        return view('documents.expiring-soon', compact('documents'));
    }

    /**
     * Renew/extend a document's expiry date.
     */
    public function renewExpiry(Request $request, Document $document): RedirectResponse {
        $user = auth()->user();
        abort_unless(
            $document->owner_id === $user->id || DocumentPolicy::isAdmin($user),
            403
        );

        $request->validate([
            'new_expiry_date' => 'required|date|after:today',
        ]);

        $this->retentionService->renewExpiry($document, $request->new_expiry_date);

        return redirect()->back()->with('success', 'Document expiry date has been renewed.');
    }
}
