@extends('layouts.crm')

@section('title', 'Create Sales Request')
@section('crm_heading', 'Create Sales Request')
@section('crm_subheading', 'Log pre-sale work against a lead using a dedicated sales form instead of a mixed request screen.')

@section('crm_actions')
    <a href="{{ route('crm.requests.create') }}" class="btn btn-light crm-btn-light">
        <i class="bx bx-arrow-back"></i> Back to chooser
    </a>
@endsection

@section('content')
    <section class="crm-card">
        <div class="crm-card-title">
            <div>
                <p class="crm-kicker">Lead sales workflow</p>
                <h2>Create sales request</h2>
            </div>
        </div>

        @include('crm.requests._sales_form', [
            'action' => route('crm.requests.sales.store'),
            'method' => null,
            'submitLabel' => 'Save sales request',
            'submitIcon' => 'fas fa-save',
            'cancelUrl' => route('crm.requests.sales.index'),
        ])
    </section>
@endsection
