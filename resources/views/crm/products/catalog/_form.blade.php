<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-field-grid">
        <div class="crm-field">
            <label for="code">Product code</label>
            <input id="code" name="code" value="{{ old('code', $product->code ?? '') }}" placeholder="Enter product code">
        </div>
        <div class="crm-field">
            <label for="name">Product name</label>
            <input id="name" name="name" value="{{ old('name', $product->name ?? '') }}" placeholder="Enter product name" required>
        </div>
        <div class="crm-field">
            <label for="type">Type</label>
            <select id="type" name="type" required>
                @foreach ($productTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $product->type ?? 'license') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="billing_frequency">Billing frequency</label>
            <select id="billing_frequency" name="billing_frequency" required>
                @foreach ($billingFrequencies as $value => $label)
                    <option value="{{ $value }}" @selected(old('billing_frequency', $product->billing_frequency ?? 'one_time') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="default_unit_label">Unit label</label>
            @php
                $selectedUnitLabel = old('default_unit_label', $product->default_unit_label ?? 'unit');
                $hasSelectedUnit = $productUnits->contains('label', $selectedUnitLabel);
            @endphp
            <select id="default_unit_label" name="default_unit_label" required>
                @if (! $hasSelectedUnit && filled($selectedUnitLabel))
                    <option value="{{ $selectedUnitLabel }}" selected>{{ $selectedUnitLabel }} · inactive/custom</option>
                @endif
                @foreach ($productUnits as $unit)
                    <option value="{{ $unit->label }}" @selected($selectedUnitLabel === $unit->label)>{{ $unit->name }} · {{ $unit->label }}</option>
                @endforeach
                @if ($productUnits->isEmpty())
                    <option value="unit" @selected($selectedUnitLabel === 'unit')>Unit · unit</option>
                @endif
            </select>
        </div>
        <div class="crm-field">
            <label for="default_unit_price">Default unit price</label>
            <input id="default_unit_price" name="default_unit_price" type="number" step="0.01" min="0" value="{{ old('default_unit_price', isset($product) ? number_format((float) $product->default_unit_price, 2, '.', '') : '0.00') }}" required>
        </div>
        <div class="crm-field">
            <label for="cpi_increase_rate">CPI increase (%)</label>
            <input id="cpi_increase_rate" name="cpi_increase_rate" type="number" step="0.01" min="0" max="100" value="{{ old('cpi_increase_rate', isset($product) ? number_format((float) $product->cpi_increase_rate, 2, '.', '') : '0.00') }}">
        </div>
        <div class="crm-field">
            <label for="default_tax_rate">Default tax rate (%)</label>
            <input id="default_tax_rate" name="default_tax_rate" type="number" step="0.01" min="0" max="100" value="{{ old('default_tax_rate', isset($product) ? number_format((float) $product->default_tax_rate, 2, '.', '') : '0.00') }}">
        </div>
        <div class="crm-field">
            <label>&nbsp;</label>
            <label class="crm-check">
                <input type="checkbox" name="active" value="1" @checked(old('active', $product->active ?? true))>
                <span>Active catalog item</span>
            </label>
        </div>
        <div class="crm-field full">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Add commercial copy used by sales and finance">{{ old('description', $product->description ?? '') }}</textarea>
        </div>
        <div class="crm-field full">
            <label for="notes">Internal notes</label>
            <textarea id="notes" name="notes" placeholder="Add pricing notes, delivery context, or implementation guidance">{{ old('notes', $product->notes ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($cancelUrl))
            <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="{{ $submitIcon ?? 'fas fa-save' }}"></i> {{ $submitLabel }}</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
        </button>
    </div>
</form>
