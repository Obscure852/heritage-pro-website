@extends('layouts.crm-wizard')

@section('title', 'Client Setup — Academic configuration submitted')

@section('wizard_header')
    <div class="crm-wizard-header">
        <p class="crm-wizard-eyebrow">Academic configuration submitted</p>
        <h1>The academic setup is now with the implementation team.</h1>
        <p>Your submitted academic revision is frozen for review. You may still return later to add supplemental migration, integration or finance details.</p>
    </div>
@endsection

@section('content')
    <section class="crm-wizard-submission-panel" aria-labelledby="academic-submission-heading">
        <div class="crm-wizard-exit-icon" aria-hidden="true"><i class="bx bx-check-shield"></i></div>
        <div>
            <p class="crm-kicker">Submission recorded</p>
            <h2 id="academic-submission-heading">Academic revision {{ $academicRevisionNumber }} submitted</h2>
            <p class="crm-muted">Submitted {{ $submission->academic_submitted_at?->format('d M Y, H:i') ?: 'now' }}. The implementation team can request changes through the CRM review workflow.</p>
        </div>
    </section>

    <section class="crm-wizard-saved-summary mt-3" aria-labelledby="supplemental-heading">
        <div>
            <p class="crm-kicker">Still available</p>
            <h2 id="supplemental-heading">Supplemental setup</h2>
        </div>
        <p>Migration, integrations and finance remain open so they can be completed without changing the submitted academic revision.</p>
        <div class="crm-action-row">
            @foreach (['migration' => 'Migration', 'integrations_access' => 'Integrations and access', 'finance' => 'Finance'] as $stageKey => $label)
                <a href="{{ route('client-setup.stage', ['token' => request()->route('token'), 'stage' => $stageKey]) }}" class="btn btn-light crm-btn-light">{{ $label }}</a>
            @endforeach
        </div>
    </section>

    <section class="crm-wizard-readiness-panel mt-3" aria-labelledby="supplemental-status-heading">
        <div class="crm-wizard-readiness-heading">
            <div><p class="crm-kicker">Supplemental status</p><h2 id="supplemental-status-heading">Complete or defer each optional section</h2></div>
            <span class="crm-pill {{ $supplementalSummary['not_started'] === 0 && $supplementalSummary['in_progress'] === 0 ? 'success' : 'warning' }}">{{ $supplementalSummary['complete'] + $supplementalSummary['deferred'] }} / {{ $supplementalSummary['total'] }} resolved</span>
        </div>
        <ul>
            @foreach ($supplementalSummary['rows'] as $row)
                <li><strong>{{ $row['label'] }}:</strong> {{ ucfirst(str_replace('_', ' ', $row['state'])) }}</li>
            @endforeach
        </ul>
        @if ($supplementalSummary['not_started'] === 0 && $supplementalSummary['in_progress'] === 0)
            <form method="POST" action="{{ route('client-setup.supplemental-complete', ['token' => request()->route('token')]) }}">
                @csrf
                <button type="submit" class="btn btn-primary"><i class="bx bx-check-circle"></i> Mark supplemental setup complete</button>
            </form>
        @else
            <p class="crm-muted">When all optional sections are completed or explicitly deferred, you can mark supplemental setup complete for the implementation team.</p>
        @endif
    </section>
@endsection
