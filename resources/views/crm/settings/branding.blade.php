@extends('layouts.crm')

@section('title', 'CRM Settings - Branding')
@section('crm_heading', 'Settings')
@section('crm_subheading', 'Manage the logo and login image shown across the CRM shell and authentication surfaces.')

@push('head')
    <style>
        @include('crm.settings._identity_styles')
    </style>
@endpush

@section('content')
    <div class="crm-stack">
        @include('crm.settings._tabs', ['activeSection' => $activeSection])

        @include('crm.partials.helper-text', [
            'title' => 'Branding',
            'content' => 'Update the brand assets shown in the CRM navigation and login experience using the same crop workflow as staff profile images.',
        ])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Branding</p>
                    <h2>Logo and login image</h2>
                </div>
            </div>

            @include('crm.settings._branding_form', ['settings' => $settings])
        </section>
    </div>
@endsection
