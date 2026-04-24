<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\ProductUpsertRequest;
use App\Models\CrmProduct;
use App\Models\CrmProductUnit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends CrmController
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'type' => (string) $request->query('type', ''),
            'billing_frequency' => (string) $request->query('billing_frequency', ''),
            'active' => (string) $request->query('active', ''),
        ];

        $products = CrmProduct::query()
            ->withCount(['quoteItems', 'invoiceItems'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($productQuery) use ($filters) {
                    $productQuery->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('code', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('description', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when($filters['type'] !== '', function ($query) use ($filters) {
                $query->where('type', $filters['type']);
            })
            ->when($filters['billing_frequency'] !== '', function ($query) use ($filters) {
                $query->where('billing_frequency', $filters['billing_frequency']);
            })
            ->when($filters['active'] !== '', function ($query) use ($filters) {
                $query->where('active', $filters['active'] === '1');
            })
            ->orderByDesc('active')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('crm.products.catalog.index', [
            'products' => $products,
            'filters' => $filters,
            'productTypes' => config('heritage_crm.commercial_product_types', []),
            'billingFrequencies' => config('heritage_crm.commercial_billing_frequencies', []),
            'canManageCatalog' => $this->crmUser()->canManageCommercialCatalog(),
            'totalProducts' => CrmProduct::query()->count(),
            'activeProducts' => CrmProduct::query()->where('active', true)->count(),
            'inactiveProducts' => CrmProduct::query()->where('active', false)->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorizeCommercialCatalogManagement();

        return view('crm.products.catalog.create', $this->formData());
    }

    public function store(ProductUpsertRequest $request): RedirectResponse
    {
        $this->authorizeCommercialCatalogManagement();

        $product = CrmProduct::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('crm.products.catalog.show', $product)
            ->with('crm_success', 'Catalog item created successfully.');
    }

    public function show(CrmProduct $crmProduct): View
    {
        return view('crm.products.catalog.show', [
            'product' => $crmProduct->loadCount(['quoteItems', 'invoiceItems']),
            'productTypes' => config('heritage_crm.commercial_product_types', []),
            'billingFrequencies' => config('heritage_crm.commercial_billing_frequencies', []),
            'canManageCatalog' => $this->crmUser()->canManageCommercialCatalog(),
        ]);
    }

    public function edit(CrmProduct $crmProduct): View
    {
        $this->authorizeCommercialCatalogManagement();

        return view('crm.products.catalog.edit', $this->formData($crmProduct));
    }

    public function update(ProductUpsertRequest $request, CrmProduct $crmProduct): RedirectResponse
    {
        $this->authorizeCommercialCatalogManagement();

        $crmProduct->update($this->validatedPayload($request));

        return redirect()
            ->route('crm.products.catalog.edit', $crmProduct)
            ->with('crm_success', 'Catalog item updated successfully.');
    }

    public function updateStatus(Request $request, CrmProduct $crmProduct): RedirectResponse
    {
        $this->authorizeCommercialCatalogManagement();

        $payload = $request->validate([
            'active' => ['required', 'boolean'],
        ]);

        $crmProduct->update([
            'active' => (bool) $payload['active'],
        ]);

        $message = $crmProduct->active
            ? 'Catalog item reactivated successfully.'
            : 'Catalog item deactivated successfully.';

        return redirect()
            ->route('crm.products.catalog.show', $crmProduct)
            ->with('crm_success', $message);
    }

    private function formData(?CrmProduct $crmProduct = null): array
    {
        return [
            'product' => $crmProduct,
            'productTypes' => config('heritage_crm.commercial_product_types', []),
            'billingFrequencies' => config('heritage_crm.commercial_billing_frequencies', []),
            'productUnits' => CrmProductUnit::query()->active()->ordered()->get(),
        ];
    }

    private function validatedPayload(ProductUpsertRequest $request): array
    {
        $payload = $request->validated();
        $payload['active'] = $request->has('active') ? $request->boolean('active') : true;
        $payload['cpi_increase_rate'] = $payload['cpi_increase_rate'] ?? 0;
        $payload['default_tax_rate'] = $payload['default_tax_rate'] ?? 0;

        return $payload;
    }
}
