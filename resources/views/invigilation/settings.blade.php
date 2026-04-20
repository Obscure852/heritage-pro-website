@extends('layouts.master')

@section('title')
    Invigilation Roster Settings
@endsection

@section('css')
    @include('invigilation.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('invigilation.index') }}">Invigilation Roster</a>
        @endslot
        @slot('title')
            Invigilation Roster Settings
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    <div class="invigilation-container">
        <div class="invigilation-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3 class="mb-1 text-white">Invigilation Roster Settings</h3>
                    <p class="mb-0 opacity-75">
                        Define module defaults for new invigilation series so the manager page starts with the right policies already selected.
                    </p>
                </div>
                <div class="col-md-5">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $seriesTypes[$defaults['default_type']] ?? 'Mock' }}</h4>
                                <small class="opacity-75">Default Type</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $defaults['default_required_invigilators'] }}</h4>
                                <small class="opacity-75">Invigilators</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $timetablePolicies[$defaults['default_timetable_conflict_policy']] ?? 'Ignore' }}</h4>
                                <small class="opacity-75">Timetable Rule</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="invigilation-body">
            <div class="help-text">
                <div class="help-title">Settings Guidance</div>
                <div class="help-content">
                    These defaults only prefill the new series form. Individual series can still override type, staffing, eligibility, and timetable conflict rules.
                </div>
            </div>

            @include('invigilation.partials.module-nav', ['current' => 'settings'])

            <div class="card-shell">
                <div class="card-body p-4">
                    <div class="invigilation-section-header">
                        <div>
                            <h5 class="invigilation-section-title">Default Series Creation Rules</h5>
                            <p class="invigilation-section-subtitle">Applied when a new series is created from the manager page.</p>
                        </div>
                    </div>

                    <form action="{{ route('invigilation.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label required-label" for="default_type">Default Series Type</label>
                                <select class="form-select" id="default_type" name="default_type">
                                    @foreach ($seriesTypes as $key => $label)
                                        <option value="{{ $key }}" {{ $defaults['default_type'] === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label required-label" for="default_required_invigilators">Default Invigilators / Room</label>
                                <input
                                    class="form-control"
                                    id="default_required_invigilators"
                                    name="default_required_invigilators"
                                    type="number"
                                    min="1"
                                    max="10"
                                    value="{{ $defaults['default_required_invigilators'] }}"
                                >
                            </div>
                            <div class="form-group grid-span-full">
                                <label class="form-label required-label" for="default_eligibility_policy">Default Eligibility Policy</label>
                                <select class="form-select" id="default_eligibility_policy" name="default_eligibility_policy">
                                    @foreach ($eligibilityPolicies as $key => $label)
                                        <option value="{{ $key }}" {{ $defaults['default_eligibility_policy'] === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group grid-span-full">
                                <label class="form-label required-label" for="default_timetable_conflict_policy">Default Timetable Conflict Policy</label>
                                <select class="form-select" id="default_timetable_conflict_policy" name="default_timetable_conflict_policy">
                                    @foreach ($timetablePolicies as $key => $label)
                                        <option value="{{ $key }}" {{ $defaults['default_timetable_conflict_policy'] === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save me-1"></i> Save Settings</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('invigilation.partials.form-loading-script')
@endsection
