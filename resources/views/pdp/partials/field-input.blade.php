@php
    static $resolvedCommentBankCache = null;
    $inputName = $namePrefix . '[' . $field->key . ']';
    $inputId = ($formKey ?? 'field') . '-' . $field->key;
    $isDisabled = $disabled ?? false;
    $errorKey = 'values.' . $field->key;
    $errorClass = $errors->has($errorKey) ? ' is-invalid' : '';
    $currentValue = old('values.' . $field->key, $value);
    if (!is_array($commentBank ?? null)) {
        $resolvedCommentBankCache = $resolvedCommentBankCache ?? app(\App\Services\Pdp\PdpSettingsService::class)->commentBank();
    }
    $resolvedCommentBank = is_array($commentBank ?? null) ? $commentBank : ($resolvedCommentBankCache ?? []);
    $commentBankRole = match ($field->key) {
        'supervisee_comment' => 'supervisee',
        'supervisor_comment' => 'supervisor',
        default => null,
    };
    $commentSuggestions = $commentBankRole ? ($resolvedCommentBank[$commentBankRole . '_comments'] ?? []) : [];
    $selectedCommentSuggestion = is_scalar($currentValue) ? trim((string) $currentValue) : '';
    $usesCommentBank = $commentSuggestions !== [];
    $commentBankSelectId = $inputId . '-comment-bank';
    $placeholder = match ($field->field_type) {
        'select' => 'Select ' . \Illuminate\Support\Str::of($field->label)->lower(),
        default => 'Enter ' . \Illuminate\Support\Str::of($field->label)->lower(),
    };
@endphp

