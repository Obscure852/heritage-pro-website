<?php

namespace App\Http\Controllers\Crm;

use App\Models\CrmInvoice;
use App\Models\CrmQuote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CommercialWorkspaceController extends CrmController
{
    public function quotes(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $baseQuery = $this->quoteQuery();

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
        ]);
    }

    public function invoices(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $baseQuery = $this->invoiceQuery();

        $invoices = (clone $baseQuery)
            ->with(['owner', 'lead', 'customer', 'contact'])
            ->withCount('items')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($invoiceQuery) use ($filters) {
                    $invoiceQuery->where('invoice_number', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('subject', 'like', '%' . $filters['q'] . '%')
                        ->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('company_name', 'like', '%' . $filters['q'] . '%'))
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('company_name', 'like', '%' . $filters['q'] . '%'))
                        ->orWhereHas('contact', fn ($contactQuery) => $contactQuery->where('name', 'like', '%' . $filters['q'] . '%'));
                });
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('crm.products.invoices.index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'invoiceStatuses' => config('heritage_crm.invoice_statuses', []),
            'draftCount' => (clone $baseQuery)->where('status', 'draft')->count(),
            'issuedCount' => (clone $baseQuery)->where('status', 'issued')->count(),
        ]);
    }

    private function quoteQuery(): Builder
    {
        return CrmQuote::query()
            ->when($this->crmUser()->isRep(), function ($query) {
                $query->where('owner_id', $this->crmUser()->id);
            });
    }

    private function invoiceQuery(): Builder
    {
        return CrmInvoice::query()
            ->when($this->crmUser()->isRep(), function ($query) {
                $query->where('owner_id', $this->crmUser()->id);
            });
    }
}
