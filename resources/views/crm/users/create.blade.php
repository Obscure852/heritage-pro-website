@extends('layouts.crm')

@section('title', 'Create CRM User')
@section('crm_heading', 'Create User')
@section('crm_subheading', 'Add a staff profile with employment details, reporting line, reusable filters, and the correct CRM role.')

@section('crm_actions')
    <a href="{{ route('crm.users.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to users
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">New user</p>
                <h2>Create an internal staff account</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('crm.users.store') }}" class="crm-form">
            @csrf

            @include('crm.users._directory-form-fields', [
                'formMode' => 'create',
            ])

            <div class="form-actions">
                <a href="{{ route('crm.users.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Create user</span>
                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
                </button>
            </div>
        </form>
    </section>
@endsection
