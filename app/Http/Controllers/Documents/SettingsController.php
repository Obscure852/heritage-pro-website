<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\DocumentRetentionPolicy;
use App\Models\DocumentTag;
use App\Models\User;
use App\Models\UserDocumentQuota;
use App\Policies\DocumentPolicy;
use App\Services\Documents\AuditService;
use App\Services\Documents\DocumentSettingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller {
    protected DocumentSettingService $settingService;

    public function __construct(DocumentSettingService $settingService) {
        $this->settingService = $settingService;
    }

    /**
     * Display the consolidated DMS admin settings page with tabs.
     */
    public function index(Request $request): View {
        abort_unless(
            Gate::any(['manage-document-categories', 'view-document-audit', 'manage-document-quotas', 'manage-document-settings']),
            403
        );

        $activeTab = $request->input('tab', '');
        $data = ['activeTab' => $activeTab];

        // ── Categories & Tags tab ──
        if (Gate::allows('manage-document-categories')) {
            $data['categories'] = DocumentCategory::with('parent:id,name')
                ->withCount('documents', 'children')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            $data['parentCategories'] = DocumentCategory::whereNull('parent_id')
                ->where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            $data['tags'] = DocumentTag::with('createdBy:id,firstname,lastname')
                ->orderBy('name')
                ->get();
        }

        // ── Retention Policies tab ──
        if (DocumentPolicy::isAdmin(auth()->user())) {
            $data['policies'] = DocumentRetentionPolicy::with('createdBy:id,firstname,lastname')
                ->orderBy('name')
                ->get();
        }

        // ── Storage Quotas tab ──
        if (Gate::allows('manage-document-quotas')) {
            $quotaQuery = User::select('id', 'firstname', 'lastname', 'email', 'department', 'status')
                ->where('status', 'Current')
                ->with('documentQuota');

            if ($activeTab === 'quotas') {
                if ($search = $request->input('search')) {
                    $quotaQuery->where(function ($q) use ($search) {
                        $q->where('firstname', 'like', '%' . $search . '%')
                          ->orWhere('lastname', 'like', '%' . $search . '%');
                    });
                }

                $sortableColumns = ['firstname', 'used_bytes', 'quota_bytes'];
                $sortBy = in_array($request->input('sort'), $sortableColumns)
                    ? $request->input('sort')
                    : 'firstname';
                $sortDir = $request->input('direction') === 'desc' ? 'desc' : 'asc';

                if (in_array($sortBy, ['used_bytes', 'quota_bytes'])) {
                    $quotaQuery->orderBy(
                        UserDocumentQuota::select($sortBy)
                            ->whereColumn('user_document_quotas.user_id', 'users.id')
                            ->limit(1),
                        $sortDir
                    );
                } else {
                    $quotaQuery->orderBy($sortBy, $sortDir);
                }
            } else {
                $sortBy = 'firstname';
                $sortDir = 'asc';
                $quotaQuery->orderBy('firstname');
            }

            $data['quotaUsers'] = $quotaQuery->paginate(50)->appends(array_merge(
                $request->only(['sort', 'direction', 'search']),
                ['tab' => 'quotas']
            ));
            $data['sortBy'] = $sortBy;
            $data['sortDir'] = $sortDir;
        }

        // ── Audit Logs tab ──
        if (Gate::allows('view-document-audit')) {
            $auditService = app(AuditService::class);

            $filters = $activeTab === 'audit'
                ? $request->only(['date_from', 'date_to', 'user_id', 'document_id', 'category', 'document_search'])
                : [];

            $data['audits'] = $auditService
                ->buildQuery($filters)
                ->paginate(50)
                ->appends(array_merge(
                    $request->only(['date_from', 'date_to', 'user_id', 'category', 'document_search']),
                    ['tab' => 'audit']
                ));

            $data['auditFilters'] = $filters;
            $data['auditUsers'] = User::select('id', 'firstname', 'lastname')
                ->where('status', 'Current')
                ->orderBy('firstname')
                ->get();
            $data['auditCategories'] = AuditService::CATEGORY_LABELS;
            $data['auditService'] = $auditService;
        }

        // ── General Settings tab ──
        if (Gate::allows('manage-document-settings')) {
            $settingsQuotas = $this->settingService->getSection('quotas');
            $settingsQuotas['default_mb'] = round($settingsQuotas['default_bytes'] / (1024 * 1024));
            $settingsQuotas['admin_mb'] = round($settingsQuotas['admin_bytes'] / (1024 * 1024));

            $data['settingsQuotas'] = $settingsQuotas;
            $data['settingsRetention'] = $this->settingService->getSection('retention');
            $data['uploads'] = $this->settingService->getSection('uploads');
            $data['approval'] = $this->settingService->getSection('approval');
            $data['allExtensions'] = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'csv', 'zip', 'gif', 'bmp', 'svg'];
        }

        return view('documents.admin-settings', $data);
    }

    /**
     * Update a settings section via AJAX.
     */
    public function updateSection(Request $request, string $section): JsonResponse {
        Gate::authorize('manage-document-settings');

        $validSections = ['quotas', 'retention', 'uploads', 'approval'];
        if (!in_array($section, $validSections, true)) {
            return response()->json(['message' => 'Invalid settings section.'], 422);
        }

        $rules = $this->getValidationRules($section);
        $validated = $request->validate($rules);

        DB::transaction(function () use ($section, $validated) {
            switch ($section) {
                case 'quotas':
                    $this->settingService->set('quotas.default_bytes', $validated['default_mb'] * 1024 * 1024);
                    $this->settingService->set('quotas.admin_bytes', $validated['admin_mb'] * 1024 * 1024);
                    $this->settingService->set('quotas.warning_threshold_percent', $validated['warning_threshold_percent']);
                    break;

                case 'retention':
                    $this->settingService->set('retention.default_days', $validated['default_days']);
                    $this->settingService->set('retention.grace_period_days', $validated['grace_period_days']);
                    $this->settingService->set('retention.trash_retention_days', $validated['trash_retention_days']);
                    break;

                case 'uploads':
                    $this->settingService->set('storage.max_file_size_mb', $validated['max_file_size_mb']);
                    $this->settingService->set('allowed_extensions', $validated['allowed_extensions']);
                    \App\Models\DocumentSetting::where('key', 'allowed_extensions')
                        ->update(['group' => 'uploads']);
                    break;

                case 'approval':
                    $this->settingService->set('approval.require_approval', $validated['require_approval']);
                    $this->settingService->set('approval.review_deadline_days', $validated['review_deadline_days']);
                    break;
            }
        });

        return response()->json([
            'success' => true,
            'message' => ucfirst($section) . ' settings saved successfully.',
        ]);
    }

    /**
     * Get validation rules for a settings section.
     */
    private function getValidationRules(string $section): array {
        return match ($section) {
            'quotas' => [
                'default_mb' => ['required', 'integer', 'min:50', 'max:50000'],
                'admin_mb' => ['required', 'integer', 'min:100', 'max:100000'],
                'warning_threshold_percent' => ['required', 'integer', 'min:50', 'max:99'],
            ],
            'retention' => [
                'default_days' => ['required', 'integer', 'min:30', 'max:36500'],
                'grace_period_days' => ['required', 'integer', 'min:1', 'max:365'],
                'trash_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            ],
            'uploads' => [
                'max_file_size_mb' => ['required', 'integer', 'min:1', 'max:500'],
                'allowed_extensions' => ['required', 'array', 'min:1'],
                'allowed_extensions.*' => ['string', 'in:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,csv,zip,gif,bmp,svg'],
            ],
            'approval' => [
                'require_approval' => ['required', 'boolean'],
                'review_deadline_days' => ['required', 'integer', 'min:1', 'max:90'],
            ],
            default => [],
        };
    }
}
