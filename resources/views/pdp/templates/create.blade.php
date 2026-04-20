@extends('layouts.master')

@section('title', 'Create PDP Template')
@section('page_title', 'Create PDP Template')
@section('css')
    @include('pdp.partials.theme-css')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            PDP Templates
        @endslot
        @slot('li_1_url')
            {{ route('staff.pdp.settings.index', ['tab' => 'templates']) }}
        @endslot
        @slot('title')
            Create PDP Template
        @endslot
    @endcomponent

    <div class="pdp-theme">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">Create PDP Template</h1>
            </div>

            <div class="help-text">
                <div class="help-title">Start a New Bounded PDP Template</div>
                <div class="help-content">
                    Start from a blank bounded template or an approved baseline, then finish authoring the draft in the template builder without changing existing PDP plans already bound to older versions.
                </div>
            </div>

            <form method="POST" action="{{ route('staff.pdp.templates.store') }}">
                @csrf

                <h3 class="section-title">Template Definition</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Baseline <span class="text-danger">*</span></label>
                        <select name="baseline_key" class="form-select" required>
                            @foreach ($blueprints as $key => $blueprint)
                                <option value="{{ $key }}" @selected(old('baseline_key') === $key)>{{ $blueprint['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Template Family Key <span class="text-danger">*</span></label>
                        <input type="text" name="template_family_key" class="form-control" value="{{ old('template_family_key') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Template Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
                    </div>
                </div>

                <div class="form-grid mt-3">
                    <div class="form-group">
                        <label class="form-label">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Source Reference</label>
                        <input type="text" name="source_reference" class="form-control" value="{{ old('source_reference') }}">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('staff.pdp.settings.index', ['tab' => 'templates']) }}" class="btn btn-secondary">
                        <i class="bx bx-x"></i> Cancel
                    </a>
                    @include('pdp.partials.submit-button', [
                        'label' => 'Create Draft',
                        'loadingText' => 'Creating draft...',
                        'icon' => 'fas fa-save',
                    ])
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    @include('pdp.partials.theme-script')
@endsection
