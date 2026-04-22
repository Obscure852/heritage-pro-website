@extends('layouts.crm')

@section('title', 'CRM Quotes')
@section('crm_heading', 'Products')
@section('crm_subheading', 'Prepare, review, and track quotes against leads or customers from inside the CRM workspace.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => number_format($draftCount), 'label' => 'Draft'])
    @include('crm.partials.header-stat', ['value' => number_format($sentCount), 'label' => 'Sent'])
    @include('crm.partials.header-stat', ['value' => number_format($acceptedCount), 'label' => 'Accepted'])
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.products._tabs', ['activeSection' => 'quotes'])

        @include('crm.partials.helper-text', [
            'title' => 'Quote Directory',
            'content' => 'Use the filters below to narrow the quote list by status or search terms, then open a document to review totals, lifecycle actions, or sharing.',
        ])

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find quotes</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.products.quotes.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Number, subject, account, contact">
                    </div>
                    <div class="crm-field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All statuses</option>
                            @foreach ($quoteStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.products.quotes.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                    @if ($canCreateQuotes)
                        <a href="{{ route('crm.products.quotes.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus-circle"></i> New quote
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Quote workflow</p>
                    <h2>Commercial quotes</h2>
                    <p>Every saved quote snapshots catalog values, discounts, tax, and currency so historical totals remain stable.</p>
                </div>
            </div>

            @if ($quotes->isEmpty())
                <div class="crm-empty">No quote records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Quote</th>
                                <th>Account</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quotes as $quote)
                                <tr>
                                    <td>
                                        <strong><a href="{{ route('crm.products.quotes.show', $quote) }}">{{ $quote->quote_number }}</a></strong>
                                        <span class="crm-muted">{{ $quote->subject ?: 'No subject' }} · {{ $quote->items_count }} line(s) · {{ $quote->quote_date?->format('d M Y') }}</span>
                                    </td>
                                    <td>{{ $quote->customer?->company_name ?: $quote->lead?->company_name ?: 'Unassigned' }}</td>
                                    <td>{{ $quote->contact?->name ?: 'No contact' }}</td>
                                    <td>
                                        <span class="crm-pill {{ in_array($quote->status, ['accepted', 'sent'], true) ? 'success' : ($quote->status === 'draft' ? 'primary' : 'muted') }}">
                                            {{ $quoteStatuses[$quote->status] ?? ucfirst($quote->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $quote->currency_code }} {{ number_format((float) $quote->total_amount, 2) }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            @include('crm.partials.view-button', [
                                                'url' => route('crm.products.quotes.show', $quote),
                                                'label' => 'View quote',
                                            ])
                                            @if (in_array($quote->status, ['draft', 'sent'], true))
                                                <a href="{{ route('crm.products.quotes.edit', $quote) }}" class="btn crm-icon-action" title="Edit quote" aria-label="Edit quote">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $quotes])
            @endif
        </section>
    </div>
@endsection
