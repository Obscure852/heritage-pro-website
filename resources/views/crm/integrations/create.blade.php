@extends('layouts.crm')

@section('title', 'Create Integration')
@section('crm_heading', 'Create Integration')
@section('crm_subheading', 'Create a new integration profile on its own page instead of inside the integrations list.')

@section('crm_actions')
    <a href="{{ route('crm.integrations.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to integrations
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">New integration</p>
                <h2>Create an integration profile</h2>
            </div>
        </div>

        @include('crm.integrations._form', [
            'action' => route('crm.integrations.store'),
            'method' => null,
            'submitLabel' => 'Save integration',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.integrations.index'),
        ])
    </section>
@endsection