<div class="form-group">
    <label class="form-label" for="{{ $inputId }}">
        {{ $field->label }}
        @if ($field->required)
            <span class="text-danger">*</span>
        @endif
        @if ($field->period_scope)
            <span class="badge-soft badge-soft-dark ms-1">{{ $viewService->periodLabel($field->period_scope) }}</span>
        @endif
    </label>

    @switch($field->field_type)
        @case('textarea')
        @case('rich_text')
        @case('comment')
            @if ($commentSuggestions !== [])
                <div class="comment-bank-picker" data-comment-bank>
                    <div class="comment-bank-picker-header">
                        <div class="comment-bank-picker-label">Saved {{ $commentBankRole }} comments</div>
                        <div class="comment-bank-picker-meta">{{ count($commentSuggestions) }} available</div>
                    </div>
                    <div class="comment-bank-picker-controls">
                        <select
                            id="{{ $commentBankSelectId }}"
                            name="comment_bank[{{ $field->key }}]"
                            class="form-select form-select-sm"
                            data-comment-bank-select
                            data-comment-target="{{ $inputId }}"
                            @disabled($isDisabled)
                            onchange="(function(select){var targetId=select.getAttribute('data-comment-target');var textarea=targetId?document.getElementById(targetId):null;if(!textarea){return;}textarea.value=select.value.trim();textarea.dispatchEvent(new Event('input',{bubbles:true}));textarea.dispatchEvent(new Event('change',{bubbles:true}));if(window.pdpApplyCommentBankSelection){window.pdpApplyCommentBankSelection(select);}})(this)"
                        >
                            <option value="" @selected($selectedCommentSuggestion === '')>Select a saved {{ $commentBankRole }} comment</option>
                            @foreach ($commentSuggestions as $suggestion)
                                <option value="{{ $suggestion }}" @selected(trim((string) $suggestion) === $selectedCommentSuggestion)>{{ \Illuminate\Support\Str::limit($suggestion, 110) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="comment-bank-picker-hint">
                        Selecting a saved comment fills the textarea below.
                        @if ($isDisabled)
                            This field is currently read-only for your role or workflow stage.
                        @endif
                    </div>
                </div>
            @endif
            <textarea
                id="{{ $inputId }}"
                name="{{ $inputName }}"
                rows="3"
                class="form-control{{ $errorClass }}"
                placeholder="{{ $placeholder }}"
                @if ($commentSuggestions !== [])
                    data-comment-bank-textarea
                    data-comment-bank-select="{{ $commentBankSelectId }}"
                @endif
                @if ($usesCommentBank && $isDisabled) readonly aria-disabled="true" @else @disabled($isDisabled) @endif
            >{{ old('values.' . $field->key, is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $value) }}</textarea>
            @break

        @case('number')
            <input id="{{ $inputId }}" type="number" step="any" name="{{ $inputName }}" class="form-control{{ $errorClass }}" @disabled($isDisabled)
                value="{{ $currentValue }}" placeholder="{{ $placeholder }}">
            @break

        @case('date')
            <input id="{{ $inputId }}" type="date" name="{{ $inputName }}" class="form-control{{ $errorClass }}" @disabled($isDisabled)
                value="{{ old('values.' . $field->key, $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value) }}" placeholder="{{ $placeholder }}">
            @break

        @case('select')
            @php
                $resolvedOptions = collect($options);
                if (
                    $currentValue !== null
                    && $currentValue !== ''
                    && !$resolvedOptions->contains(fn ($option) => $viewService->optionValue(data_get($option, 'value')) === $viewService->optionValue($currentValue))
                ) {
                    $resolvedOptions->prepend([
                        'value' => $currentValue,
                        'label' => $currentValue,
                    ]);
                }
            @endphp
            <select id="{{ $inputId }}" name="{{ $inputName }}" class="form-select{{ $errorClass }}" @disabled($isDisabled)>
                <option value="">{{ $placeholder }}</option>
                @foreach ($resolvedOptions as $option)
                    @php
                        $optionValue = $viewService->optionValue(data_get($option, 'value'));
                        $selectedValue = $viewService->optionValue($currentValue);
                    @endphp
                    <option value="{{ $optionValue }}" @selected($selectedValue === $optionValue)>
                        {{ data_get($option, 'label', $optionValue) }}
                    </option>
                @endforeach
            </select>
            @break

        @case('radio_scale')
            <div id="{{ $inputId }}" class="d-flex flex-wrap gap-3">
                @forelse ($options as $option)
                    @php
                        $optionValue = $viewService->optionValue(data_get($option, 'value'));
                        $selectedValue = $viewService->optionValue(old('values.' . $field->key, $value));
                    @endphp
                    <label class="form-check form-check-inline border rounded px-3 py-2 mb-0">
                        <input class="form-check-input" type="radio" name="{{ $inputName }}" value="{{ $optionValue }}" @disabled($isDisabled)
                            @checked($selectedValue === $optionValue)>
                        <span class="form-check-label ms-1">{{ data_get($option, 'label', $optionValue) }}</span>
                    </label>
                @empty
                    <input id="{{ $inputId }}" type="number" name="{{ $inputName }}" class="form-control{{ $errorClass }}" @disabled($isDisabled)
                        value="{{ old('values.' . $field->key, $value) }}">
                @endforelse
            </div>
            @break

        @case('metric_pair')
            <div class="row g-2">
                <div class="col-md-6">
                    <input id="{{ $inputId }}-metric" type="text" name="{{ $inputName }}[metric]" class="form-control{{ $errorClass }}" @disabled($isDisabled)
                        value="{{ old('values.' . $field->key . '.metric', data_get($value, 'metric')) }}"
                        placeholder="Metric label">
                </div>
                <div class="col-md-6">
                    <input id="{{ $inputId }}-value" type="text" name="{{ $inputName }}[value]" class="form-control{{ $errorClass }}" @disabled($isDisabled)
                        value="{{ old('values.' . $field->key . '.value', data_get($value, 'value')) }}"
                        placeholder="Metric value">
                </div>
            </div>
            @break

        @case('structured_table')
            <textarea id="{{ $inputId }}" name="{{ $inputName }}[raw]" rows="5" class="form-control{{ $errorClass }}" @disabled($isDisabled)
                placeholder="Enter JSON, CSV-style rows, or structured evidence text">{{ old('values.' . $field->key . '.raw', data_get($value, 'raw')) }}</textarea>
            @break

        @case('attachment')
            <input id="{{ $inputId }}" type="text" name="{{ $inputName }}" class="form-control{{ $errorClass }}" @disabled($isDisabled)
                value="{{ old('values.' . $field->key, $value) }}"
                placeholder="Document path or URL">
            @break

        @default
            <input id="{{ $inputId }}" type="text" name="{{ $inputName }}" class="form-control{{ $errorClass }}" @disabled($isDisabled)
                value="{{ $currentValue }}" placeholder="{{ $placeholder }}">
    @endswitch

    @error($errorKey)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
