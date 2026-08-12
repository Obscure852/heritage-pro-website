@php
    $fields = $fields ?? [];
    $data = is_array($data ?? null) ? $data : [];
    $headingPrefix = $headingPrefix ?? 'submission-stage';
@endphp

<div class="crm-submission-field-grid">
    @forelse ($fields as $field)
        @php
            $value = $data[$field['key']] ?? null;
            $fieldType = $field['type'] ?? 'text';
            $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $field['key']));
        @endphp

        @if ($fieldType === 'repeatable')
            <section class="crm-submission-repeatable" aria-labelledby="{{ $headingPrefix }}-{{ $field['key'] }}">
                <div class="crm-submission-repeatable-heading">
                    <div>
                        <h4 id="{{ $headingPrefix }}-{{ $field['key'] }}">{{ $label }}</h4>
                        @if (! empty($field['help']))
                            <p>{{ $field['help'] }}</p>
                        @endif
                    </div>
                    <span class="crm-pill muted">{{ is_array($value) ? count($value) : 0 }} {{ is_array($value) && count($value) === 1 ? 'entry' : 'entries' }}</span>
                </div>

                @if (is_array($value) && $value !== [])
                    <div class="crm-submission-repeatable-list">
                        @foreach ($value as $index => $row)
                            <div class="crm-submission-repeatable-row">
                                <h5>{{ $label }} {{ $index + 1 }}</h5>
                                @include('crm.client-setup.partials.stage-data', [
                                    'fields' => $field['fields'] ?? [],
                                    'data' => is_array($row) ? $row : [],
                                    'headingPrefix' => $headingPrefix . '-' . $field['key'] . '-' . $index,
                                ])
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="crm-empty">No {{ strtolower($label) }} recorded.</p>
                @endif
            </section>
        @else
            @php
                $isEmpty = $value === null || $value === '' || $value === [];
                $display = 'Not provided';

                if (! $isEmpty && $fieldType === 'boolean') {
                    $display = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
                } elseif (! $isEmpty && $fieldType === 'multiselect') {
                    $options = $field['options'] ?? [];
                    $display = collect((array) $value)->map(fn ($item) => $options[$item] ?? ucfirst(str_replace('_', ' ', (string) $item)))->implode(', ');
                } elseif (! $isEmpty && is_scalar($value)) {
                    $display = (string) $value;
                }
            @endphp

            <div class="crm-submission-field {{ $isEmpty ? 'is-empty' : '' }}">
                <dt>{{ $label }}</dt>
                <dd>{{ $display }}</dd>
            </div>
        @endif
    @empty
        <p class="crm-empty">No structured data has been recorded for this category.</p>
    @endforelse
</div>
