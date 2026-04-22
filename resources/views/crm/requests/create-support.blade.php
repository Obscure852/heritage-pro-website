@extends('layouts.crm')

@section('title', 'Create Support Request')
@section('crm_heading', 'Create Support Request')
@section('crm_subheading', 'Log post-sale customer work using a dedicated support form instead of a mixed request screen.')

@section('crm_actions')
    <a href="{{ route('crm.requests.create') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to chooser
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Customer support workflow</p>
                <h2>Create support request</h2>
            </div>
        </div>

        @include('crm.requests._support_form', [
            'action' => route('crm.requests.support.store'),
            'method' => null,
            'submitLabel' => 'Save support request',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.requests.support.index'),
        ])
    </section>
@endsection
