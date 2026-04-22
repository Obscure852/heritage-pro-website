<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\DiscussionThreadStoreRequest;
use App\Http\Requests\Crm\QuoteUpsertRequest;
use App\Models\CrmCommercialCurrency;
use App\Models\CrmProduct;
use App\Models\CrmQuote;
use App\Models\CrmRequest;
use App\Services\Crm\CommercialDocumentCalculator;
use App\Services\Crm\CommercialDocumentPdfService;
use App\Services\Crm\CommercialDocumentShareService;
use App\Services\Crm\CommercialDocumentValidationService;
use App\Services\Crm\CommercialNumberingService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuoteController extends CrmController
{
    public function __construct(
        private readonly CommercialDocumentCalculator $calculator,
        private readonly CommercialDocumentValidationService $validationService,
        private readonly CommercialNumberingService $numberingService,
        private readonly CommercialDocumentPdfService $pdfService,
        private readonly CommercialDocumentShareService $shareService
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $baseQuery = $this->quoteIndexQuery();

        $quotes = (clone $baseQuery)
            ->with(['owner', 'lead', 'customer', 'contact'])
            ->withCount('items')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($quoteQuery) use ($filters) {
                    $quoteQuery->where('quote_number', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('subject', 'like', '%' . $filters['q'] . '%')
                        ->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('company_name', 'like', '%' . $filters['q'] . '%'))
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('company_name', 'like', '%' . $filters['q'] . '%'))
                        ->orWhereHas('contact', fn ($contactQuery) => $contactQuery->where('name', 'like', '%' . $filters['q'] . '%'));
                });
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest('quote_date')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('crm.products.quotes.index', [
            'quotes' => $quotes,
            'filters' => $filters,
            'quoteStatuses' => config('heritage_crm.quote_statuses', []),
            'draftCount' => (clone $baseQuery)->where('status', 'draft')->count(),
            'sentCount' => (clone $baseQuery)->where('status', 'sent')->count(),
            'acceptedCount' => (clone $baseQuery)->where('status', 'accepted')->count(),
            'canCreateQuotes' => true,
        ]);
    }

    public function create(Request $request): View
    {
        return view('crm.products.quotes.create', $this->formData(null, $request));
    }

    public function store(QuoteUpsertRequest $request): RedirectResponse
    {
        $quote = DB::transaction(function () use ($request) {
            return $this->persistQuote($request->validated());
        });

        return redirect()
            ->route('crm.products.quotes.show', $quote)
            ->with('crm_success', 'Quote created successfully.');
    }

    public function show(CrmQuote $crmQuote): View
    {
        $this->authorizeQuoteAccess($crmQuote);

        $crmQuote->load([
            'owner',
            'lead:id,company_name,owner_id',
            'customer:id,company_name,owner_id',
            'contact:id,name,email,phone',
            'request:id,title,type',
            'items.product:id,name,code',
        ]);

        return view('crm.products.quotes.show', [
            'quote' => $crmQuote,
            'quoteStatuses' => config('heritage_crm.quote_statuses', []),
            'discountTypes' => config('heritage_crm.commercial_discount_types', []),
            'canEditQuote' => $this->canEditQuote($crmQuote),
            'canShareQuote' => $this->canShareQuote($crmQuote),
            'availableTransitions' => $this->availableTransitions($crmQuote),
        ]);
    }

    public function edit(CrmQuote $crmQuote): View
    {
        $this->authorizeQuoteEditing($crmQuote);

        return view('crm.products.quotes.edit', $this->formData($crmQuote));
    }

    public function update(QuoteUpsertRequest $request, CrmQuote $crmQuote): RedirectResponse
    {
        $this->authorizeQuoteEditing($crmQuote);

        DB::transaction(function () use ($request, $crmQuote) {
            $this->persistQuote($request->validated(), $crmQuote);
        });

        return redirect()
            ->route('crm.products.quotes.edit', $crmQuote)
            ->with('crm_success', 'Quote updated successfully.');
    }

    public function transition(Request $request, CrmQuote $crmQuote): RedirectResponse
    {
        $this->authorizeQuoteAccess($crmQuote);

        $payload = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $targetStatus = (string) $payload['status'];
        $allowedTransitions = $this->availableTransitions($crmQuote);

        abort_unless(array_key_exists($targetStatus, $allowedTransitions), 422);

        $updates = [
            'status' => $targetStatus,
        ];

        if ($targetStatus === 'draft') {
            $updates['shared_at'] = null;
            $updates['accepted_at'] = null;
            $updates['rejected_at'] = null;
            $updates['expired_at'] = null;
            $updates['cancelled_at'] = null;
        }

        if ($targetStatus === 'sent') {
            $updates['shared_at'] = now();
        }

        if ($targetStatus === 'accepted') {
            $updates['accepted_at'] = now();
        }

        if ($targetStatus === 'rejected') {
            $updates['rejected_at'] = now();
        }

        if ($targetStatus === 'expired') {
            $updates['expired_at'] = now();
        }

        if ($targetStatus === 'cancelled') {
            $updates['cancelled_at'] = now();
        }

        $crmQuote->update($updates);

        return redirect()
            ->route('crm.products.quotes.show', $crmQuote)
            ->with('crm_success', 'Quote status updated to ' . strtolower($allowedTransitions[$targetStatus]) . '.');
    }

    public function openPdf(CrmQuote $crmQuote): BinaryFileResponse
    {
        $this->authorizeQuoteAccess($crmQuote);

        return $this->pdfService->openResponse(
            $this->pdfService->ensureQuoteArtifact($crmQuote, $this->crmUser())
        );
    }

    public function downloadPdf(CrmQuote $crmQuote): BinaryFileResponse
    {
        $this->authorizeQuoteAccess($crmQuote);

        return $this->pdfService->downloadResponse(
            $this->pdfService->ensureQuoteArtifact($crmQuote, $this->crmUser())
        );
    }

    public function shareCreate(CrmQuote $crmQuote): View
    {
        $this->authorizeQuoteAccess($crmQuote);
        abort_unless($this->canShareQuote($crmQuote), 403);

        return view('crm.products.quotes.share', [
            'quote' => $crmQuote,
            'discussionChannels' => config('heritage_crm.discussion_channels'),
            'users' => $this->owners(),
            'integrations' => $this->availableIntegrations(),
        ]);
    }

    public function shareStore(DiscussionThreadStoreRequest $request, CrmQuote $crmQuote): RedirectResponse
    {
        $this->authorizeQuoteAccess($crmQuote);
        abort_unless($this->canShareQuote($crmQuote), 403);

        $thread = $this->shareService->shareQuote(
            $crmQuote,
            $this->crmUser(),
            $request->validated()
        );

        return redirect()
            ->route('crm.discussions.show', $thread)
            ->with('crm_success', 'Quote shared successfully.');
    }

    private function persistQuote(array $payload, ?CrmQuote $existingQuote = null): CrmQuote
    {
        $settings = $this->commercialSettingsRecord();
        $currency = CrmCommercialCurrency::query()
            ->where('is_active', true)
            ->findOrFail($payload['currency_id']);

        $lead = $this->resolveLead($payload['lead_id'] ?? null);
        $customer = $this->resolveCustomer($payload['customer_id'] ?? null);
        $contact = $this->resolveContact($payload['contact_id'] ?? null);
        $crmRequest = $this->resolveCrmRequest($payload['request_id'] ?? null);

        $this->authorizeLinkedCommercialRecords($lead, $customer, $contact, $crmRequest);

        $quotePayload = [
            'owner_id' => $this->syncedOwnerId($lead, $customer, $existingQuote?->owner_id),
            'lead_id' => $lead?->id,
            'customer_id' => $customer?->id,
            'contact_id' => $contact?->id,
            'request_id' => $crmRequest?->id,
            'quote_number' => $existingQuote?->quote_number ?? $this->numberingService->nextQuoteNumber(),
            'status' => $existingQuote?->status ?? 'draft',
            'quote_date' => $payload['quote_date'],
            'valid_until' => $payload['valid_until'],
        ];

        $this->validationService->validateQuote($quotePayload);

        $linePayload = $this->normalizedLinePayload(
            $payload['items'],
            $settings->allow_line_discounts,
            (float) $settings->default_tax_rate,
            $existingQuote?->items()->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->values()->all() ?? []
        );

        $documentDiscountType = $settings->allow_document_discounts
            ? (string) $payload['document_discount_type']
            : 'none';
        $documentDiscountValue = $settings->allow_document_discounts
            ? (float) ($payload['document_discount_value'] ?? 0)
            : 0.0;

        $calculation = $this->calculator->calculate(
            array_map(function (array $line) {
                return [
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_type' => $line['discount_type'],
                    'discount_value' => $line['discount_value'],
                    'tax_rate' => $line['tax_rate'],
                ];
            }, $linePayload),
            $documentDiscountType,
            $documentDiscountValue,
            (int) $currency->precision
        );

        $quoteAttributes = [
            'owner_id' => $quotePayload['owner_id'],
            'lead_id' => $quotePayload['lead_id'],
            'customer_id' => $quotePayload['customer_id'],
            'contact_id' => $quotePayload['contact_id'],
            'request_id' => $quotePayload['request_id'],
            'quote_number' => $quotePayload['quote_number'],
            'status' => $quotePayload['status'],
            'subject' => $payload['subject'] ?: null,
            'quote_date' => $quotePayload['quote_date'],
            'valid_until' => $quotePayload['valid_until'],
            'currency_code' => $currency->code,
            'currency_symbol' => $currency->symbol,
            'currency_position' => $currency->symbol_position,
            'currency_precision' => $currency->precision,
            'document_discount_type' => $calculation['document_discount_type'],
            'document_discount_value' => $calculation['document_discount_value'],
            'document_discount_amount' => $calculation['document_discount_amount'],
            'subtotal_amount' => $calculation['subtotal_amount'],
            'tax_amount' => $calculation['tax_amount'],
            'total_amount' => $calculation['total_amount'],
            'notes' => $payload['notes'] ?: null,
            'terms' => $payload['terms'] ?: null,
        ];

        $quote = $existingQuote ?? new CrmQuote();
        $quote->fill($quoteAttributes);
        $quote->save();

        $quote->items()->delete();

        foreach ($linePayload as $index => $line) {
            $calculatedLine = $calculation['lines'][$index];

            $quote->items()->create([
                'product_id' => $line['product_id'],
                'source_type' => $line['source_type'],
                'position' => $index + 1,
                'item_name' => $line['item_name'],
                'item_description' => $line['item_description'],
                'unit_label' => $line['unit_label'],
                'quantity' => $calculatedLine['quantity'],
                'unit_price' => $calculatedLine['unit_price'],
                'gross_amount' => $calculatedLine['gross_amount'],
                'discount_type' => $calculatedLine['discount_type'],
                'discount_value' => $calculatedLine['discount_value'],
                'discount_amount' => $calculatedLine['discount_amount'],
                'net_amount' => $calculatedLine['net_amount'],
                'tax_rate' => $calculatedLine['tax_rate'],
                'tax_amount' => $calculatedLine['tax_amount'],
                'total_amount' => $calculatedLine['total_amount'],
            ]);
        }

        return $quote->fresh(['owner', 'lead', 'customer', 'contact', 'request', 'items.product']);
    }

    private function normalizedLinePayload(
        array $items,
        bool $allowLineDiscounts,
        float $defaultTaxRate,
        array $allowedInactiveProductIds = []
    ): array
    {
        return collect($items)
            ->values()
            ->map(function (array $item, int $index) use ($allowLineDiscounts, $defaultTaxRate, $allowedInactiveProductIds) {
                $product = filled($item['product_id'] ?? null)
                    ? CrmProduct::query()->find((int) $item['product_id'])
                    : null;

                if ($product !== null && ! $product->active && ! in_array((int) $product->id, $allowedInactiveProductIds, true)) {
                    throw ValidationException::withMessages([
                        'items.' . $index . '.product_id' => 'Inactive products cannot be used on new quotes.',
                    ]);
                }

                $itemName = trim((string) ($item['item_name'] ?? ''));
                $itemDescription = trim((string) ($item['item_description'] ?? ''));
                $unitLabel = trim((string) ($item['unit_label'] ?? ''));

                $itemName = $itemName !== '' ? $itemName : ($product?->name ?? '');
                $itemDescription = $itemDescription !== '' ? $itemDescription : (trim((string) ($product?->description ?? '')) ?: null);
                $unitLabel = $unitLabel !== '' ? $unitLabel : (trim((string) ($product?->default_unit_label ?? '')) ?: 'unit');

                if ($itemName === '') {
                    throw ValidationException::withMessages([
                        'items' => 'Every quote line must include an item name or selected product.',
                    ]);
                }

                return [
                    'product_id' => $product?->id,
                    'source_type' => $product !== null ? 'catalog' : 'custom',
                    'item_name' => $itemName,
                    'item_description' => $itemDescription,
                    'unit_label' => $unitLabel,
                    'quantity' => (float) ($item['quantity'] ?? 1),
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
                    'discount_type' => $allowLineDiscounts ? (string) ($item['discount_type'] ?? 'none') : 'none',
                    'discount_value' => $allowLineDiscounts ? (float) ($item['discount_value'] ?? 0) : 0.0,
                    'tax_rate' => isset($item['tax_rate']) && $item['tax_rate'] !== ''
                        ? (float) $item['tax_rate']
                        : (float) ($product?->default_tax_rate ?? $defaultTaxRate),
                ];
            })
            ->all();
    }

    private function formData(?CrmQuote $crmQuote = null, ?Request $request = null): array
    {
        $crmQuote?->loadMissing('items.product');

        $settings = $this->commercialSettingsRecord()->load('defaultCurrency');
        $selectedLeadId = (int) (($request?->query('lead_id')) ?: 0);
        $selectedCustomerId = (int) (($request?->query('customer_id')) ?: 0);
        $selectedRequestId = (int) (($request?->query('request_id')) ?: 0);
        $selectedContactId = (int) (($request?->query('contact_id')) ?: 0);

        $salesRequests = $this->salesRequestsForSelect();

        if ($selectedRequestId > 0) {
            $selectedRequest = $salesRequests->firstWhere('id', $selectedRequestId);

            if ($selectedRequest !== null) {
                $selectedLeadId = $selectedLeadId ?: (int) ($selectedRequest->lead_id ?? 0);
                $selectedCustomerId = $selectedCustomerId ?: (int) ($selectedRequest->customer_id ?? 0);
                $selectedContactId = $selectedContactId ?: (int) ($selectedRequest->contact_id ?? 0);
            }
        }

        $historicalInactiveProducts = collect();

        if ($crmQuote !== null) {
            $historicalInactiveProducts = $crmQuote->items
                ->pluck('product')
                ->filter(fn ($product) => $product !== null && ! $product->active)
                ->unique('id')
                ->mapWithKeys(function ($product) {
                    return [
                        (int) $product->id => [
                            'id' => (int) $product->id,
                            'name' => $product->name,
                            'code' => $product->code,
                        ],
                    ];
                });
        }

        return [
            'quote' => $crmQuote,
            'leads' => $this->leadsForSelect(),
            'customers' => $this->customersForSelect(),
            'contacts' => $this->contactsForSelect(),
            'salesRequests' => $salesRequests,
            'products' => CrmProduct::query()
                ->where('active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'code',
                    'name',
                    'description',
                    'default_unit_label',
                    'default_unit_price',
                    'default_tax_rate',
                ]),
            'historicalInactiveProducts' => $historicalInactiveProducts,
            'currencies' => CrmCommercialCurrency::query()
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
            'discountTypes' => config('heritage_crm.commercial_discount_types', []),
            'quoteStatuses' => config('heritage_crm.quote_statuses', []),
            'settings' => $settings,
            'defaultSelections' => [
                'lead_id' => $selectedLeadId ?: null,
                'customer_id' => $selectedCustomerId ?: null,
                'request_id' => $selectedRequestId ?: null,
                'contact_id' => $selectedContactId ?: null,
                'currency_id' => $settings->default_currency_id,
            ],
            'lineItems' => $crmQuote?->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'item_name' => $item->item_name,
                    'item_description' => $item->item_description,
                    'unit_label' => $item->unit_label,
                    'quantity' => number_format((float) $item->quantity, 2, '.', ''),
                    'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
                    'tax_rate' => number_format((float) $item->tax_rate, 2, '.', ''),
                    'discount_type' => $item->discount_type,
                    'discount_value' => number_format((float) $item->discount_value, 2, '.', ''),
                ];
            })->all() ?: [$this->blankLineItem($settings)],
            'editableStatuses' => $this->editableStatuses(),
        ];
    }

    private function salesRequestsForSelect()
    {
        return $this->scopeOwned(
            CrmRequest::query()
                ->select(['id', 'owner_id', 'lead_id', 'customer_id', 'contact_id', 'title', 'type'])
                ->where('type', 'sales')
                ->orderBy('title')
        )->get();
    }

    private function quoteIndexQuery(): Builder
    {
        return CrmQuote::query()
            ->when($this->crmUser()->isRep(), function ($query) {
                $query->where('owner_id', $this->crmUser()->id);
            });
    }

    private function authorizeQuoteAccess(CrmQuote $crmQuote): void
    {
        abort_unless($this->crmUser()->canAccessCommercialDocumentRecord($crmQuote->owner_id), 403);
    }

    private function authorizeQuoteEditing(CrmQuote $crmQuote): void
    {
        $this->authorizeQuoteAccess($crmQuote);
        abort_unless($this->canEditQuote($crmQuote), 403);
    }

    private function canEditQuote(CrmQuote $crmQuote): bool
    {
        return in_array($crmQuote->status, $this->editableStatuses(), true);
    }

    private function canShareQuote(CrmQuote $crmQuote): bool
    {
        return $this->crmUser()->canAccessCommercialDocumentRecord($crmQuote->owner_id)
            && in_array($crmQuote->status, ['draft', 'sent'], true);
    }

    private function editableStatuses(): array
    {
        return ['draft', 'sent'];
    }

    private function availableTransitions(CrmQuote $crmQuote): array
    {
        return match ($crmQuote->status) {
            'draft' => [
                'sent' => 'Sent',
                'cancelled' => 'Cancelled',
            ],
            'sent' => [
                'draft' => 'Draft',
                'accepted' => 'Accepted',
                'rejected' => 'Rejected',
                'expired' => 'Expired',
                'cancelled' => 'Cancelled',
            ],
            'rejected', 'expired', 'cancelled' => [
                'draft' => 'Draft',
            ],
            default => [],
        };
    }

    private function blankLineItem($settings): array
    {
        return [
            'product_id' => null,
            'item_name' => '',
            'item_description' => '',
            'unit_label' => 'unit',
            'quantity' => '1.00',
            'unit_price' => '0.00',
            'tax_rate' => number_format((float) $settings->default_tax_rate, 2, '.', ''),
            'discount_type' => 'none',
            'discount_value' => '0.00',
        ];
    }
}
