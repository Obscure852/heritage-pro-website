<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $pdfTitle }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; margin: 24px; }
        .preview-banner { background: #111827; color: #fff; padding: 10px 14px; margin-bottom: 18px; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; }
        .header { display: table; width: 100%; margin-bottom: 18px; }
        .header-cell { display: table-cell; vertical-align: top; }
        .header-right { text-align: right; }
        .logo { max-width: 72px; max-height: 72px; margin-bottom: 8px; }
        .eyebrow { text-transform: uppercase; font-size: 10px; letter-spacing: 0.08em; color: #6b7280; }
        h1 { margin: 4px 0 6px; font-size: 24px; }
        .muted { color: #6b7280; }
        .meta-grid, .field-grid, .review-grid { width: 100%; border-collapse: collapse; }
        .meta-grid td, .field-grid td { width: 50%; padding: 8px 10px; border: 1px solid #e5e7eb; vertical-align: top; }
        .field-label { text-transform: uppercase; font-size: 10px; letter-spacing: 0.06em; color: #6b7280; margin-bottom: 4px; }
        .panel { margin-top: 18px; }
        .panel h2 { font-size: 15px; margin: 0 0 10px; padding-bottom: 6px; border-bottom: 1px solid #111827; }
        .entry { border: 1px solid #d1d5db; margin-bottom: 12px; }
        .entry-title { background: #f3f4f6; padding: 8px 10px; font-weight: bold; }
        .review-grid td { width: 33.33%; border: 1px solid #e5e7eb; padding: 10px; vertical-align: top; }
        .signature-image { max-height: 42px; max-width: 140px; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table th, .signature-table td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; vertical-align: top; }
        pre { white-space: pre-wrap; word-break: break-word; margin: 0; font-family: DejaVu Sans Mono, monospace; font-size: 11px; }
    </style>
</head>
<body>
    @inject('pdpSettings', 'App\Services\Pdp\PdpSettingsService')
    @php
        $generalGuidanceLines = collect(preg_split('/\r\n|\r|\n/', (string) ($pdpSettings->generalSettings()['general_guidance'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();
    @endphp
    @if (!empty($isPreview))
        <div class="preview-banner">HTML Preview</div>
    @endif

    <div class="header">
        <div class="header-cell">
            @if ($showLogo && $logoBase64)
                <img src="{{ $logoBase64 }}" alt="School logo" class="logo">
            @endif
            <div class="eyebrow">{{ $school?->school_name ?: 'Staff PDP' }}</div>
            <h1>{{ $pdfTitle }}</h1>
            <div class="muted">{{ $plan->template->name }} | {{ $plan->template->code }} | v{{ $plan->template->version }}</div>
        </div>
        <div class="header-cell header-right">
            <div class="eyebrow">Generated</div>
            <div>{{ $generatedAt->format('Y-m-d H:i') }}</div>
            <div class="muted">{{ ucfirst($plan->status) }} plan</div>
        </div>
    </div>

    <table class="meta-grid">
        <tr>
            <td>
                <div class="field-label">Employee</div>
                <div>{{ $plan->user->full_name }}</div>
            </td>
            <td>
                <div class="field-label">Supervisor</div>
                <div>{{ $plan->supervisor?->full_name ?? 'Not assigned' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">Plan Period</div>
                <div>{{ $plan->plan_period_start->format('Y-m-d') }} to {{ $plan->plan_period_end->format('Y-m-d') }}</div>
            </td>
            <td>
                <div class="field-label">Current Period</div>
                <div>{{ $plan->current_period_key ?: 'None' }}</div>
            </td>
        </tr>
    </table>

    <div class="panel">
        <h2>Review Timeline</h2>
        <table class="review-grid">
            <tr>
                @foreach ($reviews as $review)
                    <td>
                        <div class="field-label">{{ $viewService->periodLabel($review->period_key) }}</div>
                        <div><strong>Status:</strong> {{ ucfirst($review->status) }}</div>
                        <div><strong>Total:</strong> {{ data_get($review->score_summary_json, 'total_score', 'N/A') }}</div>
                        <div><strong>Band:</strong> {{ data_get($review->score_summary_json, 'rating_band', 'N/A') }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    @foreach ($sections as $sectionData)
        <div class="panel">
            <h2>{{ $sectionData['section']->label }}</h2>

            @if ($sectionData['section']->key === 'employee_information' && $generalGuidanceLines->isNotEmpty())
                <table class="field-grid" style="margin-bottom: 12px;">
                    <tr>
                        <td>
                            <div class="field-label">General Guidance</div>
                            <ol style="margin: 0; padding-left: 18px;">
                                @foreach ($generalGuidanceLines as $guidanceLine)
                                    <li style="margin-bottom: 4px;">{{ $guidanceLine }}</li>
                                @endforeach
                            </ol>
                        </td>
                    </tr>
                </table>
            @endif

            @if ($sectionData['section']->is_repeatable)
                @if ($sectionData['section']->key === 'performance_objectives' && $sectionData['grouped_entries']->isNotEmpty())
                    @foreach ($sectionData['grouped_entries'] as $group)
                        <div class="entry-title" style="margin-bottom: 8px;">{{ $group['label'] }}</div>
                        @foreach ($group['entries'] as $entry)
                            <div class="entry">
                                <div class="entry-title">{{ data_get($entry->values_json, 'objective', 'Objective') }}</div>
                                @if ($entry->childEntries->isNotEmpty())
                                    <table class="field-grid">
                                        @foreach ($entry->childEntries as $childEntry)
                                            <tr>
                                                @foreach ($sectionData['detail_fields'] as $field)
                                                    <td>
                                                        <div class="field-label">{{ $field->label }}</div>
                                                        {{ $viewService->displayValue($field, data_get($childEntry->values_json, $field->key)) }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                                <table class="field-grid">
                                    @foreach ($sectionData['evaluation_fields'] as $field)
                                        @php
                                            $value = $viewService->sectionFieldValue($field, $sectionData, $entry);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="field-label">{{ $field->label }}</div>
                                                @if (is_array($value))
                                                    <pre>{{ $viewService->displayValue($field, $value) }}</pre>
                                                @else
                                                    {{ $viewService->displayValue($field, $value) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        @endforeach
                    @endforeach
                @else
                    @forelse ($sectionData['entries'] as $entry)
                        <div class="entry">
                            <div class="entry-title">Entry {{ $loop->iteration }}</div>
                            <table class="field-grid">
                                @foreach ($sectionData['fields'] as $field)
                                    @php
                                        $value = $viewService->sectionFieldValue($field, $sectionData, $entry);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="field-label">{{ $field->label }}</div>
                                            @if (is_array($value))
                                                <pre>{{ $viewService->displayValue($field, $value) }}</pre>
                                            @else
                                                {{ $viewService->displayValue($field, $value) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @empty
                        <div class="muted">No entries recorded.</div>
                    @endforelse
                @endif
            @else
                <table class="field-grid">
                    @foreach ($sectionData['fields'] as $field)
                        @php
                            $value = $viewService->sectionFieldValue($field, $sectionData);
                        @endphp
                        <tr>
                            <td>
                                <div class="field-label">{{ $field->label }}</div>
                                @if (is_array($value))
                                    <pre>{{ $viewService->displayValue($field, $value) }}</pre>
                                @else
                                    {{ $viewService->displayValue($field, $value) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>
    @endforeach

    <div class="panel">
        <h2>Approval and Signatures</h2>
        <table class="signature-table">
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Scope</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Signer</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plan->signatures as $signature)
                    @php
                        $signatureDataUri = $pdfService->signatureDataUri($signature);
                    @endphp
                    <tr>
                        <td>{{ $signature->approval_step_key }}</td>
                        <td>{{ $signature->review?->period_key ? $viewService->periodLabel($signature->review->period_key) : 'Plan Level' }}</td>
                        <td>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $signature->role_type)) }}</td>
                        <td>{{ ucfirst($signature->status) }}</td>
                        <td>
                            {{ $signature->signer?->full_name ?? 'Pending' }}
                            @if ($signature->signed_at)
                                <div class="muted">{{ $signature->signed_at->format('Y-m-d H:i') }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($signatureDataUri)
                                <img src="{{ $signatureDataUri }}" alt="Signature" class="signature-image">
                            @else
                                {{ $signature->resolved_signature_path ?: 'No signature on file' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
