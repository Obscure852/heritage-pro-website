@extends('layouts.crm')

@section('title', 'Create Sales Stage')
@section('crm_heading', 'Settings')
@section('crm_subheading', 'Create a new sales stage on its own page instead of inside the stage list.')

@section('crm_actions')
    <a href="{{ route('crm.settings.sales-stages') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to sales stages
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.settings._tabs', ['activeSection' => 'sales-stages'])

        @include('crm.partials.helper-text', [
            'title' => 'New Sales Stage',
            'content' => 'Set the stage name, order, and terminal flags here, then return to the stage list to verify how it fits the pipeline.',
        ])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">New pipeline stage</p>
                    <h2>Create a sales stage</h2>
                </div>
            </div>

            @include('crm.settings.sales-stages._form', [
                'action' => route('crm.settings.sales-stages.store'),
                'method' => null,
                'submitLabel' => 'Save stage',
                'submitIcon' => 'fas fa-save',
                'cancelUrl' => route('crm.settings.sales-stages'),
                'defaultPosition' => 1,
            ])
        </section>
    </div>
@endsection
