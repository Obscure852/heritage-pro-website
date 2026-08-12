@extends('layouts.crm-wizard')

@section('title', 'Client Setup — Saved')

@section('wizard_header')
    <div class="crm-wizard-header">
        <p class="crm-wizard-eyebrow">Progress saved</p>
        <h1>You can continue when you’re ready.</h1>
        <p>Your client setup information has been saved securely. We have signed you out of this session so the setup link can be shared safely.</p>
    </div>
@endsection

@section('content')
    <section class="crm-wizard-exit-panel" aria-labelledby="exit-heading">
        <div class="crm-wizard-exit-icon" aria-hidden="true"><i class="bx bx-check"></i></div>
        <div>
            <p class="crm-kicker">Resume link</p>
            <h2 id="exit-heading">Return using your original setup link</h2>
            <p class="crm-muted">When you return, we will send a fresh verification code to {{ $maskedEmail }} before opening your saved draft.</p>
        </div>
        <a href="{{ route('client-setup.entry', ['token' => request()->route('token')]) }}" class="btn btn-primary">
            <i class="bx bx-link-external"></i> Return to setup
        </a>
        <p class="crm-muted mt-3 mb-0">
            Lost the original link?
            <a href="{{ route('client-setup.resume') }}">Request a fresh resume link</a>.
        </p>
    </section>
@endsection
