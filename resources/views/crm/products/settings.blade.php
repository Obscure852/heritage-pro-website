@extends('layouts.crm')

@section('title', 'Products Settings')
@section('crm_heading', 'Products')
@section('crm_subheading', 'Manage currencies, numbering, discount policy, and default tax behavior for quotes and invoices.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => number_format($currencies->count()), 'label' => 'Currencies'])
    @include('crm.partials.header-stat', ['value' => number_format($currencies->where('is_active', true)->count()), 'label' => 'Active currencies'])
    @include('crm.partials.header-stat', ['value' => number_format($units->where('is_active', true)->count()), 'label' => 'Active units'])
    @include('crm.partials.header-stat', ['value' => number_format($sectors->where('is_active', true)->count()), 'label' => 'Active sectors'])
    @include('crm.partials.header-stat', ['value' => $settings->defaultCurrency?->code ?: 'Unset', 'label' => 'Default'])
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Products Settings',
            'content' => 'Use this page to adjust currencies, numbering, and discount controls for new documents only; saved quotes and invoices keep their own snapshots.',
        ])

        <div class="crm-tabs crm-tabs-top">
            <a href="{{ route('crm.products.settings') }}" @class(['crm-tab', 'is-active' => ($activeSettingsTab ?? 'defaults') === 'defaults'])>
                <i class="bx bx-slider-alt"></i>
                <span>Document defaults</span>
            </a>
            <a href="{{ route('crm.products.settings', ['tab' => 'currencies']) }}" @class(['crm-tab', 'is-active' => ($activeSettingsTab ?? 'defaults') === 'currencies'])>
                <i class="bx bx-money"></i>
                <span>Currencies</span>
            </a>
            <a href="{{ route('crm.products.settings', ['tab' => 'units']) }}" @class(['crm-tab', 'is-active' => ($activeSettingsTab ?? 'defaults') === 'units'])>
                <i class="bx bx-ruler"></i>
                <span>Units</span>
            </a>
            <a href="{{ route('crm.products.settings', ['tab' => 'sectors']) }}" @class(['crm-tab', 'is-active' => ($activeSettingsTab ?? 'defaults') === 'sectors'])>
                <i class="bx bx-purchase-tag-alt"></i>
                <span>Sectors</span>
            </a>
        </div>

        @if (($activeSettingsTab ?? 'defaults') === 'defaults')
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Commercial controls</p>
                        <h2>Document defaults</h2>
                        <p>These defaults apply to new quotes and invoices only. Saved documents keep their own snapshot values.</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#documentDefaultsModal">
                        <i class="bx bx-edit"></i> Edit controls
                    </button>
                </div>

                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Control</th>
                                <th>Current value</th>
                                <th>Scope</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Default currency</strong></td>
                                <td>{{ $settings->defaultCurrency?->code ?: 'Unset' }}</td>
                                <td>New quotes and invoices</td>
                            </tr>
                            <tr>
                                <td><strong>Default tax rate</strong></td>
                                <td>{{ number_format((float) $settings->default_tax_rate, 2) }}%</td>
                                <td>Document tax default</td>
                            </tr>
                            <tr>
                                <td><strong>Quote numbering</strong></td>
                                <td>{{ $settings->quote_prefix }}-{{ $settings->quote_next_sequence }}</td>
                                <td>Next quote sequence</td>
                            </tr>
                            <tr>
                                <td><strong>Invoice numbering</strong></td>
                                <td>{{ $settings->invoice_prefix }}-{{ $settings->invoice_next_sequence }}</td>
                                <td>Next invoice sequence</td>
                            </tr>
                            <tr>
                                <td><strong>Line discounts</strong></td>
                                <td><span class="crm-pill {{ $settings->allow_line_discounts ? 'success' : 'muted' }}">{{ $settings->allow_line_discounts ? 'Enabled' : 'Disabled' }}</span></td>
                                <td>Line item controls</td>
                            </tr>
                            <tr>
                                <td><strong>Document discounts</strong></td>
                                <td><span class="crm-pill {{ $settings->allow_document_discounts ? 'success' : 'muted' }}">{{ $settings->allow_document_discounts ? 'Enabled' : 'Disabled' }}</span></td>
                                <td>Document total controls</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="modal fade" id="documentDefaultsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit commercial controls</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('crm.products.settings.update') }}" class="crm-form" id="documentDefaultsForm">
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
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" form="documentDefaultsForm" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save product settings</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        @elseif (($activeSettingsTab ?? 'defaults') === 'currencies')
            @if ($editCurrency)
            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Currency maintenance</p>
                        <h2>Edit {{ $editCurrency->code }}</h2>
                    </div>
                </div>

                @include('crm.settings._commercial_currency_form', [
                    'action' => route('crm.products.settings.currencies.update', $editCurrency),
                    'method' => 'PATCH',
                    'currency' => $editCurrency,
                    'prefix' => 'edit_currency',
                    'submitLabel' => 'Save currency',
                    'cancelUrl' => route('crm.products.settings', ['tab' => 'currencies']),
                ])
            </section>
            @endif

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Currency list</p>
                    <h2>Configured currencies</h2>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#currencyModal">
                    <i class="bx bx-plus"></i> New currency
                </button>
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
                                            <a href="{{ route('crm.products.settings.edit-currency', $currency) }}" class="btn crm-icon-action" title="Edit currency" aria-label="Edit currency">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if ($currency->is_active)
                                                <form method="POST" action="{{ route('crm.products.settings.currencies.destroy', $currency) }}" class="crm-inline-form" onsubmit="return confirm('Deactivate this currency?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn crm-icon-action crm-icon-danger" title="Delete currency" aria-label="Delete currency">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="modal fade" id="currencyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New currency</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('crm.settings._commercial_currency_form', [
                            'action' => route('crm.products.settings.currencies.store'),
                            'method' => null,
                            'currency' => null,
                            'prefix' => 'new_currency',
                            'submitLabel' => 'Add currency',
                            'cancelUrl' => null,
                        ])
                    </div>
                </div>
            </div>
        </div>
        @elseif (($activeSettingsTab ?? 'defaults') === 'units')
            @if ($editUnit)
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Unit maintenance</p>
                            <h2>Edit {{ $editUnit->name }}</h2>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('crm.products.settings.units.update', $editUnit) }}" class="crm-form">
                        @csrf
                        @method('PATCH')

                        <div class="crm-field-grid">
                            <div class="crm-field">
                                <label for="edit_unit_name">Unit name</label>
                                <input id="edit_unit_name" name="name" value="{{ old('name', $editUnit->name) }}" maxlength="80" required>
                            </div>
                            <div class="crm-field">
                                <label for="edit_unit_label">Unit label</label>
                                <input id="edit_unit_label" name="label" value="{{ old('label', $editUnit->label) }}" maxlength="40" required>
                            </div>
                            <div class="crm-field">
                                <label for="edit_unit_sort_order">Sort order</label>
                                <input id="edit_unit_sort_order" name="sort_order" type="number" min="0" max="999999" value="{{ old('sort_order', $editUnit->sort_order) }}">
                            </div>
                            <div class="crm-field">
                                <label>&nbsp;</label>
                                <label class="crm-check">
                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editUnit->is_active))>
                                    <span>Active unit</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('crm.products.settings', ['tab' => 'units']) }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save unit</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                            </button>
                        </div>
                    </form>
                </section>
            @endif

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Unit list</p>
                        <h2>Configured units</h2>
                        <p>Active units appear in the catalog product form. Saved products and documents keep their stored unit labels.</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#unitModal">
                        <i class="bx bx-plus"></i> New unit
                    </button>
                </div>

                @if ($units->isEmpty())
                    <div class="crm-empty">No product units have been configured yet.</div>
                @else
                    <div class="crm-table-wrap">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Label</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($units as $unit)
                                    <tr>
                                        <td><strong>{{ $unit->name }}</strong></td>
                                        <td>{{ $unit->label }}</td>
                                        <td>{{ $unit->sort_order }}</td>
                                        <td>
                                            <span class="crm-pill {{ $unit->is_active ? 'success' : 'muted' }}">{{ $unit->is_active ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td class="crm-table-actions">
                                            <div class="crm-action-row">
                                                <a href="{{ route('crm.products.settings.edit-unit', $unit) }}" class="btn crm-icon-action" title="Edit unit" aria-label="Edit unit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if ($unit->is_active)
                                                    <form method="POST" action="{{ route('crm.products.settings.units.destroy', $unit) }}" class="crm-inline-form" onsubmit="return confirm('Deactivate this unit?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn crm-icon-action crm-icon-danger" title="Delete unit" aria-label="Delete unit">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="modal fade" id="unitModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">New unit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" id="unitForm" action="{{ route('crm.products.settings.units.store') }}" class="crm-form">
                                @csrf

                                <div class="crm-field-grid">
                                    <div class="crm-field">
                                        <label for="unit_name">Unit name</label>
                                        <input id="unit_name" name="name" value="{{ old('name') }}" maxlength="80" placeholder="License" required>
                                    </div>
                                    <div class="crm-field">
                                        <label for="unit_label">Unit label</label>
                                        <input id="unit_label" name="label" value="{{ old('label') }}" maxlength="40" placeholder="license" required>
                                    </div>
                                    <div class="crm-field">
                                        <label for="unit_sort_order">Sort order</label>
                                        <input id="unit_sort_order" name="sort_order" type="number" min="0" max="999999" value="{{ old('sort_order', 0) }}">
                                    </div>
                                    <div class="crm-field">
                                        <label>&nbsp;</label>
                                        <label class="crm-check">
                                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                                            <span>Active unit</span>
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" form="unitForm" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Add unit</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @elseif (($activeSettingsTab ?? 'defaults') === 'sectors')
            @if ($editSector)
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Sector maintenance</p>
                            <h2>Edit {{ $editSector->name }}</h2>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('crm.products.settings.sectors.update', $editSector) }}" class="crm-form">
                        @csrf
                        @method('PATCH')

                        <div class="crm-field-grid">
                            <div class="crm-field">
                                <label for="edit_sector_name">Sector name</label>
                                <input id="edit_sector_name" name="name" value="{{ old('name', $editSector->name) }}" maxlength="120" required>
                            </div>
                            <div class="crm-field">
                                <label for="edit_sector_sort_order">Sort order</label>
                                <input id="edit_sector_sort_order" name="sort_order" type="number" min="0" max="999999" value="{{ old('sort_order', $editSector->sort_order) }}">
                            </div>
                            <div class="crm-field">
                                <label>&nbsp;</label>
                                <label class="crm-check">
                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editSector->is_active))>
                                    <span>Active sector</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('crm.products.settings', ['tab' => 'sectors']) }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save sector</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                            </button>
                        </div>
                    </form>
                </section>
            @endif

            <section class="crm-card">
                <div class="crm-card-title">
                    <div>
                        <p class="crm-kicker">Sector list</p>
                        <h2>Configured sectors</h2>
                        <p>Active sectors appear in lead and customer forms, and are accepted in lead imports.</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectorModal">
                        <i class="bx bx-plus"></i> New sector
                    </button>
                </div>

                @if ($sectors->isEmpty())
                    <div class="crm-empty">No sectors have been configured yet.</div>
                @else
                    <div class="crm-table-wrap">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    <th>Sector</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sectors as $sector)
                                    <tr>
                                        <td><strong>{{ $sector->name }}</strong></td>
                                        <td>{{ $sector->sort_order }}</td>
                                        <td>
                                            <span class="crm-pill {{ $sector->is_active ? 'success' : 'muted' }}">{{ $sector->is_active ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td class="crm-table-actions">
                                            <div class="crm-action-row">
                                                <a href="{{ route('crm.products.settings.edit-sector', $sector) }}" class="btn crm-icon-action" title="Edit sector" aria-label="Edit sector">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if ($sector->is_active)
                                                    <form method="POST" action="{{ route('crm.products.settings.sectors.destroy', $sector) }}" class="crm-inline-form" onsubmit="return confirm('Deactivate this sector?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn crm-icon-action crm-icon-danger" title="Delete sector" aria-label="Delete sector">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="modal fade" id="sectorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">New sector</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" id="sectorForm" action="{{ route('crm.products.settings.sectors.store') }}" class="crm-form">
                                @csrf

                                <div class="crm-field-grid">
                                    <div class="crm-field">
                                        <label for="sector_name">Sector name</label>
                                        <input id="sector_name" name="name" value="{{ old('name') }}" maxlength="120" placeholder="Education" required>
                                    </div>
                                    <div class="crm-field">
                                        <label for="sector_sort_order">Sort order</label>
                                        <input id="sector_sort_order" name="sort_order" type="number" min="0" max="999999" value="{{ old('sort_order', 0) }}">
                                    </div>
                                    <div class="crm-field">
                                        <label>&nbsp;</label>
                                        <label class="crm-check">
                                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                                            <span>Active sector</span>
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light crm-btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" form="sectorForm" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Add sector</span>
                                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
