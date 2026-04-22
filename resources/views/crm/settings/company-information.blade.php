@extends('layouts.crm')

@section('title', 'CRM Settings - Company Information')
@section('crm_heading', 'Settings')
@section('crm_subheading', 'Manage the core company details used across CRM branding, login screens, and shared operational references.')

@push('head')
    <style>
        @include('crm.settings._identity_styles')
    </style>
@endpush

@section('content')
    <div class="crm-stack">
        @include('crm.settings._tabs', ['activeSection' => $activeSection])

        @include('crm.partials.helper-text', [
            'title' => 'Company Information',
            'content' => 'Update the company profile details used across the CRM workspace and customer-facing authentication surfaces.',
        ])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Company information</p>
                    <h2>Business profile</h2>
                </div>
            </div>

            @include('crm.settings._company_information_form', ['settings' => $settings])
        </section>
    </div>
@endsection
