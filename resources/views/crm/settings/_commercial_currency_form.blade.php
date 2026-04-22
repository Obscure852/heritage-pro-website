<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-field-grid">
        <div class="crm-field">
            <label for="{{ $prefix }}_code">Currency code</label>
            <input id="{{ $prefix }}_code" name="code" value="{{ old('code', $currency->code ?? '') }}" maxlength="3" placeholder="BWP" required>
        </div>
        <div class="crm-field">
            <label for="{{ $prefix }}_name">Currency name</label>
            <input id="{{ $prefix }}_name" name="name" value="{{ old('name', $currency->name ?? '') }}" placeholder="Botswana Pula" required>
        </div>
        <div class="crm-field">
            <label for="{{ $prefix }}_symbol">Symbol</label>
            <input id="{{ $prefix }}_symbol" name="symbol" value="{{ old('symbol', $currency->symbol ?? '') }}" placeholder="P" required>
        </div>
        <div class="crm-field">
            <label for="{{ $prefix }}_symbol_position">Symbol position</label>
            <select id="{{ $prefix }}_symbol_position" name="symbol_position" required>
                <option value="before" @selected(old('symbol_position', $currency->symbol_position ?? 'before') === 'before')>Before amount</option>
                <option value="after" @selected(old('symbol_position', $currency->symbol_position ?? 'after') === 'after')>After amount</option>
            </select>
        </div>
        <div class="crm-field">
            <label for="{{ $prefix }}_precision">Precision</label>
            <input id="{{ $prefix }}_precision" name="precision" type="number" min="0" max="4" value="{{ old('precision', $currency->precision ?? 2) }}" required>
        </div>
        <div class="crm-field">
            <label>&nbsp;</label>
            <label class="crm-check">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $currency->is_active ?? true))>
                <span>Active currency</span>
            </label>
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($cancelUrl))
            <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save"></i> {{ $submitLabel }}</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
        </button>
    </div>
</form>
