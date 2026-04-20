@php
    $extraQuery = $extraQuery ?? [];
    $selectClass = $selectClass ?? '';
@endphp

@if (($seriesOptions ?? collect())->isNotEmpty())
    <form action="{{ $action }}" method="GET" class="invigilation-filter-row mb-3">
        @foreach ($extraQuery as $key => $value)
            @if ($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <select name="series_id" class="form-select module-filter-select {{ $selectClass }}" onchange="this.form.submit()">
            @foreach ($seriesOptions as $seriesOption)
                <option value="{{ $seriesOption->id }}" {{ (int) ($series?->id ?? 0) === (int) $seriesOption->id ? 'selected' : '' }}>
                    {{ $seriesOption->name }} | Term {{ $seriesOption->term?->term ?? '-' }}, {{ $seriesOption->term?->year ?? '-' }}
                </option>
            @endforeach
        </select>
        <noscript>
            <button type="submit" class="btn btn-light">
                <i class="fas fa-filter me-1"></i> Apply
            </button>
        </noscript>
    </form>
@endif
