@extends('layouts.crm-wizard')

@section('title', 'Resume Client Setup')

@section('wizard_header')
    <div class="crm-wizard-header">
        <p class="crm-wizard-eyebrow">Secure resume</p>
        <h1>Continue your client setup</h1>
        <p>Enter the email address used for your invitation. We will send a fresh secure link if an active setup is associated with it.</p>
    </div>
@endsection

@section('content')
    <section class="crm-card" aria-labelledby="resume-heading">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Saved progress</p>
                <h2 id="resume-heading">Request a new setup link</h2>
                <p class="crm-muted">For privacy, the confirmation message is the same whether or not an active setup exists.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('client-setup.resume.request') }}" class="crm-form">
            @csrf
            <div class="crm-field">
                <label for="resume-email">Invitation email</label>
                <input id="resume-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="form-control" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="bx bx-link"></i> Send resume link</span>
                <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...</span>
            </button>
        </form>
    </section>
@endsection
