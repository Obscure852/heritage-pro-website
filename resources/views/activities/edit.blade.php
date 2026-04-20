@extends('layouts.master')

@section('title')
    Edit Activity
@endsection

@section('css')
    @include('activities.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            Edit Activity
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Edit Activity</h1>
                <p class="page-subtitle">Update the base activity definition while keeping lifecycle transitions explicit and audited.</p>
            </div>
        </div>

        <div class="form-body">
            <div class="info-note">
                <div class="help-title">Lifecycle Status</div>
                <div class="help-content">
                    Status changes are handled from the activity detail page so activation, pause, closure, and archive actions stay explicit and audited.
                </div>
            </div>

            <form action="{{ route('activities.update', $activity) }}" method="POST" id="activity-form" class="needs-validation" novalidate data-activity-form>
                @csrf
                @method('PUT')
                @include('activities.partials.form-fields')

                <div class="form-actions">
                    <a href="{{ route('activities.show', $activity) }}" class="btn btn-secondary">
                        <i class="bx bx-x"></i> Cancel
                    </a>
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
    </div>
@endsection

@section('script')
    @include('activities.partials.form-script')
@endsection
