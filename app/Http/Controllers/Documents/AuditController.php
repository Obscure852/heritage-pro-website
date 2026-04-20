<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Documents\AuditService;
use App\Exports\AuditLogExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AuditController extends Controller {
    private AuditService $auditService;

    public function __construct(AuditService $auditService) {
        $this->auditService = $auditService;
    }

    /**
     * Display the paginated, filterable audit log listing.
     */
    public function index(Request $request): View {
        Gate::authorize('view-document-audit');

        $filters = $request->only([
            'date_from',
            'date_to',
            'user_id',
            'document_id',
            'category',
            'document_search',
        ]);

        $audits = $this->auditService
            ->buildQuery($filters)
            ->paginate(50)
            ->appends($request->query());

        $users = User::select('id', 'firstname', 'lastname')
            ->where('status', 'Current')
            ->orderBy('firstname')
            ->get();

        $categories = AuditService::CATEGORY_LABELS;

        return view('documents.audit-logs', compact(
            'audits',
            'filters',
            'users',
            'categories',
        ))->with('auditService', $this->auditService);
    }

    /**
     * Export the current filtered audit log view to Excel.
     */
    public function export(Request $request): BinaryFileResponse {
        Gate::authorize('view-document-audit');

        $filters = $request->only([
            'date_from',
            'date_to',
            'user_id',
            'document_id',
            'category',
            'document_search',
        ]);

        $query = $this->auditService->buildQuery($filters);

        return Excel::download(
            new AuditLogExport($query),
            'audit-logs-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
