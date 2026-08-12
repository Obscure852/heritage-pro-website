@extends('layouts.crm')

@php
    $institution = data_get($submission->payloadArray(), 'scope.institution_legal_name') ?: $submission->customer?->company_name ?: $submission->lead?->company_name ?: 'Unnamed institution';
    $from = $comparison['from'];
    $to = $comparison['to'];
@endphp

@section('title', $institution . ' - Revision comparison')
@section('crm_heading', 'Revision comparison')
@section('crm_subheading', $institution . ' · compare saved Client Setup snapshots and identify additions, edits and removals.')

@section('crm_actions')
    <a href="{{ route('crm.client-setup.show', $submission) }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Back to review</a>
@endsection

@section('content')
    <div class="crm-stack">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Selected snapshots</p>
                    <h2>Revision #{{ $from->revision_number }} to revision #{{ $to->revision_number }}</h2>
                    <p>Only changed values are shown. A removed value is present in the earlier snapshot but absent from the later snapshot.</p>
                </div>
                <span class="crm-pill {{ $comparison['changed_count'] ? 'primary' : 'success' }}">{{ $comparison['changed_count'] }} change(s)</span>
            </div>
            <div class="crm-meta-list">
                <div class="crm-meta-row"><span>Earlier revision</span><strong>#{{ $from->revision_number }} · {{ ucfirst(str_replace('_', ' ', $from->source)) }} · {{ $from->created_at?->format('d M Y H:i') }}</strong></div>
                <div class="crm-meta-row"><span>Later revision</span><strong>#{{ $to->revision_number }} · {{ ucfirst(str_replace('_', ' ', $to->source)) }} · {{ $to->created_at?->format('d M Y H:i') }}</strong></div>
            </div>
        </section>

        <section class="crm-card">
            @if ($comparison['changes'] === [])
                <div class="crm-empty">The selected snapshots contain no changed values.</div>
            @else
                <div class="crm-table-wrap" role="region" aria-label="Revision value comparison" tabindex="0">
                    <table class="crm-table">
                        <thead><tr><th>Field path</th><th>Earlier value</th><th>Later value</th></tr></thead>
                        <tbody>
                            @foreach ($comparison['changes'] as $change)
                                <tr>
                                    <td><strong>{{ $change['key'] }}</strong></td>
                                    <td><pre style="white-space:pre-wrap;margin:0;font:inherit">{{ $change['from'] }}</pre></td>
                                    <td><pre style="white-space:pre-wrap;margin:0;font:inherit">{{ $change['to'] }}</pre></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
