@extends('layouts.master')

@section('title', 'Create PDP Plan')
@section('page_title', 'Create PDP Plan')
@section('css')
    @include('pdp.partials.theme-css')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Staff PDP
        @endslot
        @slot('li_1_url')
            {{ route('staff.pdp.plans.index') }}
        @endslot
        @slot('title')
            Create PDP Plan
        @endslot
    @endcomponent

    <div class="pdp-theme">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">Create PDP Plan</h1>
            </div>

            <div class="help-text">
                <div class="help-title">Create a New Staff PDP Plan</div>
                <div class="help-content">
                    Capture the employee, template version, reporting period, and workflow state for a new Staff PDP record. Fields marked with <span class="text-danger">*</span> are required.
                </div>
            </div>

            <form method="POST" action="{{ route('staff.pdp.plans.store') }}">
                @csrf
                @include('pdp.partials.plan-form', [
                    'availableUsers' => $availableUsers,
                    'templates' => $templates,
                ])
            </form>
        </div>
    </div>
@endsection
@section('script')
    @include('pdp.partials.theme-script')
@endsection
