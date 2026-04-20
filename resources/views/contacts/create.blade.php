@extends('layouts.master')

@section('title')
    New Business Contact
@endsection

@section('css')
    @include('contacts.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('contacts.index') }}">Contacts</a>
        @endslot
        @slot('title')
            New Business Contact
        @endslot
    @endcomponent

    @include('contacts.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">New Business Contact</h1>
                <p class="page-subtitle">Create a reusable business record that can be selected in Assets and Maintenance.</p>
            </div>
        </div>

        <div class="help-text">
            <div class="help-title">Create New Contact</div>
            <div class="help-content">
                Add the business details, assign the right business-category tags, and capture at least one primary contact person before saving.
            </div>
        </div>

        <div class="info-note">
            <div class="help-title">Contact Tags</div>
            <div class="help-content">
                Tags decide where the business can be used and show the kind of work it does. Choose every category that applies so the contact appears in the right asset and maintenance workflows.
            </div>
        </div>

        <form action="{{ route('contacts.store') }}" method="POST" id="contact-form">
            @csrf
            @include('contacts.partials.form-fields')

            <div class="form-actions">
                <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Create Contact</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Creating...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@section('script')
    @include('contacts.partials.form-script')
@endsection
