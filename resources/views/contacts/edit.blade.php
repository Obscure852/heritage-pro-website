@extends('layouts.master')

@section('title')
    Edit Business Contact
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
            Edit {{ $contact->name }}
        @endslot
    @endcomponent

    @include('contacts.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Edit Business Contact</h1>
                <p class="page-subtitle">Update the shared business record, people list, and category tags without breaking existing asset links.</p>
            </div>
        </div>

        <div class="help-text">
            <div class="help-title">Edit Business Contact</div>
            <div class="help-content">
                Keep the business details current, make sure one contact person remains primary, and only deactivate a contact if it should no longer be selectable.
            </div>
        </div>

        <div class="info-note">
            <div class="help-title">Contact Tags</div>
            <div class="help-content">
                Tags decide where the business can be used and show the kind of work it does. Keep them aligned with the services this business currently provides so linked records stay accurate.
            </div>
        </div>

        <form action="{{ route('contacts.update', $contact) }}" method="POST" id="contact-form">
            @csrf
            @method('PUT')
            @include('contacts.partials.form-fields')

            <div class="form-actions form-actions-between">
                <a href="{{ route('contacts.show', $contact) }}" class="btn btn-light">Back</a>
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@section('script')
    @include('contacts.partials.form-script')
@endsection
