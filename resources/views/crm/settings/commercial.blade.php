@extends('layouts.crm')

@section('title', 'CRM Settings - Commercial')
@section('crm_heading', 'Settings')
@section('crm_subheading', 'Manage currencies, numbering, discount policy, and default commercial tax behavior for the CRM products workspace.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => number_format($currencies->count()), 'label' => 'Supported'])
    @include('crm.partials.header-stat', ['value' => number_format($currencies->where('is_active', true)->count()), 'label' => 'Active'])
    @include('crm.partials.header-stat', ['value' => $settings->defaultCurrency?->code ?: 'Unset', 'label' => 'Default'])
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.settings._tabs', ['activeSection' => 'commercial'])

        @include('crm.partials.helper-text', [
            'title' => 'Commercial Settings',
            'content' => 'Use this page to adjust currencies, numbering, and discount controls for new documents only; saved quotes and invoices keep their own snapshots.',
        ])

        <div class="crm-grid cols-2">
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Commercial controls</p>
                        <h2>Document defaults</h2>
                        <p>These defaults apply to new quotes and invoices only. Saved documents keep their own snapshot values.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('crm.settings.commercial.update') }}" class="crm-form">
                    @csrf
                    @method('PATCH')

                    <div class="crm-field-grid">
                        <div class="crm-field">
                            <label for="default_currency_id">Default currency</label>
                            <select id="default_currency_id" name="default_currency_id" required>
                                @foreach ($currencies->where('is_active', true) as $currency)
                                    <option value="{{ $currency->id }}" @selected((int) old('default_currency_id', $settings->default_currency_id) === $currency->id)>
                                        {{ $currency->code }} · {{ $currency->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="crm-field">
                            <label for="default_tax_rate">Default tax rate (%)</label>
                            <input id="default_tax_rate" name="default_tax_rate" type="number" step="0.01" min="0" max="100" value="{{ old('default_tax_rate', number_format((float) $settings->default_tax_rate, 2, '.', '')) }}">
                        </div>
                        <div class="crm-field">
                            <label for="quote_prefix">Quote prefix</label>
                            <input id="quote_prefix" name="quote_prefix" value="{{ old('quote_prefix', $settings->quote_prefix) }}" maxlength="20" required>
                        </div>
                        <div class="crm-field">
                            <label for="quote_next_sequence">Next quote sequence</label>
                            <input id="quote_next_sequence" name="quote_next_sequence" type="number" min="1" max="999999999" value="{{ old('quote_next_sequence', $settings->quote_next_sequence) }}" required>
                        </div>
                        <div class="crm-field">
                            <label for="invoice_prefix">Invoice prefix</label>
                            <input id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" maxlength="20" required>
                        </div>
                        <div class="crm-field">
                            <label for="invoice_next_sequence">Next invoice sequence</label>
                            <input id="invoice_next_sequence" name="invoice_next_sequence" type="number" min="1" max="999999999" value="{{ old('invoice_next_sequence', $settings->invoice_next_sequence) }}" required>
                        </div>
                        <div class="crm-field">
                            <label>&nbsp;</label>
                            <label class="crm-check">
                                <input type="checkbox" name="allow_line_discounts" value="1" @checked(old('allow_line_discounts', $settings->allow_line_discounts))>
                                <span>Allow line discounts</span>
                            </label>
                        </div>
                        <div class="crm-field">
                            <label>&nbsp;</label>
                            <label class="crm-check">
                                <input type="checkbox" name="allow_document_discounts" value="1" @checked(old('allow_document_discounts', $settings->allow_document_discounts))>
                                <span>Allow document discounts</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-loading">
                            <span class="btn-text"><i class="fas fa-save"></i> Save commercial settings</span>
                            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                        </button>
                    </div>
                </form>
            </section>

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Supported currencies</p>
                        <h2>Add currency</h2>
                    </div>
                </div>

                @include('crm.settings._commercial_currency_form', [
                    'action' => route('crm.settings.commercial.currencies.store'),
                    'method' => null,
                    'currency' => null,
                    'prefix' => 'new_currency',
                    'submitLabel' => 'Add currency',
                    'cancelUrl' => null,
                ])
            </section>
        </div>

        @if ($editCurrency)
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Currency maintenance</p>
                        <h2>Edit {{ $editCurrency->code }}</h2>
                    </div>
                </div>

                @include('crm.settings._commercial_currency_form', [
                    'action' => route('crm.settings.commercial.currencies.update', $editCurrency),
                    'method' => 'PATCH',
                    'currency' => $editCurrency,
                    'prefix' => 'edit_currency',
                    'submitLabel' => 'Save currency',
                    'cancelUrl' => route('crm.settings.commercial'),
                ])
            </section>
        @endif

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Currency list</p>
                    <h2>Configured currencies</h2>
                </div>
            </div>

            @if ($errors->has('currency'))
                <div class="crm-help">
                    {{ $errors->first('currency') }}
                </div>
            @endif

            @if ($currencies->isEmpty())
                <div class="crm-empty">No currencies have been configured yet.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Currency</th>
                                <th>Symbol</th>
                                <th>Precision</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($currencies as $currency)
                                <tr>
                                    <td>
                                        <strong>{{ $currency->code }}</strong>
                                        <span class="crm-muted">{{ $currency->name }}</span>
                                    </td>
                                    <td>{{ $currency->symbol }} · {{ $currency->symbol_position === 'before' ? 'Before amount' : 'After amount' }}</td>
                                    <td>{{ $currency->precision }}</td>
                                    <td>
                                        <div class="crm-inline">
                                            <span class="crm-pill {{ $currency->is_active ? 'success' : 'muted' }}">{{ $currency->is_active ? 'Active' : 'Inactive' }}</span>
                                            @if ($settings->default_currency_id === $currency->id)
                                                <span class="crm-pill primary">Default</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.settings.commercial.edit-currency', $currency) }}" class="btn crm-icon-action" title="Edit currency" aria-label="Edit currency">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
