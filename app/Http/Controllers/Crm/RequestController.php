<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CrmRequestUpsertRequest;
use App\Http\Requests\Crm\RequestActivityStoreRequest;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\RequestAttachment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RequestController extends CrmController
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('crm.requests.sales.index');
    }

    public function salesIndex(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'owner_id' => (string) $request->query('owner_id', ''),
            'lead_id' => (string) $request->query('lead_id', ''),
            'sales_stage_id' => (string) $request->query('sales_stage_id', ''),
            'outcome' => (string) $request->query('outcome', ''),
        ];

        $salesStatsQuery = $this->scopeOwned(CrmRequest::query()->where('type', 'sales'));
        $salesStats = [
            ['label' => 'Total', 'value' => (clone $salesStatsQuery)->count()],
            ['label' => 'Pending', 'value' => (clone $salesStatsQuery)->where('outcome', 'pending')->count()],
            ['label' => 'Won', 'value' => (clone $salesStatsQuery)->where('outcome', 'won')->count()],
            ['label' => 'Lost', 'value' => (clone $salesStatsQuery)->where('outcome', 'lost')->count()],
        ];

        $requests = $this->requestIndexBaseQuery()
            ->where('type', 'sales')
            ->when($filters['lead_id'] !== '', function ($query) use ($filters) {
                $query->where('lead_id', (int) $filters['lead_id']);
            })
            ->when($filters['sales_stage_id'] !== '', function ($query) use ($filters) {
                $query->where('sales_stage_id', (int) $filters['sales_stage_id']);
            })
            ->when($filters['outcome'] !== '', function ($query) use ($filters) {
                $query->where('outcome', $filters['outcome']);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('crm.requests.sales-index', [
            'requests' => $requests,
            'owners' => $this->owners(),
            'leads' => $this->requestLeadsForFilter(),
            'salesStages' => $this->availableSalesStages(),
            'requestOutcomes' => config('heritage_crm.request_outcomes'),
            'filters' => $filters,
            'salesStats' => $salesStats,
        ]);
    }

    public function supportIndex(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'owner_id' => (string) $request->query('owner_id', ''),
            'customer_id' => (string) $request->query('customer_id', ''),
            'support_status' => (string) $request->query('support_status', ''),
        ];

        $supportStatsQuery = $this->scopeOwned(CrmRequest::query()->where('type', 'support'));
        $supportStats = [
            ['label' => 'Total', 'value' => (clone $supportStatsQuery)->count()],
            ['label' => 'Open', 'value' => (clone $supportStatsQuery)->where('support_status', 'open')->count()],
            ['label' => 'In Progress', 'value' => (clone $supportStatsQuery)->where('support_status', 'in_progress')->count()],
            ['label' => 'Resolved', 'value' => (clone $supportStatsQuery)->where('support_status', 'resolved')->count()],
            ['label' => 'Closed', 'value' => (clone $supportStatsQuery)->where('support_status', 'closed')->count()],
        ];

        $requests = $this->requestIndexBaseQuery()
            ->where('type', 'support')
            ->when($filters['customer_id'] !== '', function ($query) use ($filters) {
                $query->where('customer_id', (int) $filters['customer_id']);
            })
            ->when($filters['support_status'] !== '', function ($query) use ($filters) {
                $query->where('support_status', $filters['support_status']);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('crm.requests.support-index', [
            'requests' => $requests,
            'owners' => $this->owners(),
            'customers' => $this->requestCustomersForFilter(),
            'supportStatuses' => config('heritage_crm.support_statuses'),
            'filters' => $filters,
            'supportStats' => $supportStats,
        ]);
    }

    public function create(): View
    {
        return view('crm.requests.create');
    }

    public function createSales(): View
    {
        return view('crm.requests.create-sales', $this->salesFormData());
    }

    public function createSupport(): View
    {
        return view('crm.requests.create-support', $this->supportFormData());
    }

    public function store(CrmRequestUpsertRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $crmRequest = CrmRequest::query()->create($this->payload($request, $data));
        $this->storeAttachments($request, $crmRequest);

        return redirect()
            ->route('crm.requests.show', $crmRequest)
            ->with('crm_success', 'Request created successfully.');
    }

    public function storeSales(CrmRequestUpsertRequest $request): RedirectResponse
    {
        return $this->store($request);
    }

    public function storeSupport(CrmRequestUpsertRequest $request): RedirectResponse
    {
        return $this->store($request);
    }

    public function show(CrmRequest $crmRequest): View
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);

        $crmRequest->load([
            'owner',
            'lead:id,company_name',
            'customer:id,company_name',
            'contact:id,name,lead_id,customer_id',
            'salesStage:id,name',
            'activities.user',
            'attachments.uploadedBy:id,name',
            'quotes' => fn ($query) => $query
                ->with(['contact:id,name'])
                ->withCount('items')
                ->latest('quote_date')
                ->latest('id'),
            'invoices' => fn ($query) => $query
                ->with(['contact:id,name'])
                ->withCount('items')
                ->latest('invoice_date')
                ->latest('id'),
        ]);

        return view('crm.requests.show', [
            'crmRequest' => $crmRequest,
            'requestTypes' => config('heritage_crm.request_types'),
            'supportStatuses' => config('heritage_crm.support_statuses'),
            'activityTypes' => config('heritage_crm.activity_types'),
            'quoteStatuses' => config('heritage_crm.quote_statuses'),
            'invoiceStatuses' => config('heritage_crm.invoice_statuses'),
        ]);
    }

    public function edit(CrmRequest $crmRequest): View
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);
        $crmRequest->load('attachments.uploadedBy:id,name');

        if ($crmRequest->type === 'support') {
            return view('crm.requests.edit-support', array_merge($this->supportFormData(), [
                'crmRequest' => $crmRequest,
            ]));
        }

        return view('crm.requests.edit-sales', array_merge($this->salesFormData(), [
            'crmRequest' => $crmRequest,
        ]));
    }

    public function update(CrmRequestUpsertRequest $request, CrmRequest $crmRequest): RedirectResponse
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);

        $crmRequest->update($this->payload($request, $request->validated(), $crmRequest));
        $this->storeAttachments($request, $crmRequest);

        return redirect()
            ->route('crm.requests.edit', $crmRequest)
            ->with('crm_success', 'Request updated successfully.');
    }

    public function destroy(CrmRequest $crmRequest): RedirectResponse
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);
        $crmRequest->load('attachments');

        foreach ($crmRequest->attachments as $attachment) {
            $this->deleteAttachmentFile($attachment);
        }

        $crmRequest->forceDelete();

        return redirect()
            ->route($crmRequest->type === 'support' ? 'crm.requests.support.index' : 'crm.requests.sales.index')
            ->with('crm_success', 'Request deleted permanently.');
    }

    public function openAttachment(CrmRequest $crmRequest, RequestAttachment $requestAttachment): BinaryFileResponse
    {
        $this->authorizeAttachmentAccess($crmRequest, $requestAttachment);

        return response()->file(
            $this->attachmentAbsolutePath($requestAttachment),
            [
                'Content-Type' => $requestAttachment->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . addslashes($requestAttachment->original_name) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function downloadAttachment(CrmRequest $crmRequest, RequestAttachment $requestAttachment): BinaryFileResponse
    {
        $this->authorizeAttachmentAccess($crmRequest, $requestAttachment);

        return response()->download(
            $this->attachmentAbsolutePath($requestAttachment),
            $requestAttachment->original_name,
            [
                'Content-Type' => $requestAttachment->mime_type ?: 'application/octet-stream',
            ]
        );
    }

    public function destroyAttachment(CrmRequest $crmRequest, RequestAttachment $requestAttachment): RedirectResponse
    {
        $this->authorizeAttachmentAccess($crmRequest, $requestAttachment);
        $this->deleteAttachmentFile($requestAttachment);
        $requestAttachment->delete();

        return back()->with('crm_success', 'Attachment deleted permanently.');
    }

    public function storeActivity(RequestActivityStoreRequest $request, CrmRequest $crmRequest): RedirectResponse
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);

        $activity = $crmRequest->activities()->create([
            'user_id' => $this->crmUser()->id,
            ...$request->validated(),
        ]);

        $crmRequest->forceFill([
            'last_contact_at' => $activity->occurred_at,
        ])->save();

        return redirect()
            ->route('crm.requests.show', $crmRequest)
            ->with('crm_success', 'Activity logged successfully.');
    }

    private function payload(
        CrmRequestUpsertRequest $request,
        array $data,
        ?CrmRequest $existingRequest = null
    ): array {
        $lead = $this->resolveLead($data['lead_id'] ?? null);
        $customer = $this->resolveCustomer($data['customer_id'] ?? null);
        $contact = $this->resolveContact($data['contact_id'] ?? null);

        $this->authorizeLinkedRecords($lead, $customer, $contact, $existingRequest);

        if ($contact !== null) {
            $leadMismatch = $lead !== null && (int) $contact->lead_id !== (int) $lead->id;
            $customerMismatch = $customer !== null && (int) $contact->customer_id !== (int) $customer->id;

            if ($leadMismatch || $customerMismatch) {
                throw ValidationException::withMessages([
                    'contact_id' => 'The selected contact must belong to the selected lead or customer.',
                ]);
            }
        }

        $data['owner_id'] = $this->syncedOwnerId($lead, $customer, $data['owner_id'] ?? $existingRequest?->owner_id);

        if ($data['type'] === 'sales') {
            $data['support_status'] = null;
            $data['outcome'] = $data['outcome'] ?? 'pending';
        } else {
            $data['lead_id'] = null;
            $data['sales_stage_id'] = null;
            $data['outcome'] = null;
        }

        return $this->normalizeTimestamps($request, $data);
    }

    private function normalizeTimestamps(CrmRequestUpsertRequest $request, array $data): array
    {
        $data['next_action_at'] = $request->filled('next_action_at') ? $request->date('next_action_at') : null;
        $data['last_contact_at'] = $request->filled('last_contact_at') ? $request->date('last_contact_at') : null;
        $data['closed_at'] = $request->filled('closed_at') ? $request->date('closed_at') : null;

        return $data;
    }

    private function requestIndexBaseQuery(): Builder
    {
        return $this->scopeOwned(
            CrmRequest::query()
                ->with([
                    'owner',
                    'lead:id,company_name',
                    'customer:id,company_name',
                    'contact:id,name',
                    'salesStage:id,name',
                ])
                ->when(($q = trim((string) request()->query('q', ''))) !== '', function ($query) use ($q) {
                    $query->where(function ($requestQuery) use ($q) {
                        $requestQuery->where('title', 'like', '%' . $q . '%')
                            ->orWhere('description', 'like', '%' . $q . '%')
                            ->orWhere('next_action', 'like', '%' . $q . '%');
                    });
                })
                ->when(($ownerId = (string) request()->query('owner_id', '')) !== '', function ($query) use ($ownerId) {
                    $query->where('owner_id', (int) $ownerId);
                })
        );
    }

    private function requestLeadsForFilter()
    {
        $query = Lead::query()
            ->select(['id', 'company_name', 'owner_id'])
            ->orderBy('company_name');

        return $this->scopeOwned($query)->get();
    }

    private function requestCustomersForFilter()
    {
        $query = Customer::query()
            ->select(['id', 'company_name', 'owner_id'])
            ->orderBy('company_name');

        return $this->scopeOwned($query)->get();
    }

    private function salesFormData(): array
    {
        return [
            'owners' => $this->owners(),
            'leads' => $this->leadsForSelect(),
            'contacts' => $this->contactsForSelect()
                ->filter(fn ($contact) => $contact->lead_id !== null)
                ->values(),
            'salesStages' => $this->availableSalesStages(),
            'requestOutcomes' => config('heritage_crm.request_outcomes'),
        ];
    }

    private function supportFormData(): array
    {
        return [
            'owners' => $this->owners(),
            'customers' => $this->customersForSelect(),
            'contacts' => $this->contactsForSelect()
                ->filter(fn ($contact) => $contact->customer_id !== null)
                ->values(),
            'supportStatuses' => config('heritage_crm.support_statuses'),
        ];
    }

    private function storeAttachments(CrmRequestUpsertRequest $request, CrmRequest $crmRequest): void
    {
        $files = $request->file('attachments', []);

        if (! is_array($files) || $files === []) {
            return;
        }

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('crm/request-attachments/' . $crmRequest->id, 'documents');

            $crmRequest->attachments()->create([
                'uploaded_by_id' => $this->crmUser()->id,
                'disk' => 'documents',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'extension' => strtolower((string) $file->getClientOriginalExtension()),
                'size' => (int) $file->getSize(),
            ]);
        }
    }

    private function authorizeAttachmentAccess(CrmRequest $crmRequest, RequestAttachment $requestAttachment): void
    {
        abort_unless((int) $requestAttachment->request_id === (int) $crmRequest->id, 404);

        $this->authorizeRecordAccess($crmRequest->owner_id);
    }

    private function attachmentAbsolutePath(RequestAttachment $requestAttachment): string
    {
        abort_unless(Storage::disk($requestAttachment->disk)->exists($requestAttachment->path), 404);

        return Storage::disk($requestAttachment->disk)->path($requestAttachment->path);
    }

    private function deleteAttachmentFile(RequestAttachment $requestAttachment): void
    {
        Storage::disk($requestAttachment->disk)->delete($requestAttachment->path);
    }
}
