@extends('layouts.master')

@section('title')
    New Activity
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
            New Activity
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">New Activity</h1>
                <p class="page-subtitle">Create the core activity record for the selected term, including delivery, participation, reporting, and optional fee defaults.</p>
            </div>
        </div>

        <div class="form-body">
            <div class="help-text">
                <div class="help-title">Activity Setup</div>
                <div class="help-content">
                    Use this page to define the activity identity, how it runs, how participation is tracked, and whether it should carry attendance, house reporting, or fee defaults.
                </div>
            </div>

            <form action="{{ route('activities.store') }}" method="POST" id="activity-form" class="needs-validation" novalidate data-activity-form>
                @csrf
                @include('activities.partials.form-fields')

                <div class="form-actions">
                    <a href="{{ route('activities.index') }}" class="btn btn-secondary">
                        <i class="bx bx-x"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Create Activity</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Creating...
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
