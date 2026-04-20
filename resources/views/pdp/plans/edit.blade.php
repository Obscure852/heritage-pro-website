@extends('layouts.master')

@section('title', 'Edit PDP Plan')
@section('page_title', 'Edit PDP Plan')
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
        @slot('li_2')
            Plan Details
        @endslot
        @slot('li_2_url')
            {{ route('staff.pdp.plans.show', $plan) }}
        @endslot
        @slot('title')
            Edit PDP Plan
        @endslot
    @endcomponent

    <div class="pdp-theme">
        <div class="form-container">
            <div class="page-header">
                <h1 class="page-title">Edit PDP Plan</h1>
            </div>

            <div class="help-text">
                <div class="help-title">Update Plan Settings</div>
                <div class="help-content">
                    Adjust the reporting period, supervisor, status, and current workflow period while keeping the bound template version unchanged.
                </div>
            </div>

            <form method="POST" action="{{ route('staff.pdp.plans.update', $plan) }}">
                @csrf
                @method('PUT')
                @include('pdp.partials.plan-form', [
                    'plan' => $plan,
                    'availableUsers' => $availableUsers,
                    'templates' => collect(),
                ])
            </form>
        </div>
    </div>
@endsection
@section('script')
    @include('pdp.partials.theme-script')
@endsection
