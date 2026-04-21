@extends('layouts.crm')

@section('title', 'Create Dev Request')
@section('crm_heading', 'Create Dev Request')
@section('crm_subheading', 'Log a requested development or improvement on its own page instead of inside the backlog list.')

@section('crm_actions')
    <a href="{{ route('crm.dev.index') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to dev
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">New dev item</p>
                <h2>Log an improvement request</h2>
            </div>
        </div>

        @include('crm.dev._form', [
            'action' => route('crm.dev.store'),
            'method' => null,
            'submitLabel' => 'Save dev request',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.dev.index'),
        ])
    </section>
@endsection
